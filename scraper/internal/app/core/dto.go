package core

// AdditionalDataDTO serves any unspecified additional data, like "Manufacturers", "Factions", etc.
type AdditionalDataDTO struct {
	Name     string              `json:"name"`
	Note     NullableString      `json:"note"`
	Children []AdditionalDataDTO `json:"children"`
}
