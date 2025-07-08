package droid

import (
	"github.com/PuerkitoBio/goquery"
	"github.com/gocolly/colly/v2"
	"strings"
	"sw-vehicles/internal/app/core"
	"sw-vehicles/internal/app/helpers"
)

// Parser is a specific parser for the droid page.
// Extends core.Parser.
type Parser struct {
	core.Parser
}

func NewParser() Parser {
	return Parser{}
}

// Parse starts the parsing process of the droid page.
func (p *Parser) Parse(page *colly.HTMLElement, appearances []core.AppearanceDTO) DTO {
	infobox := p.FindPageInfobox(page)

	return DTO{
		EntityType:              core.EntityTypeDroid,
		MainInfo:                p.GetMainInfo(page),
		Line:                    core.NullableString(p.parseLine(infobox)),
		Model:                   core.NullableString(p.parseModel(infobox)),
		Class:                   core.NullableString(p.parseClass(infobox)),
		Manufacturers:           p.parseManufacturers(infobox),
		TechnicalSpecifications: p.parseTechnicalSpecifications(infobox),
		Factions:                p.ParseFactions(infobox),
		Appearances:             appearances,
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

func (p *Parser) parseModel(infoboxSelection *goquery.Selection) string {
	typeSelection := infoboxSelection.Find(`div[data-source="model"]`)
	if typeSelection.Length() == 0 {
		return ""
	}

	types := p.ParseInfoboxDataSource(typeSelection)
	if len(types) == 0 {
		return ""
	}

	return types[0].Name
}

func (p *Parser) parseClass(infoboxSelection *goquery.Selection) string {
	typeSelection := infoboxSelection.Find(`div[data-source="class"]`)
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

func (p *Parser) parseTechnicalSpecifications(infoboxSelection *goquery.Selection) []TechSpecDTO {
	var specs []TechSpecDTO

	dataSources := map[string]string{
		"Height": "height",
		"Mass":   "mass",
		"Gender": "gender",
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
