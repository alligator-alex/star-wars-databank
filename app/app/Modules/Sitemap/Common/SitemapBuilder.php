<?php

declare(strict_types=1);

namespace App\Modules\Sitemap\Common;

use App\Modules\Sitemap\Common\Providers\SitemapProvider;
use RuntimeException;
use XMLWriter;

class SitemapBuilder
{
    private const string DATE_FORMAT = 'Y-m-d\TH:i:sP';
    private const string XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    /** @var SitemapProvider[] */
    private array $providers;
    private string $filesPath;
    private string $domain;

    /**
     * @param SitemapProvider ...$providers
     */
    public function __construct(...$providers)
    {
        $this->filesPath = public_path('sitemaps/');
        $this->domain = (string) config('app.url');
        $this->providers = $providers;
    }

    /**
     * @throws RuntimeException
     */
    public function generate(): void
    {
        $indexFile = public_path('sitemap.xml');

        $xml = new XMLWriter();

        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('sitemapindex');
        $xml->writeAttribute('xmlns', self::XMLNS);

        foreach ($this->providers as $provider) {
            if ($this->buildUrlsetFile($provider)) {
                $xml->startElement('sitemap');
                $xml->writeElement('loc', $this->domain . '/sitemaps/' . $provider->getCode() . '.xml');
                $xml->endElement();
            }
        }

        $xml->endElement();
        $xml->endDocument();

        if (!file_put_contents($indexFile, $xml->outputMemory())) {
            throw new RuntimeException('Unable to save index sitemap');
        }
    }

    /**
     * @throws RuntimeException
     */
    private function buildUrlsetFile(SitemapProvider $provider): ?string
    {
        $items = $provider->getItems();
        if (empty($provider->getItems())) {
            return null;
        }

        $file = $this->filesPath . $provider->getCode() . '.xml';

        $xml = new XMLWriter();

        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', self::XMLNS);

        foreach ($items as $item) {
            $xml->startElement('url');
            $xml->writeElement('loc', $item->getLoc());

            if ($item->getLastmod()) {
                $xml->writeElement(
                    'lastmod',
                    $item->getLastmod()->format(self::DATE_FORMAT)
                );
            }

            if ($item->getChangefreq()) {
                $xml->writeElement('changefreq', $item->getChangefreq()->value);
            }

            if ($item->getPriority()) {
                $xml->writeElement('priority', number_format($item->getPriority(), 1));
            }

            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        if (!file_put_contents($file, $xml->outputMemory())) {
            throw new RuntimeException('Unable to save urlset sitemap for "' . $provider->getCode() . '"');
        }

        return (filesize($file)) ? $file : null;
    }
}
