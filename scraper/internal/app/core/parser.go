package core

import (
	"github.com/PuerkitoBio/goquery"
	"github.com/gocolly/colly/v2"
	"strings"
	"sw-vehicles/internal/app/helpers"
)

// Parser is a common parser for Wookieepedia pages.
// It contains basic function and should be extended to parse a specific page type.
type Parser struct{}

func (p *Parser) GetMainInfo(page *colly.HTMLElement) MainInfoDTO {
	infobox := p.FindPageInfobox(page)

	return MainInfoDTO{
		Name:        p.ParsePageTitle(page),
		ImageURL:    p.ParseMainImageUrl(infobox),
		Description: p.ParsePageText(page),
		URL:         page.Request.URL.Scheme + "://" + page.Request.URL.Host + page.Request.URL.Path,
		RelatedURL:  NullableString(p.ParseCanonRelatedURL(page)),
		IsCanon:     p.IsCanonPage(page),
	}
}

// ParsePageTitle extracts the page title from the H1 heading.
// If for some reason the H1 is missing, attempts to extract the page title from an infobox.
func (p *Parser) ParsePageTitle(page *colly.HTMLElement) string {
	heading := page.DOM.Find("h1#firstHeading")
	if heading.Length() > 0 {
		note := heading.Find("small")
		if note.Length() > 0 {
			return strings.TrimSpace(strings.Replace(heading.Text(), note.Text(), "", 1))
		}

		return strings.TrimSpace(heading.Text())
	}

	infobox := p.FindPageInfobox(page)

	heading = infobox.Find(`h2[data-source="name"]`)
	if heading.Length() > 0 {
		return strings.TrimSpace(heading.Text())
	}

	heading = infobox.Find(`h2[data-source="title"]`)
	if heading.Length() > 0 {
		return strings.TrimSpace(heading.Text())
	}

	return "Unknown"
}

// ParsePageText extracts the page text from HTML <p> tags (or plain text) and removes any unnecessary formatting.
func (p *Parser) ParsePageText(page *colly.HTMLElement) string {
	var description string

	ignoredNodesNames := []string{
		"h2",
		"h3",
		"h4",
		"table",
		"aside",
		"div",
		"ul",
		"ol",
		"sup",
		"sub",
		"figure",
		"img",
	}

	// extract all possible text from the page
	page.DOM.Find(".mw-parser-output").Contents().Each(func(i int, sel *goquery.Selection) {
		nodeName := goquery.NodeName(sel)
		for _, ignoredNodeName := range ignoredNodesNames {
			if nodeName == ignoredNodeName {
				return
			}
		}

		// if there is a paragraph, just grab its text
		if nodeName == "p" {
			description = helpers.ConcatStrings(description, "\n", sel.Text())
			return
		}

		// if there is no paragraph, concatenate every child node text
		description = helpers.ConcatStrings(description, sel.Text())
	})

	var builder strings.Builder

	// remove all reference links ("[1]", "[2]", etc.)
	isInReference := false
	for _, char := range description {
		symbol := string(char)

		if symbol == "[" {
			isInReference = true
		}

		if isInReference && (symbol == "]") {
			isInReference = false
			continue
		}

		if isInReference {
			continue
		}

		builder.WriteString(symbol)
	}

	description = builder.String()

	builder.Reset()

	// remove extra line breaks
	isPrevCharNewLine := false
	for _, char := range description {
		symbol := string(char)

		if isPrevCharNewLine && symbol == "\n" {
			continue
		} else if isPrevCharNewLine {
			isPrevCharNewLine = false
		}

		if symbol == "\n" {
			isPrevCharNewLine = true
		}

		builder.WriteString(symbol)
	}

	description = builder.String()

	return strings.TrimSpace(description)
}

// ParseMainImageUrl extracts the first image URL from the infobox, which is used as the main image.
func (p *Parser) ParseMainImageUrl(infoboxSelection *goquery.Selection) string {
	imageSelection := infoboxSelection.Find(`figure[data-source="image"]`)

	if imageSelection.Length() == 0 {
		imageSelection = infoboxSelection.Find(`div[data-source="imagefallback"]`).Find("figure")
	}

	if imageSelection.Length() == 0 {
		return ""
	}

	if href, exists := imageSelection.ChildrenFiltered("a").First().Attr("href"); exists {
		return href
	}

	return ""
}

// IsPageTemplateSupported verifies that page template is included in the supported list.
func (p *Parser) IsPageTemplateSupported(template PageTemplate, supportedTemplates []PageTemplate) bool {
	for _, t := range supportedTemplates {
		if t == template {
			return true
		}
	}

	return false
}

// ExtractPageUrlTemplateName extracts the page template name from "https://starwars.fandom.com/wiki/Template:***" URL.
func (p *Parser) ExtractPageUrlTemplateName(url string) string {
	return strings.Replace(url, "https://starwars.fandom.com/wiki/Template:", "", 1)
}

