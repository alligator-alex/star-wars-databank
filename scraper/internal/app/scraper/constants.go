package scraper

const urlPadSize int = 120
const urlPadSymbol string = "."

const firstNavPageUrl string = "https://starwars.fandom.com/wiki/Special:AllPages?from="

const (
	pageTypeUnsupported = iota // 0
	pageTypeAppearance
	pageTypeVehicle
	pageTypeDroid
)
