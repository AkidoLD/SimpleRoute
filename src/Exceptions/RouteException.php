<?php

namespace SimpleRoute\Exceptions;

use Exception;

class RouteException extends Exception {}

class InvalidRouterUri extends RouteException{}

class RouteNotFoundException extends RouteException{}