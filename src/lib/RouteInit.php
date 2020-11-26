<?php
namespace Lethe\Lib;

use \Yaf\Plugin_Abstract;
use \Yaf\Request_Abstract;
use \Yaf\Response_Abstract;

class RouteInit extends Plugin_Abstract
{
    public function routerShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        $uri = $request->getRequestUri();
        $uri = substr($uri, 1);
        $arr = explode('/', $uri);
        if (count($arr) == 2) {
            $request->controller = $arr[0];
        } elseif (count($arr) == 3) {
            $request->controller = $arr[1];
        }
    }
}
