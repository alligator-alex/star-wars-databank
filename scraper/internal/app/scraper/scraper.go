package scraper

import (
	"encoding/json"
	"errors"
	"fmt"
	"os"
	"strings"
	"sw-vehicles/internal/app/appearance"
	"sw-vehicles/internal/app/core"
	"sw-vehicles/internal/app/helpers"
	"sw-vehicles/internal/app/logger"
	"sw-vehicles/internal/app/vehicle"
	"time"

	"github.com/PuerkitoBio/goquery"
	"github.com/gocolly/colly/v2"
	"github.com/gocolly/colly/v2/extensions"
	"github.com/gocolly/colly/v2/queue"
)

type BrokerClient interface {
	Publish(body string)
	Stop()
}

var alreadyVisitedError *colly.AlreadyVisitedError

type Scraper struct {
	logger              logger.FileLogger
	brokerClient        BrokerClient
	navPageCollector    *colly.Collector
	detailPageCollector *colly.Collector
	navPageQueue        *queue.Queue
	detailPageQueue     *queue.Queue
	pageNum             int
	totalVisited        int
	totalScraped        int
	appearanceParser    appearance.Parser
	vehicleParser       vehicle.Parser
}

func NewScraper(brokerClient BrokerClient) Scraper {
	scraper := Scraper{
		logger:           logger.NewFileLogger("scraper.log", false),
		brokerClient:     brokerClient,
		appearanceParser: appearance.NewParser(),
		vehicleParser:    vehicle.NewParser(),
	}

	return scraper
}

// Run starts scraping of every page from the Wookieepedia.
func (s *Scraper) Run(continueFrom string) {
	s.navPageCollector = s.newCollector(0)
	s.navPageQueue = s.newQueue(1)
	s.registerNavPageCollectorEvents()

	s.detailPageCollector = s.newCollector(10)
	s.detailPageQueue = s.newQueue(20)
	s.registerDetailPageCollectorEvents()

	s.logger.Log("Start scraping")

	err := s.navPageQueue.AddURL(firstNavPageUrl + continueFrom)
	if err != nil {
		s.logger.Log("Unable to add Nav page URL:", err)
	}

	err = s.navPageQueue.Run(s.navPageCollector)
	if err != nil {
		s.logger.Log("Unable to run nav page queue:", err)
	}

	s.stop()

	s.logger.Log("Scraping complete", "\nVisited:", s.totalVisited, "\nScraped:", s.totalScraped)
}

func (s *Scraper) stop() {
	s.brokerClient.Publish("<<< THE END >>>")
}

func (s *Scraper) newCollector(randomDelay int) *colly.Collector {
	collector := colly.NewCollector(
		colly.AllowedDomains("starwars.fandom.com"),
		colly.DisallowedDomains("fandom.com"), // don't go any deeper
	)

	if randomDelay > 0 {
		err := collector.Limit(&colly.LimitRule{
			RandomDelay: time.Duration(randomDelay) * time.Second,
		})

		if (err != nil) && !errors.Is(err, colly.ErrNoPattern) {
			s.logger.Log("Unable to create collector:", err)
			panic(err)
		}
	}

	extensions.RandomUserAgent(collector)
	extensions.Referer(collector)

	return collector
}

func (s *Scraper) newQueue(threads int) *queue.Queue {
	var q *queue.Queue
	var err error

	if threads < 1 {
		threads = 1
	}

	q, err = queue.New(threads, &queue.InMemoryQueueStorage{MaxSize: 10000})

	if err != nil {
		fmt.Println("Unable to init queue:", err)
		os.Exit(1)
	}

	return q
}

func (s *Scraper) registerNavPageCollectorEvents() {
	s.pageNum = 1

	s.navPageCollector.OnRequest(func(req *colly.Request) {
		s.logger.Log("Navigating page", s.pageNum, "-", req.URL.String())
	})

	s.navPageCollector.OnError(func(r *colly.Response, err error) {
		if s.isErrorAlreadyVisited(err) {
			return
		}

		s.logger.Log("Error:", err)
	})

	s.navPageCollector.OnHTML("main.page__main", func(page *colly.HTMLElement) {
		s.scrapeNavPage(page)
	})
}

