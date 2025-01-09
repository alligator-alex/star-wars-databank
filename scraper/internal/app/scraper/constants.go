package scraper

const urlPadSize int = 120
const urlPadSymbol string = "."

const (
	templateAirVehicle          PageTemplate = "Air_vehicle"
	templateAquaticVehicle      PageTemplate = "Aquatic_vehicle"
	templateGroundVehicle       PageTemplate = "Ground_vehicle"
	templateRepulsorliftVehicle PageTemplate = "Repulsorlift_vehicle"
	templateSpaceStation        PageTemplate = "Space_station"
	templateStarshipClass       PageTemplate = "Starship_class"
	templateVehicle             PageTemplate = "Vehicle"

	templateMovie     PageTemplate = "Movie"
	templateTvSeries  PageTemplate = "Television_series"
	templateVideoGame PageTemplate = "Video_game"
	templateBook      PageTemplate = "Book"
	templateComicBook PageTemplate = "Comic_book"
)

const firstNavPageUrl string = "https://starwars.fandom.com/wiki/Special:AllPages?from="
