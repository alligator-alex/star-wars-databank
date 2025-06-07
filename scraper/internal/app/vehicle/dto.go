package vehicle

import (
	"sw-vehicles/internal/app/appearance"
	"sw-vehicles/internal/app/core"
)

// DTO serves content from a Vehicle page.
type DTO struct {
	Name                    string                   `json:"name"`
	Category                string                   `json:"category"`
	Line                    core.NullableString      `json:"line"`
	Type                    core.NullableString      `json:"type"`
	ImageURL                string                   `json:"imageUrl"`
	Description             string                   `json:"description"`
	URL                     string                   `json:"url"`
	RelatedURL              core.NullableString      `json:"relatedUrl"`
	Manufacturers           []core.AdditionalDataDTO `json:"manufacturers"`
	Factions                []core.AdditionalDataDTO `json:"factions"`
	TechnicalSpecifications []TechSpecDTO            `json:"technicalSpecifications"`
	IsCanon                 bool                     `json:"isCanon"`
	Appearances             []appearance.DTO         `json:"appearances"`
}

// TechSpecDTO serves technical specification item content.
type TechSpecDTO struct {
	Name  string `json:"name"`
	Value string `json:"value"`
}
