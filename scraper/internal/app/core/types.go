package core

type NullableString string

// Mashal empty string as null.
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
