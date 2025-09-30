<?php

namespace SimpleRoute\Exceptions\Router;

use Exception;

class RouteException extends Exception {}

class InvalidUriException extends RouteException{}

class RouteNotFoundException extends RouteException{}