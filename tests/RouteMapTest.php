<?php

require_once __DIR__."/../bootstrap.php";

use Router\RouteMap;

$uri = "/user/param/23/user/test";

$routeMap = new RouteMap($uri);

for($i = 0 ; $i < 3; $i++) {
    prettyPrint($routeMap(), "p");
}

echo $routeMap->getURI().BR;


var_dump($routeMap->getUnusedSegments());