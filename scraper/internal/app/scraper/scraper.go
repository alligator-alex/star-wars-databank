package scraper

import (
	"encoding/json"
	"errors"
	"fmt"
	"os"
	"strings"
	"sw-vehicles/internal/app/core"
	"sw-vehicles/internal/app/helpers"
	"sw-vehicles/internal/app/logger"
	"sw-vehicles/internal/app/vehicle"
	"time"

	"github.com/PuerkitoBio/goquery"
	"github.com/gocolly/colly/v2"
	"github.com/gocolly/colly/v2/extensions"
	"github.com/gocolly/colly/v2/queue"
)

type PageTemplate string

type BrokerClient interface {
	Publish(body string)
	Stop()
}

// see isErrorAlreadyVisited() function
var alreadyVisitedError *colly.AlreadyVisitedError

type Scraper struct {
	logger              logger.FileLogger
	brokerClient        BrokerClient
	navPageCollector    *colly.Collector
	detailPageCollector *colly.Collector
	navPageQueue        *queue.Queue
	detailPageQueue     *queue.Queue
	pageNum             int
	totalVisited        int
	totalScraped        int
}

func NewScraper(brokerClient BrokerClient) Scraper {
	scraper := Scraper{
		logger:       logger.NewFileLogger("scraper.log", false),
		brokerClient: brokerClient,
	}

	return scraper
}

func (s *Scraper) Run(continueFrom string) {
	s.navPageCollector = s.newCollector(0)
	s.navPageQueue = s.newQueue(1)
	s.registerNavPageCollectorEvents()

	s.detailPageCollector = s.newCollector(10)
	s.detailPageQueue = s.newQueue(20)
	s.registerDetailPageCollectorEvents()

	s.logger.Log("Start scraping")

	s.navPageQueue.AddURL(firstNavPageUrl + continueFrom)
	s.navPageQueue.Run(s.navPageCollector)

	s.stop()

	s.logger.Log("Scraping complete", "\nVisited:", s.totalVisited, "\nScraped:", s.totalScraped)
}

func (s *Scraper) stop() {
	s.brokerClient.Publish("<<< THE END >>>")
}

func (s *Scraper) newCollector(randomDelay int) *colly.Collector {
	collector := colly.NewCollector(
		colly.AllowedDomains("starwars.fandom.com"),
		colly.DisallowedDomains("fandom.com"), // don't go any deeper
	)

	if randomDelay > 0 {
		collector.Limit(&colly.LimitRule{
			RandomDelay: time.Duration(randomDelay) * time.Second,
		})
	}

	extensions.RandomUserAgent(collector)
	extensions.Referer(collector)

	return collector
}

func (s *Scraper) newQueue(threads int) *queue.Queue {
	var q *queue.Queue
	var err error

	if threads < 1 {
		threads = 1
	}

	q, err = queue.New(threads, &queue.InMemoryQueueStorage{MaxSize: 10000})

	if err != nil {
		fmt.Println("Unable to init queue:", err)
		os.Exit(1)
	}

	return q
}

func (s *Scraper) registerNavPageCollectorEvents() {
	s.pageNum = 1

	s.navPageCollector.OnRequest(func(req *colly.Request) {
		s.logger.Log("Navigating page", s.pageNum, "-", req.URL.String())
	})

	s.navPageCollector.OnError(func(r *colly.Response, err error) {
		if s.isErrorAlreadyVisited(err) {
			return
		}

		s.logger.Log("Error:", err)
	})

	s.navPageCollector.OnHTML("main.page__main", func(page *colly.HTMLElement) {
		s.scrapeNavPage(page)
	})
}

func (s *Scraper) registerDetailPageCollectorEvents() {
	s.detailPageCollector.OnResponse(func(resp *colly.Response) {
		s.totalVisited++

		if resp.StatusCode != 200 {
			s.logger.Log("-", helpers.PadStringRight(resp.Request.URL.String(), urlPadSize, urlPadSymbol), "invalid HTTP status:", resp.StatusCode)
		}
	})

	s.detailPageCollector.OnError(func(resp *colly.Response, err error) {
		if s.isErrorAlreadyVisited(err) {
			return
		}

		s.logger.Log("-", helpers.PadStringRight(resp.Request.URL.String(), urlPadSize, urlPadSymbol), "unknown error:", err)
	})

	s.detailPageCollector.OnHTML("main.page__main", func(page *colly.HTMLElement) {
		s.scrapeDetailPage(page)
	})
}

