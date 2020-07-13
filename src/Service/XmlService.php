<?php

namespace App\Service;

class XmlService
{
    /**
     * @param string $title
     * @param string $link
     * @param string $description
     * @param array $documentaries
     * @return string
     */
    public function generateXml(string $title, string $link, string $description, array $documentaries)
    {
        $http = 'http://';

        $xml = <<<xml
<?xml version='1.0' encoding='UTF-8'?>
<rss version='2.0'>
<channel>
<title>{$title}</title>
<link>{$http}{$link}</link>
<description>{$description}</description>
<language>en-us</language>
xml;
        foreach ($documentaries as $documentary) {
            $title = self::xmlEscape($documentary->getTitle());
            $slug = self::xmlEscape($documentary->getSlug());
            $description = self::xmlEscape($documentary->getSummary());
            $pubDate = $documentary->getCreatedAt()->format('Y-m-d\TH:i:sP');
            $xml .= <<<xml
<item>
<title>{$title}</title>
<link>{$http}documentarywire.com/{$slug}</link>
<description>{$description}</description>
<pubDate>$pubDate</pubDate>
</item>
xml;
        }
        $xml .= "</channel></rss>";

        return $xml;
    }

    private static function xmlEscape($string) {
        return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
    }
}