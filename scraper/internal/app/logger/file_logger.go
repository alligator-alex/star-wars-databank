package logger

import (
	"fmt"
	"log"
	"os"
	"path/filepath"
	"strings"
	"sw-vehicles/internal/app/helpers"
)

// FileLogger is a wrapper for fmt.Println() function,
// but it also writes the message to a file.
type FileLogger struct {
	path     string
	isSilent bool
}

func NewFileLogger(fileName string, isSilent bool) FileLogger {
	rootDir, err := helpers.GetRootDir()

	if err != nil {
		fmt.Println("Unable to initialize logger:", err)
	}

	if !strings.HasSuffix(fileName, ".log") {
		helpers.ConcatStrings(fileName, ".log")
	}

	return FileLogger{
		path:     filepath.Join(rootDir, "logs", fileName),
		isSilent: isSilent,
	}
}

// Log prints a message to the file.
func (logger FileLogger) Log(message ...any) {
	logFile, err := os.OpenFile(logger.path, os.O_APPEND|os.O_RDWR|os.O_CREATE, 0644)

	if err != nil {
		log.Panic(err)
	}

	defer func(logFile *os.File) {
		err := logFile.Close()
		if err != nil {
			panic(err)
		}
	}(logFile)

	log.SetOutput(logFile)

	if !logger.isSilent {
		fmt.Println(message...)
	}

	log.Println(message...)
}
