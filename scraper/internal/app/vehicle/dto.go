package vehicle

import "sw-vehicles/internal/app/core"

type AdditionalDataDTO struct {
	Name     string              `json:"name"`
	Note     core.NullableString `json:"note"`
	Children []AdditionalDataDTO `json:"children"`
}

type TechSpecDTO struct {
	Name  string `json:"name"`
	Value string `json:"value"`
}

type AppearanceDTO struct {
	Name        string              `json:"name"`
	URL         string              `json:"url"`
	ImageURL    string              `json:"imageUrl"`
	Type        core.NullableString `json:"type"`
	ReleaseDate core.NullableString `json:"releaseDate"`
	IsFirst     bool                `json:"isFirst"`
}

type VehicleDTO struct {
	Name                    string              `json:"name"`
	Category                string              `json:"category"`
	Line                    core.NullableString `json:"line"`
	Type                    core.NullableString `json:"type"`
	ImageURL                string              `json:"imageUrl"`
	Description             string              `json:"description"`
	URL                     string              `json:"url"`
	RelatedURL              core.NullableString `json:"relatedUrl"`
	Manufacturers           []AdditionalDataDTO `json:"manufacturers"`
	Factions                []AdditionalDataDTO `json:"factions"`
	TechnicalSpecifications []TechSpecDTO       `json:"technicalSpecifications"`
	IsCanon                 bool                `json:"isCanon"`
	Appearances             []AppearanceDTO     `json:"appearances"`
}