func (s *Scraper) findPageInfobox(page *colly.HTMLElement) *goquery.Selection {
	return page.DOM.Find("aside.portable-infobox")
}

// Collect all links leading to detail pages.
func (s *Scraper) scrapeNavPage(page *colly.HTMLElement) {
	s.logger.Log("Collecting detail pages...")

	detailPagesUrls := []string{}
	page.ForEach(".mw-allpages-body a[href]", func(i int, el *colly.HTMLElement) {
		detailPagesUrls = append(detailPagesUrls, el.Request.AbsoluteURL(el.Attr("href")))
	})

	detailPagesFound := len(detailPagesUrls)
	if detailPagesFound == 0 {
		s.logger.Log("No detail pages found, exiting")
		os.Exit(1)
	}

	s.logger.Log("Found", detailPagesFound, "more pages")

	for _, u := range detailPagesUrls {
		s.detailPageQueue.AddURL(u)
	}

	s.logger.Log("Starting detail page queue")
	s.detailPageQueue.Run(s.detailPageCollector)
	s.detailPageQueue.Stop()
	s.logger.Log("Detail page queue completed")

	// try to find a link leading to the next page
	s.logger.Log("Looking for the next page...")

	nextPageUrl := firstNavPageUrl
	page.ForEach(".mw-allpages-nav > a[href]", func(i int, el *colly.HTMLElement) {
		url := el.Request.AbsoluteURL(el.Attr("href"))
		if url == "" {
			return
		}

		if !strings.HasPrefix(url, firstNavPageUrl) {
			return
		}

		if !strings.HasPrefix(el.Text, "Next page") {
			return
		}

		// there are two ".mw-allpages-nav" elements on the page
		// so we must cache first matched url
		if nextPageUrl != url {
			nextPageUrl = url

			s.pageNum++

			s.logger.Log("Found next page:", nextPageUrl)

			s.navPageQueue.Stop()
			s.navPageQueue.AddURL(nextPageUrl)
			s.navPageQueue.Run(s.navPageCollector)
		}
	})

	s.logger.Log("No next page found")

	s.navPageQueue.Stop()
}

func (s *Scraper) scrapeDetailPage(page *colly.HTMLElement) {
	paddedUrl := helpers.PadStringRight(page.Request.URL.String(), urlPadSize, urlPadSymbol)

	if !s.isVehiclePage(page) {
		s.logger.Log("-", paddedUrl, "skipped (not a vehicle)")
		return
	}

	appearances := s.parseAppearances(page)
	if len(appearances) == 0 {
		s.logger.Log("-", paddedUrl, "skipped (no appearances)")
		return
	}

	infobox := s.findPageInfobox(page)

	dto := vehicle.VehicleDTO{
		Name:                    s.parsePageTitle(page),
		Category:                s.parseVehicleCategory(infobox),
		Line:                    core.NullableString(s.parseVehicleLine(infobox)),
		Type:                    core.NullableString(s.parseVehicleType(infobox)),
		ImageURL:                s.parseImageUrl(infobox),
		Description:             s.parsePageText(page),
		URL:                     page.Request.URL.Scheme + "://" + page.Request.URL.Host + page.Request.URL.Path,
		RelatedURL:              core.NullableString(s.parseCanonRelatedURL(page)),
		Manufacturers:           s.parseVehicleManufacturers(infobox),
		Factions:                s.parseVehicleFactions(infobox),
		TechnicalSpecifications: s.parseVehicleTechnicalSpecifications(infobox),
		IsCanon:                 s.isCanonPage(page),
		Appearances:             appearances,
	}

	data, err := json.Marshal(dto)
	if err != nil {
		s.logger.Log("-", paddedUrl, "unable to marshal JSON:", err)
		return
	}

	s.brokerClient.Publish(string(data))

	s.totalScraped++
	s.logger.Log("-", paddedUrl, "scraped")
}

