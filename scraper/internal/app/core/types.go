package core

type NullableString string

// MarshalJSON encodes empty string as null.
func (s NullableString) MarshalJSON() ([]byte, error) {
	if s == "" {
		return []byte("null"), nil
	}

	result := make([]byte, 0, len(s)+2)

	result = append(result, '"')
	result = append(result, []byte(s)...)
	result = append(result, '"')

	return result, nil
}

type PageTemplate string

type InvalidPageTemplateError struct {
}

func (e *InvalidPageTemplateError) Error() string {
	return "This page has an invalid template"
}
