package appearance

import "sw-vehicles/internal/app/core"

// DTO serves appearance content (movie, series, game, etc.).
type DTO struct {
	Name        string              `json:"name"`
	URL         string              `json:"url"`
	ImageURL    string              `json:"imageUrl"`
	Type        core.NullableString `json:"type"`
	ReleaseDate core.NullableString `json:"releaseDate"`
	IsFirst     bool                `json:"isFirst"`
}
