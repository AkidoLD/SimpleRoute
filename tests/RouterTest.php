<?php

use PHPUnit\Framework\TestCase;
use SimpleRoute\Router\Router;
use SimpleRoute\Router\Node;
use SimpleRoute\Router\NodeTree;
use SimpleRoute\Router\UriSlicer;
use SimpleRoute\Exceptions\Router\RouteNotFoundException;

class RouterTest extends TestCase {

    public function testNodeHandlerIsCalled() {
        $called = false;
        $root = new Node('root');
        $child = new Node('test', function() use (&$called) {
            $called = true;
        });
        $root->addChild($child);

        $tree = new NodeTree($root);
        $router = new Router($tree);

        $uri = new UriSlicer('/test');
        $router($uri);

        $this->assertTrue($called, "Node handler should have been called.");
    }

    public function testRouteNotFoundExceptionThrown() {
        $this->expectException(RouteNotFoundException::class);

        $root = new Node('root');
        $tree = new NodeTree($root);
        $router = new Router($tree);

        $uri = new UriSlicer('/nonexistent');
        $router($uri);
    }

    public function testFailureHandlerIsCalledOnException() {
        $called = false;
        $failureHandler = function($e) use (&$called) {
            $called = true;
            // Optionally check exception type
            $this->assertInstanceOf(RouteNotFoundException::class, $e);
        };

        $root = new Node('root');
        $tree = new NodeTree($root);
        $router = new Router($tree, $failureHandler);

        $uri = new UriSlicer('/nonexistent');
        $router($uri);

        $this->assertTrue($called, "Failure handler should have been called.");
    }
}
