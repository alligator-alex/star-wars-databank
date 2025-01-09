package logger

import (
	"fmt"
	"log"
	"os"
	"path/filepath"
	"strings"
	"sw-vehicles/internal/app/helpers"
)

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

func (logger FileLogger) Log(message ...any) {
	logFile, err := os.OpenFile(logger.path, os.O_APPEND|os.O_RDWR|os.O_CREATE, 0644)

	if err != nil {
		log.Panic(err)
	}

	defer logFile.Close()

	log.SetOutput(logFile)

	if !logger.isSilent {
		fmt.Println(message...)
	}

	log.Println(message...)
}
