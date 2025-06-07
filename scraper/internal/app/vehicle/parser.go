package vehicle

import (
	"github.com/PuerkitoBio/goquery"
	"github.com/gocolly/colly/v2"
	"strings"
	"sw-vehicles/internal/app/appearance"
	"sw-vehicles/internal/app/core"
	"sw-vehicles/internal/app/helpers"
)

// Parser is a specific parser for the vehicle page.
// Extends core.Parser.
type Parser struct {
	core.Parser
}

func NewParser() Parser {
	return Parser{}
}

// Parse starts the parsing process.
func (p *Parser) Parse(page *colly.HTMLElement, appearances []appearance.DTO) DTO {
	infobox := p.FindPageInfobox(page)

	return DTO{
		Name:                    p.ParsePageTitle(page),
		Category:                p.parseCategory(infobox),
		Line:                    core.NullableString(p.parseLine(infobox)),
		Type:                    core.NullableString(p.parseType(infobox)),
		ImageURL:                p.ParseImageUrl(infobox),
		Description:             p.ParsePageText(page),
		URL:                     page.Request.URL.Scheme + "://" + page.Request.URL.Host + page.Request.URL.Path,
		RelatedURL:              core.NullableString(p.ParseCanonRelatedURL(page)),
		Manufacturers:           p.parseManufacturers(infobox),
		Factions:                p.parseFactions(infobox),
		TechnicalSpecifications: p.parseTechnicalSpecifications(infobox),
		IsCanon:                 p.IsCanonPage(page),
		Appearances:             appearances,
	}
}

func (p *Parser) parseCategory(infoboxSelection *goquery.Selection) string {
	var template string

	url, exists := infoboxSelection.Find(".plainlinks > a").Attr("href")
	if exists {
		template = p.ExtractPageUrlTemplateName(url)
	}

	switch template {
	case string(TemplateAirVehicle):
		return "Air"
	case string(TemplateAquaticVehicle):
		return "Aquatic"
	case string(TemplateGroundVehicle):
		return "Ground"
	case string(TemplateRepulsorliftVehicle):
		return "Repulsorlift"
	case string(TemplateSpaceStation):
		return "Space station"
	case string(TemplateStarshipClass):
		return "Starship"
	case string(TemplateVehicle):
		return "Other"
	default:
		return ""
	}
}

func (p *Parser) parseLine(infoboxSelection *goquery.Selection) string {
	lineSelection := infoboxSelection.Find(`div[data-source="line"]`)
	if lineSelection.Length() == 0 {
		return ""
	}

	lines := p.ParseInfoboxDataSource(lineSelection)
	if len(lines) == 0 {
		return ""
	}

	replacer := strings.NewReplacer(" series", "", "-series", "", " line", "", "-line", "")
	return replacer.Replace(lines[0].Name)
}

func (p *Parser) parseType(infoboxSelection *goquery.Selection) string {
	typeSelection := infoboxSelection.Find(`div[data-source="type"]`)
	if typeSelection.Length() == 0 {
		return ""
	}

	types := p.ParseInfoboxDataSource(typeSelection)
	if len(types) == 0 {
		return ""
	}

	return types[0].Name
}

func (p *Parser) parseManufacturers(infoboxSelection *goquery.Selection) []core.AdditionalDataDTO {
	manufacturersData := infoboxSelection.Find(`div[data-source="manufacturer"]`)
	if manufacturersData.Length() == 0 {
		return []core.AdditionalDataDTO{}
	}

	return p.ParseInfoboxDataSource(manufacturersData)
}

func (p *Parser) parseFactions(infoboxSelection *goquery.Selection) []core.AdditionalDataDTO {
	factionsData := infoboxSelection.Find(`div[data-source="affiliation"]`)
	if factionsData.Length() == 0 {
		return []core.AdditionalDataDTO{}
	}

	return p.ParseInfoboxDataSource(factionsData)
}

func (p *Parser) parseTechnicalSpecifications(infoboxSelection *goquery.Selection) []TechSpecDTO {
	var specs []TechSpecDTO

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
	var data []core.AdditionalDataDTO

	for specName, sourceName := range dataSources {
		selector = helpers.ConcatStrings(`div[data-source="`, sourceName, `"]`)
		if dataSelection = infoboxSelection.Find(selector); dataSelection.Length() == 0 {
			continue
		}

		if data = p.ParseInfoboxDataSource(dataSelection); len(data) == 0 {
			continue
		}

		textParts := strings.Split(data[0].Name, " (")
		value := textParts[0]

		replacer := strings.NewReplacer("≈", "", "±", "", ",", "")

		specs = append(specs, TechSpecDTO{
			Name:  specName,
			Value: strings.TrimSpace(replacer.Replace(value)),
		})
	}

	return specs
}
