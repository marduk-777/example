<?php

declare(strict_types=1);

namespace Services\HtmlParser\WayType;

class FileGetContnts extends RouterAbstract
{
    /**
     * @return bool|string
     */
    public function getContent(): bool|string
    {
        return file_get_contents($this->url);
    }
}