func (s *Scraper) registerDetailPageCollectorEvents() {
	s.detailPageCollector.OnResponse(func(resp *colly.Response) {
		s.totalVisited++

		if resp.StatusCode != 200 {
			s.logger.Log("-", helpers.PadStringRight(resp.Request.URL.String(), urlPadSize, urlPadSymbol), "invalid HTTP status:", resp.StatusCode)
		}
	})

	s.detailPageCollector.OnError(func(resp *colly.Response, err error) {
		if s.isErrorAlreadyVisited(err) {
			return
		}

		s.logger.Log("-", helpers.PadStringRight(resp.Request.URL.String(), urlPadSize, urlPadSymbol), "unknown error:", err)
	})

	s.detailPageCollector.OnHTML("main.page__main", func(page *colly.HTMLElement) {
		s.scrapeDetailPage(page)
	})
}

func (s *Scraper) scrapeNavPage(page *colly.HTMLElement) {
	s.logger.Log("Collecting detail pages...")

	var detailPagesUrls []string
	page.ForEach(".mw-allpages-body a[href]", func(i int, el *colly.HTMLElement) {
		detailPagesUrls = append(detailPagesUrls, el.Request.AbsoluteURL(el.Attr("href")))
	})

	detailPagesFound := len(detailPagesUrls)
	if detailPagesFound == 0 {
		s.logger.Log("No detail pages found, exiting")
		os.Exit(1)
	}

	s.logger.Log("Found", detailPagesFound, "more pages")

	for _, u := range detailPagesUrls {
		err := s.detailPageQueue.AddURL(u)
		if err != nil {
			s.logger.Log("Unable to add detail page URL:", err)
			continue
		}
	}

	s.logger.Log("Starting detail page queue")

	err := s.detailPageQueue.Run(s.detailPageCollector)
	if err != nil {
		s.logger.Log("Unable to run detail page queue:", err)
		return
	}

	s.detailPageQueue.Stop()
	s.logger.Log("Detail page queue completed")

	// try to find a link leading to the next page
	s.logger.Log("Looking for the next page...")

	nextPageUrl := firstNavPageUrl
	page.ForEach(".mw-allpages-nav > a[href]", func(i int, el *colly.HTMLElement) {
		url := el.Request.AbsoluteURL(el.Attr("href"))
		if url == "" {
			return
		}

		if !strings.HasPrefix(url, firstNavPageUrl) {
			return
		}

		if !strings.HasPrefix(el.Text, "Next page") {
			return
		}

		// there are two ".mw-allpages-nav" elements on the page
		// so we must cache first matched url
		if nextPageUrl != url {
			nextPageUrl = url

			s.pageNum++

			s.logger.Log("Found next page:", nextPageUrl)

			s.navPageQueue.Stop()

			err := s.navPageQueue.AddURL(nextPageUrl)
			if err != nil {
				s.logger.Log("Unable to add next page URL:", err)
				return
			}

			err = s.navPageQueue.Run(s.navPageCollector)
			if err != nil {
				s.logger.Log("Unable to run nav page queue:", err)
				return
			}
		}
	})

	s.logger.Log("No next page found")

	s.navPageQueue.Stop()
}

func (s *Scraper) scrapeDetailPage(page *colly.HTMLElement) {
	paddedUrl := helpers.PadStringRight(page.Request.URL.String(), urlPadSize, urlPadSymbol)

	if !s.isVehiclePage(page) {
		s.logger.Log("-", paddedUrl, "skipped (not a vehicle)")
		return
	}

	appearances := s.collectAppearances(page)
	if len(appearances) == 0 {
		s.logger.Log("-", paddedUrl, "skipped (no appearances)")
		return
	}

	dto := s.vehicleParser.Parse(page, appearances)

	if err := s.publishToBroker(dto); err != nil {
		s.logger.Log("-", paddedUrl, "unable to publish to broker:", err)
		return
	}

	s.totalScraped++
	s.logger.Log("-", paddedUrl, "scraped")
}

