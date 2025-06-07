package helpers

import "strings"

// ConcatStrings concatenate multiple strings into one.
func ConcatStrings(values ...string) string {
	var builder strings.Builder

	for _, value := range values {
		builder.WriteString(value)
	}

	return builder.String()
}

// PadStringRight pads a string to the right to a certain length with given symbol.
func PadStringRight(str string, length int, symbol string) string {
	if len(symbol) == 0 {
		symbol = " "
	}

	diff := length - len(str)
	if diff <= 0 {
		return str
	}

	var builder strings.Builder

	builder.WriteString(str)
	for i := 1; i < diff; i++ {
		builder.WriteString(symbol)
	}

	return builder.String()
}