func (s *Scraper) scrapeWorkOfArtPage(page *colly.HTMLElement) vehicle.AppearanceDTO {
	if !s.isAppearancePage(page) {
		return vehicle.AppearanceDTO{}
	}

	infobox := s.findPageInfobox(page)

	dto := vehicle.AppearanceDTO{
		Name:        s.parsePageTitle(page),
		URL:         page.Request.URL.String(),
		ImageURL:    s.parseImageUrl(infobox),
		Type:        core.NullableString(s.parseWorkOfArtType(infobox)),
		ReleaseDate: core.NullableString(s.parseWorkOfArtReleaseDate(infobox)),
	}

	return dto
}

func (s *Scraper) parseWorkOfArtType(infoboxSelection *goquery.Selection) string {
	var template string

	url, exists := infoboxSelection.Find(".plainlinks > a").Attr("href")
	if exists {
		template = s.parsePageUrlTemplateName(url)
	}

	switch template {
	case string(templateMovie):
		return "Movie"
	case string(templateTvSeries):
		return "Series"
	case string(templateVideoGame):
		return "Game"
	case string(templateBook):
		return "Book"
	case string(templateComicBook):
		return "ComicBook"
	default:
		return ""
	}
}

func (s *Scraper) parseWorkOfArtReleaseDate(infoboxSelection *goquery.Selection) string {
	dateSelection := infoboxSelection.Find(`div[data-source="release date"]`)
	if dateSelection.Length() == 0 {
		dateSelection = infoboxSelection.Find(`div[data-source="first aired"]`)
	}

	if dateSelection.Length() == 0 {
		return ""
	}

	lines := s.parseInfoboxDataSource(dateSelection)
	if len(lines) == 0 {
		return ""
	}

	textParts := strings.Split(lines[0].Name, " (")

	return textParts[0]
}

