package droid

import "sw-vehicles/internal/app/core"

// DTO serves content from a Droid page.
type DTO struct {
	EntityType              core.EntityType          `json:"entityType"`
	MainInfo                core.MainInfoDTO         `json:"mainInfo"`
	Line                    core.NullableString      `json:"line"`
	Model                   core.NullableString      `json:"model"`
	Class                   core.NullableString      `json:"class"`
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
