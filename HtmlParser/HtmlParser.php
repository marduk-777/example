<?php

declare(strict_types=1);

namespace Services\HtmlParser;

use Services\HtmlParser\WayType\RouterAbstract;

final class HtmlParser
{
    private const REGEXP = '/<(\w+)(?:([\'"]).*?\2|.)*?>/';
    private string $content;
    public array $tags = [];

    /**
     * @param string $url
     * @param WayTypeEnums $wayType
     */
    public function __construct(private readonly string $url, private readonly WayTypeEnums $wayType)
    {
        $this->content = RouterAbstract::init($this->wayType)->setUrl($this->url)->getContent();
    }

    /**
     * @return $this
     */
    public function parse(): HtmlParser
    {
        preg_match_all(self::REGEXP, $this->content, $this->tags);
        return $this;
    }

    /**
     * @return array
     */
    public function render(): array
    {
        return array_count_values($this->tags[1]);
    }
}
