<?php

namespace SimpleRoute\Exceptions;

use Exception;

class RouteException extends Exception {}

class InvalidUriException extends RouteException{}

class RouteNotFoundException extends RouteException{}