func (s *Scraper) publishToBroker(dto interface{}) error {
	data, err := json.Marshal(dto)
	if err != nil {
		return err
	}

	s.brokerClient.Publish(string(data))

	return nil
}

func (s *Scraper) isErrorAlreadyVisited(err error) bool {
	return errors.As(err, &alreadyVisitedError)
}

func (s *Scraper) isVehiclePage(page *colly.HTMLElement) bool {
	pageTemplates := []core.PageTemplate{
		vehicle.TemplateAirVehicle,
		vehicle.TemplateAquaticVehicle,
		vehicle.TemplateGroundVehicle,
		vehicle.TemplateRepulsorliftVehicle,
		vehicle.TemplateSpaceStation,
		vehicle.TemplateStarshipClass,
		vehicle.TemplateVehicle,
	}

	return s.vehicleParser.IsPageOneOfTemplates(page, pageTemplates)
}

func (s *Scraper) isAppearancePage(page *colly.HTMLElement) bool {
	pageTemplates := []core.PageTemplate{
		appearance.TemplateMovie,
		appearance.TemplateTvSeries,
		appearance.TemplateVideoGame,
		appearance.TemplateBook,
		appearance.TemplateComicBook,
	}

	return s.appearanceParser.IsPageOneOfTemplates(page, pageTemplates)
}

func (s *Scraper) collectAppearances(page *colly.HTMLElement) []appearance.DTO {
	var appearances []appearance.DTO

	// at first, we must collect appearance items
	heading := page.DOM.Find("#Appearances")
	if heading.Length() == 0 {
		return appearances
	}

	list := heading.Parent().Next()
	// could be some element between the heading and the list
	if !list.Is(".responsivediv") && !list.Is("ul") {
		list = list.Next()
	}

	listItems := list.Find("li")
	if listItems.Length() == 0 {
		return appearances
	}

	var dto appearance.DTO

	appearancePageCollector := s.newCollector(10)

	// then we need to register an event to parse target page (when collector receives html)
	appearancePageCollector.OnHTML("main.page__main", func(page *colly.HTMLElement) {
		dto = s.appearanceParser.Parse(page)
	})

	// now scrape each found appearance page
	listItems.Each(func(index int, appearance *goquery.Selection) {
		url, exists := appearance.Find("a:first-of-type").Attr("href")
		if !exists {
			return
		}

		// wait for "OnHTML" event fills the dto
		err := appearancePageCollector.Visit(page.Request.AbsoluteURL(url))
		if (err != nil) && !s.isErrorAlreadyVisited(err) {
			s.logger.Log("Unable to visit appearance page:", err)
			return
		}

		appearancePageCollector.Wait()

		if dto.Name == "" {
			return
		}

		// check notes - it must be empty or "First appearance" / "DLC" (for games) only
		// skip every other "Mentioned only", "Cover only", "In flashback(s)", "Appears in hologram" etc.
		textParts := strings.Split(strings.TrimSpace(appearance.Text()), " (")
		if len(textParts) > 1 {
			note := strings.ToLower(strings.TrimSpace(strings.Trim(textParts[1], ")")))
			if (note != "first appearance") && (note != "dlc") {
				return
			}

			dto.IsFirst = note == "first appearance"
		}

		// don't add same item twice, set "IsFirst" only
		for i, a := range appearances {
			if a.Name == dto.Name {
				if !a.IsFirst && dto.IsFirst {
					appearances[i].IsFirst = dto.IsFirst
				}

				return
			}
		}

		appearances = append(appearances, dto)
	})

	return appearances
}
