<?php

namespace Services\HtmlParser;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use JetBrains\PhpStorm\NoReturn;

class HtmlParserController extends Controller
{
    /**
     * @param Request $request
     * @return void
     */
    #[NoReturn] public function index(Request $request)
    {
        $parser = new HtmlParser($request->get('url'), config('way_type') ?? WayTypeEnums::CURL);
        dd($parser->parse()->render());
    }
}
