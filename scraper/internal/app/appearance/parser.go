package appearance

import (
	"github.com/PuerkitoBio/goquery"
	"github.com/gocolly/colly/v2"
	"strings"
	"sw-vehicles/internal/app/core"
)

// Parser is a specific parser for the appearance page.
// Extends core.Parser.
type Parser struct {
	core.Parser
}

func NewParser() Parser {
	return Parser{}
}

// Parse starts the parsing process of the appearance page.
func (p *Parser) Parse(page *colly.HTMLElement) core.AppearanceDTO {
	infobox := p.FindPageInfobox(page)

	return core.AppearanceDTO{
		Name:        p.ParsePageTitle(page),
		URL:         page.Request.URL.String(),
		ImageURL:    p.ParseMainImageUrl(infobox),
		Type:        core.NullableString(p.parseWorkOfArtType(infobox)),
		ReleaseDate: core.NullableString(p.parseWorkOfArtReleaseDate(infobox)),
	}
}

func (p *Parser) parseWorkOfArtType(infoboxSelection *goquery.Selection) string {
	var template string

	url, exists := infoboxSelection.Find(".plainlinks > a").Attr("href")
	if exists {
		template = p.ExtractPageUrlTemplateName(url)
	}

	switch template {
	case string(TemplateMovie):
		return "Movie"
	case string(TemplateTvSeries):
		return "Series"
	case string(TemplateVideoGame):
		return "Game"
	case string(TemplateBook):
		return "Book"
	case string(TemplateComicBook):
		return "ComicBook"
	default:
		return ""
	}
}

func (p *Parser) parseWorkOfArtReleaseDate(infoboxSelection *goquery.Selection) string {
	dateSelection := infoboxSelection.Find(`div[data-source="release date"]`)
	if dateSelection.Length() == 0 {
		dateSelection = infoboxSelection.Find(`div[data-source="first aired"]`)
	}

	if dateSelection.Length() == 0 {
		return ""
	}

	lines := p.ParseInfoboxDataSource(dateSelection)
	if len(lines) == 0 {
		return ""
	}

	textParts := strings.Split(lines[0].Name, " (")

	return textParts[0]
}
