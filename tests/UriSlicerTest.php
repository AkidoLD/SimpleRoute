<?php

use SimpleRoute\Router\UriSlicer;

require_once __DIR__."/../bootstrap.php";

$uri = "/user/param/23/user/test";

$uriSlicer = new UriSlicer($uri);

for($i = 0 ; $i < 3; $i++) {
    prettyPrint($uriSlicer(), "p");
}

echo $uriSlicer->getURI().BR;


var_dump($uriSlicer->getUnusedSegments());