// IsCanonPage checks if the page has information about canon.
// It looks for "#canontab". Inside that, it finds "#canontab-canon_ctcw", which is an active tab for "Canon" page.
// It checks if the current page's URL is the same as the URL of that active tab.
// If not, it looks for "#title-eraicons".
// It finds an image and checks if its "alt" attribute value ends with "is considered canon" string.
func (p *Parser) IsCanonPage(page *colly.HTMLElement) bool {
	canonTabs := page.DOM.Find("#canontab")
	if canonTabs.Length() > 0 {
		activeCanonTab := canonTabs.Find("#canontab-canon_ctcw")
		if activeCanonTab.Length() == 0 {
			return false
		}

		url, exists := activeCanonTab.Find("a[href]").Attr("href")
		return exists && page.Request.AbsoluteURL(url) == page.Request.URL.String()
	}

	eraIcons := page.DOM.Find("#title-eraicons")
	if eraIcons.Length() > 0 {
		alt, exists := eraIcons.Find("img[src]").Attr("alt")

		return exists && strings.Contains(alt, "is considered canon")
	}

	return false
}

// ParseCanonRelatedURL finds the URL of a related "Legends" page for "Canon" (or vice versa).
// Both pages contain similar information and can be used in conjunction with each other.
func (p *Parser) ParseCanonRelatedURL(page *colly.HTMLElement) string {
	canonTabs := page.DOM.Find("#canontab")
	if canonTabs.Length() == 0 {
		return ""
	}

	var url string
	canonTabs.Find("td").EachWithBreak(func(index int, tab *goquery.Selection) bool {
		tabUrl, hasUrl := tab.Find("a[href]").Attr("href")
		if !hasUrl {
			return true
		}

		if page.Request.AbsoluteURL(tabUrl) == page.Request.URL.String() {
			return true
		}

		url = page.Request.AbsoluteURL(tabUrl)
		return false
	})

	return url
}

// FindPageInfobox finds the "aside.portable-infobox" on the given page.
func (p *Parser) FindPageInfobox(page *colly.HTMLElement) *goquery.Selection {
	return page.DOM.Find("aside.portable-infobox")
}

// ParseInfoboxDataSource parses [data-source="*"] items from "aside.portable-infobox" element.
func (p *Parser) ParseInfoboxDataSource(selection *goquery.Selection) []AdditionalDataDTO {
	var dto []AdditionalDataDTO
	if selection.Length() == 0 {
		return dto
	}

	valueSelection := selection.ChildrenFiltered(".pi-data-value")

	// could be nested <ul>...
	if list := valueSelection.ChildrenFiltered("ul"); list.Length() > 0 {
		return p.ParseInfoboxNestedList(list)
	}

	// ...or single text value
	item := p.parseInfoboxItem(valueSelection)
	if item.Name != "" {
		dto = append(dto, item)
	}

	return dto
}

// ParseInfoboxNestedList parses "aside.portable-infobox" nested ul.
func (p *Parser) ParseInfoboxNestedList(selection *goquery.Selection) []AdditionalDataDTO {
	var items []AdditionalDataDTO
	selection.Children().Each(func(i int, sel *goquery.Selection) {
		item := p.parseInfoboxItem(sel)

		if item.Name != "" {
			items = append(items, item)
		}
	})

	return items
}

func (p *Parser) parseInfoboxItem(selection *goquery.Selection) AdditionalDataDTO {
	var builder strings.Builder
	selection.Contents().Each(func(i int, sel *goquery.Selection) {
		nodeName := goquery.NodeName(sel)

		if (nodeName != "#text") && (nodeName != "i") && (nodeName != "span") && (nodeName != "a") {
			return
		}

		text := strings.TrimSpace(sel.Text())
		if text == "" {
			return
		}

		if builder.Len() > 0 {
			builder.WriteString(" ")
		}

		builder.WriteString(text)
	})

	value := builder.String()

	// remove all extra spaces around dashes, slashes, commas, brackets etc.
	replacer := strings.NewReplacer(
		" ( ", " (",
		" ) ", ") ",
		" )", ")",
		" / ", "/",
		" /", "/",
		"/ ", "/",
		" - ", "-",
		" -", "-",
		"- ", "-",
		" ,", ",",
	)

	value = strings.TrimSpace(replacer.Replace(value))

	var note string
	if noteText := selection.Find("small").First().Text(); noteText != "" {
		note = strings.ToLower(strings.TrimSpace(strings.Trim(noteText, "()")))
	}

	var children []AdditionalDataDTO
	if nestedList := selection.ChildrenFiltered("ul"); nestedList.Length() > 0 {
		children = p.ParseInfoboxNestedList(nestedList)
	}

	return AdditionalDataDTO{
		Name:     value,
		Note:     NullableString(note),
		Children: children,
	}
}

func (p *Parser) ParseFactions(infoboxSelection *goquery.Selection) []AdditionalDataDTO {
	factionsData := infoboxSelection.Find(`div[data-source="affiliation"]`)
	if factionsData.Length() == 0 {
		return []AdditionalDataDTO{}
	}

	return p.ParseInfoboxDataSource(factionsData)
}
