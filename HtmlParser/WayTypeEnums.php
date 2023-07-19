<?php

declare(strict_types=1);

namespace Services\HtmlParser;

enum WayTypeEnums: string
{
    case CURL = 'Curl';

    case FILE_GET_CONTETNS = 'FileGetContents';
}
