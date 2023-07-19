<?php

declare(strict_types=1);

namespace Services\HtmlParser\WayType;

abstract class RouterAbstract
{
    /**
     * @var string
     */
    public string $url = '';

    public static function init($wayType)
    {
        return new $wayType();
    }

    /**
     * @param $url
     * @return RouterAbstract
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    abstract public function getContent();
}
