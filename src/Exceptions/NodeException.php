<?php

namespace SimpleRoute\Exceptions;

class NodeException extends \Exception {}

class NodeChildIsNotANode extends NodeException {}

class NodeHandlerNotSet extends NodeException {}

class NodeChildKeyMismatchException extends NodeException {}
