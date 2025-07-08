package vehicle

import "sw-vehicles/internal/app/core"

// DTO serves content from a Vehicle page.
type DTO struct {
	EntityType              core.EntityType          `json:"entityType"`
	MainInfo                core.MainInfoDTO         `json:"mainInfo"`
	Category                string                   `json:"category"`
	Line                    core.NullableString      `json:"line"`
	Type                    core.NullableString      `json:"type"`
	Manufacturers           []core.AdditionalDataDTO `json:"manufacturers"`
	TechnicalSpecifications []TechSpecDTO            `json:"technicalSpecifications"`
	Factions                []core.AdditionalDataDTO `json:"factions"`
	Appearances             []core.AppearanceDTO     `json:"appearances"`
}

// TechSpecDTO serves technical specification item content.
type TechSpecDTO struct {
	Name  string `json:"name"`
	Value string `json:"value"`
}
