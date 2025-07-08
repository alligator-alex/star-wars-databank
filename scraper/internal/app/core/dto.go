package core

// MainInfoDTO serves main information shared between any entity (vehicle, droid, weapon, etc.).
type MainInfoDTO struct {
	Name        string         `json:"name"`
	ImageURL    string         `json:"imageUrl"`
	Description string         `json:"description"`
	URL         string         `json:"url"`
	RelatedURL  NullableString `json:"relatedUrl"`
	IsCanon     bool           `json:"isCanon"`
}

// AppearanceDTO serves appearance content (movie, series, game, etc.).
type AppearanceDTO struct {
	Name        string         `json:"name"`
	URL         string         `json:"url"`
	ImageURL    string         `json:"imageUrl"`
	Type        NullableString `json:"type"`
	ReleaseDate NullableString `json:"releaseDate"`
	IsFirst     bool           `json:"isFirst"`
}

// AdditionalDataDTO serves any unspecified additional data, like "Manufacturers", "Factions", etc.
type AdditionalDataDTO struct {
	Name     string              `json:"name"`
	Note     NullableString      `json:"note"`
	Children []AdditionalDataDTO `json:"children"`
}