func (s *Scraper) parsePageTitle(page *colly.HTMLElement) string {
	heading := page.DOM.Find("h1#firstHeading")
	if heading.Length() > 0 {
		note := heading.Find("small")
		if note.Length() > 0 {
			return strings.TrimSpace(strings.Replace(heading.Text(), note.Text(), "", 1))
		}

		return strings.TrimSpace(heading.Text())
	}

	infobox := s.findPageInfobox(page)

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

// Get vehicle category based on page template.
// See:
// - https://starwars.fandom.com/wiki/Category:Vehicle_infobox_templates;
// - https://starwars.fandom.com/wiki/Template:Starship_class;
// - https://starwars.fandom.com/wiki/Template:Space_station.
func (s *Scraper) parseVehicleCategory(infoboxSelection *goquery.Selection) string {
	var template string

	url, exists := infoboxSelection.Find(".plainlinks > a").Attr("href")
	if exists {
		template = s.parsePageUrlTemplateName(url)
	}

	switch template {
	case string(templateAirVehicle):
		return "Air"
	case string(templateAquaticVehicle):
		return "Aquatic"
	case string(templateGroundVehicle):
		return "Ground"
	case string(templateRepulsorliftVehicle):
		return "Repulsorlift"
	case string(templateSpaceStation):
		return "Space station"
	case string(templateStarshipClass):
		return "Starship"
	case string(templateVehicle):
		return "Other"
	default:
		return ""
	}
}

func (s *Scraper) parseImageUrl(infoboxSelection *goquery.Selection) string {
	imageSelection := infoboxSelection.Find(`figure[data-source="image"]`)
	if imageSelection.Length() == 0 {
		return ""
	}

	if href, exists := imageSelection.ChildrenFiltered("a").First().Attr("href"); exists {
		return href
	}

	return ""
}

func (s *Scraper) parseVehicleLine(infoboxSelection *goquery.Selection) string {
	lineSelection := infoboxSelection.Find(`div[data-source="line"]`)
	if lineSelection.Length() == 0 {
		return ""
	}

	lines := s.parseInfoboxDataSource(lineSelection)
	if len(lines) == 0 {
		return ""
	}

	replacer := strings.NewReplacer(" series", "", "-series", "", " line", "", "-line", "")
	return replacer.Replace(lines[0].Name)
}

func (s *Scraper) parseVehicleType(infoboxSelection *goquery.Selection) string {
	typeSelection := infoboxSelection.Find(`div[data-source="type"]`)
	if typeSelection.Length() == 0 {
		return ""
	}

	types := s.parseInfoboxDataSource(typeSelection)
	if len(types) == 0 {
		return ""
	}

	return types[0].Name
}

func (s *Scraper) parsePageText(page *colly.HTMLElement) string {
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

		// if there is a paragraph, just grab it's text
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

func (s *Scraper) parseVehicleManufacturers(infoboxSelection *goquery.Selection) []vehicle.AdditionalDataDTO {
	manufacturersData := infoboxSelection.Find(`div[data-source="manufacturer"]`)
	if manufacturersData.Length() == 0 {
		return []vehicle.AdditionalDataDTO{}
	}

	return s.parseInfoboxDataSource(manufacturersData)
}

func (s *Scraper) parseVehicleFactions(infoboxSelection *goquery.Selection) []vehicle.AdditionalDataDTO {
	factionsData := infoboxSelection.Find(`div[data-source="affiliation"]`)
	if factionsData.Length() == 0 {
		return []vehicle.AdditionalDataDTO{}
	}

	return s.parseInfoboxDataSource(factionsData)
}

func (s *Scraper) parseVehicleTechnicalSpecifications(infoboxSelection *goquery.Selection) []vehicle.TechSpecDTO {
	specs := []vehicle.TechSpecDTO{}

	dataSources := map[string]string{
		"Length":                    "length",
		"Width":                     "width",
		"Height":                    "height",
		"Diameter":                  "diameter",
		"Maximum acceleration":      "max accel",
		"Maximum speed":             "max speed",
		"Maximum atmospheric speed": "max speed",
		"MGLT":                      "mglt",
		"Hyperdrive rating":         "hyperdrive",
	}

	var selector string
	var dataSelection *goquery.Selection
	var data []vehicle.AdditionalDataDTO

	for specName, sourceName := range dataSources {
		selector = helpers.ConcatStrings(`div[data-source="`, sourceName, `"]`)
		if dataSelection = infoboxSelection.Find(selector); dataSelection.Length() == 0 {
			continue
		}

		if data = s.parseInfoboxDataSource(dataSelection); len(data) == 0 {
			continue
		}

		textParts := strings.Split(data[0].Name, " (")
		value := textParts[0]

		replacer := strings.NewReplacer("≈", "", "±", "", ",", "")

		specs = append(specs, vehicle.TechSpecDTO{
			Name:  specName,
			Value: strings.TrimSpace(replacer.Replace(value)),
		})
	}

	return specs
}

// Parse [data-source="*"] items from "aside.portable-infobox" element.
func (s *Scraper) parseInfoboxDataSource(selection *goquery.Selection) []vehicle.AdditionalDataDTO {
	dto := []vehicle.AdditionalDataDTO{}
	if selection.Length() == 0 {
		return dto
	}

	valueSelection := selection.ChildrenFiltered(".pi-data-value")

	// could be nested <ul>...
	if list := valueSelection.ChildrenFiltered("ul"); list.Length() > 0 {
		return s.parseInfoboxNestedList(list)
	}

	// ...or single text value
	item := s.parseInfoboxItem(valueSelection)
	if item.Name != "" {
		dto = append(dto, item)
	}

	return dto
}

// Parse "aside.portable-infobox" nested ul.
func (s *Scraper) parseInfoboxNestedList(selection *goquery.Selection) []vehicle.AdditionalDataDTO {
	var items []vehicle.AdditionalDataDTO
	selection.Children().Each(func(i int, sel *goquery.Selection) {
		item := s.parseInfoboxItem(sel)

		if item.Name != "" {
			items = append(items, item)
		}
	})

	return items
}

// Parse "aside.portable-infobox" single text value.
func (s *Scraper) parseInfoboxItem(selection *goquery.Selection) vehicle.AdditionalDataDTO {
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

	children := []vehicle.AdditionalDataDTO{}
	if nestedList := selection.ChildrenFiltered("ul"); nestedList.Length() > 0 {
		children = s.parseInfoboxNestedList(nestedList)
	}

	return vehicle.AdditionalDataDTO{
		Name:     value,
		Note:     core.NullableString(note),
		Children: children,
	}
}

// Check if Colly returns an error for already visited page.
func (s *Scraper) isErrorAlreadyVisited(err error) bool {
	return errors.As(err, &alreadyVisitedError)
}

func (s *Scraper) parsePageUrlTemplateName(url string) string {
	return strings.Replace(url, "https://starwars.fandom.com/wiki/Template:", "", 1)
}

// Check if page contains information about vehicle by searching for template url.
// Example: https://starwars.fandom.com/wiki/Template:Starship_class.
func (s *Scraper) isVehiclePage(page *colly.HTMLElement) bool {
	pageTemplates := []PageTemplate{
		templateAirVehicle,
		templateAquaticVehicle,
		templateGroundVehicle,
		templateRepulsorliftVehicle,
		templateSpaceStation,
		templateStarshipClass,
		templateVehicle,
	}

	return s.isPageOneOfTemplates(page, pageTemplates)
}

// Check if page contains information about appearance by searching for template url.
// Example: https://starwars.fandom.com/wiki/Template:Video_game.
func (s *Scraper) isAppearancePage(page *colly.HTMLElement) bool {
	pageTemplates := []PageTemplate{
		templateMovie,
		templateTvSeries,
		templateVideoGame,
		templateBook,
		templateComicBook,
	}

	return s.isPageOneOfTemplates(page, pageTemplates)
}

func (s *Scraper) isPageOneOfTemplates(page *colly.HTMLElement, pageTemplates []PageTemplate) bool {
	url, exists := s.findPageInfobox(page).Find(".plainlinks > a").Attr("href")
	if !exists {
		return false
	}

	template := s.parsePageUrlTemplateName(url)

	for _, t := range pageTemplates {
		if string(t) == template {
			return true
		}
	}

	return false
}

// Check if page contains canon information.
// First, search for "#canontab", inside find "#canontab-canon_ctcw" (active tab for "Canon") and check if current page url equals active tab url.
// Otherwise search for "#title-eraicons" element, find image and check if "alt" attribute value ends with "is considered canon".
func (s *Scraper) isCanonPage(page *colly.HTMLElement) bool {
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

// One page could have two instances: one for "canon", other for "legends".
// They share the same information and can complement each other.
func (s *Scraper) parseCanonRelatedURL(page *colly.HTMLElement) string {
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

func (s *Scraper) parseAppearances(page *colly.HTMLElement) []vehicle.AppearanceDTO {
	var appearances []vehicle.AppearanceDTO

	heading := page.DOM.Find("#Appearances")
	if heading.Length() == 0 {
		return appearances
	}

	list := heading.Parent().Next()
	// could be some element between the heading and the list
	if !list.Is(".responsivediv") && !list.Is("ul") {
		list = list.Next()
	}

	listItems := list.Find("li")
	if listItems.Length() == 0 {
		return appearances
	}

	appearancePageCollector := s.newCollector(10)

	var dto vehicle.AppearanceDTO
	appearancePageCollector.OnHTML("main.page__main", func(page *colly.HTMLElement) {
		dto = s.scrapeWorkOfArtPage(page)
	})

	listItems.Each(func(index int, appearance *goquery.Selection) {
		url, exists := appearance.Find("a:first-of-type").Attr("href")
		if !exists {
			return
		}

		appearancePageCollector.Visit(page.Request.AbsoluteURL(url))
		appearancePageCollector.Wait()

		if dto.Name == "" {
			return
		}

		// check notes - it must be empty or "First appearance" / "DLC" (for games) only
		// skip every other "Mentioned only", "Cover only", "In flashback(s)", "Appears in hologram" etc.
		textParts := strings.Split(strings.TrimSpace(appearance.Text()), " (")
		if len(textParts) > 1 {
			note := strings.ToLower(strings.TrimSpace(strings.Trim(textParts[1], ")")))
			if (note != "first appearance") && (note != "dlc") {
				return
			}

			dto.IsFirst = (note == "first appearance")
		}

		// don't add same item multiple times, set "IsFirst" only
		for i, appearance := range appearances {
			if appearance.Name == dto.Name {
				if !appearance.IsFirst && dto.IsFirst {
					appearances[i].IsFirst = dto.IsFirst
				}

				return
			}
		}

		appearances = append(appearances, dto)
	})

	return appearances
}
