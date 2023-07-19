<?php

declare(strict_types=1);

namespace Services\HtmlParser\WayType;

class Curl extends RouterAbstract
{
    /**
     * @return bool|string
     */
    public function getContent(): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }
}
