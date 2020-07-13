<?php

namespace App\Service;

class XmlService
{
    /**
     * @param array $documentaries
     * @return string
     */
    public function generateXml(array $documentaries)
    {
        $http = 'http://';

        $xml = <<<xml
<?xml version='1.0' encoding='UTF-8'?>
<rss version='2.0'>
<channel>
<title>DocumentaryWIRE</title>
<link>{$http}documentarywire.como</link>
<description>Watch Documentaries Online</description>
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