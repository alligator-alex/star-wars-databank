package helpers

import "strings"

// Concatenate multiple strings into one.
func ConcatStrings(values ...string) string {
	var builder strings.Builder

	for _, value := range values {
		builder.WriteString(value)
	}

	return builder.String()
}

// Pad a string to the right to a certain length with another string.
func PadStringRight(str string, length int, padStr string) string {
	if len(padStr) == 0 {
		padStr = " "
	}

	diff := length - len(str)
	if diff <= 0 {
		return str
	}

	var builder strings.Builder

	builder.WriteString(str)
	for i := 1; i < diff; i++ {
		builder.WriteString(padStr)
	}

	return builder.String()
}
