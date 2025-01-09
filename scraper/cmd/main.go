package main

import (
	"log"
	"os"
	"path/filepath"
	"sw-vehicles/internal/app/broker"
	"sw-vehicles/internal/app/helpers"
	"sw-vehicles/internal/app/scraper"

	"github.com/joho/godotenv"
)

func main() {
	if err := loadEnv(); err != nil {
		log.Fatalln("Unable to load .env file:", err)
	}

	runScraper()
}

func loadEnv() error {
	rootDir, err := helpers.GetRootDir()
	if err != nil {
		return err
	}

	envPath := filepath.Join(rootDir, ".env")

	return godotenv.Load(envPath)
}

func runScraper() {
	conf := broker.NewConfig(
		os.Getenv("RABBITMQ_HOST"),
		os.Getenv("RABBITMQ_PORT"),
		os.Getenv("RABBITMQ_USER"),
		os.Getenv("RABBITMQ_PASSWORD"),
	)

	brokerClient := broker.NewRabbitMQClient(conf, os.Getenv("RABBITMQ_EXCHANGE"))
	defer brokerClient.Stop()

	app := scraper.NewScraper(&brokerClient)
	app.Run(os.Getenv("NAV_PAGE_CONTINUE_FROM"))
}
