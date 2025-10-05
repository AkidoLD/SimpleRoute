<?php

namespace SimpleRoute\Tests\Router;

use PHPUnit\Framework\TestCase;
use SimpleRoute\Router\Router;
use SimpleRoute\Router\NodeTree;
use SimpleRoute\Router\Node;
use SimpleRoute\Router\UriSlicer;
use SimpleRoute\Exceptions\Router\RouteNotFoundException;
use SimpleRoute\Exceptions\Node\NodeHandlerNotSetException;
use SimpleRoute\Exceptions\Router\InvalidRouteException;

/**
 * Tests complets pour la classe Router
 */
class RouterTest extends TestCase
{
    private NodeTree $nodeTree;
    private Node $rootNode;

    protected function setUp(): void
    {
        // Créer un arbre de nodes pour les tests
        $this->rootNode = new Node('root');
        $this->nodeTree = new NodeTree($this->rootNode);
    }

    public function testCanBeInstantiatedWithNodeTree()
    {
        $router = new Router($this->nodeTree);
        
        $this->assertInstanceOf(Router::class, $router);
        $this->assertSame($this->nodeTree, $router->getNodeTree());
        $this->assertNull($router->getFailureHandler());
    }

    public function testCanBeInstantiatedWithFailureHandler()
    {
        $handler = function($e) { return "error handled"; };
        $router = new Router($this->nodeTree, $handler);
        
        $this->assertSame($handler, $router->getFailureHandler());
    }

    public function testCanSetAndGetNodeTree()
    {
        $router = new Router($this->nodeTree);
        $newRoot = new Node('new-root');
        $newTree = new NodeTree($newRoot);
        
        $router->setNodeTree($newTree);
        
        $this->assertSame($newTree, $router->getNodeTree());
        $this->assertNotSame($this->nodeTree, $router->getNodeTree());
    }

    public function testCanSetAndGetFailureHandler()
    {
        $router = new Router($this->nodeTree);
        $handler = function($e) { return "handled: " . $e->getMessage(); };
        
        $router->setFailureHandler($handler);
        
        $this->assertSame($handler, $router->getFailureHandler());
    }

    public function testCanSetFailureHandlerToNull()
    {
        $handler = function($e) { return "error"; };
        $router = new Router($this->nodeTree, $handler);
        
        $router->setFailureHandler(null);
        
        $this->assertNull($router->getFailureHandler());
    }

    public function testExecutesHandlerWhenSingleSegmentRouteIsFound()
    {
        $executed = false;
        $userNode = new Node('user', function() use (&$executed) { $executed = true; });
        $this->rootNode->addChild($userNode);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = new UriSlicer('/user');
        
        $router->dispatch($uriSlicer);
        
        $this->assertTrue($executed, 'Handler should have been executed');
    }

    public function testCanNavigateNestedRoutes()
    {
        $result = null;
        $userNode = new Node('user');
        $profileNode = new Node('profile', function() use (&$result) { $result = 'profile-page'; });
        
        $this->rootNode->addChild($userNode);
        $userNode->addChild($profileNode);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = new UriSlicer('/user/profile');
        
        $router->dispatch($uriSlicer);
        
        $this->assertEquals('profile-page', $result);
    }

    public function testCallsFailureHandlerWhenRouteNotFound()
    {
        $caughtException = null;
        $failureHandler = function($e) use (&$caughtException) {
            $caughtException = $e;
        };
        
        $router = new Router($this->nodeTree, $failureHandler);
        $uriSlicer = new UriSlicer('/nonexistent');
        
        $router->dispatch($uriSlicer);
        
        $this->assertInstanceOf(RouteNotFoundException::class, $caughtException);
    }

    public function testThrowsExceptionWhenNoFailureHandlerSet()
    {
        $this->expectException(RouteNotFoundException::class);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = new UriSlicer('/nonexistent');
        
        $router->dispatch($uriSlicer);
    }

    public function testIsCallableViaInvoke()
    {
        $executed = false;
        $homeNode = new Node('home', function() use (&$executed) { $executed = true; });
        $this->rootNode->addChild($homeNode);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = new UriSlicer('/home');
        
        // Utiliser le Router comme une fonction
        $router($uriSlicer);
        
        $this->assertTrue($executed);
    }

    public function testThrowsWhenNodeHasNoHandler()
    {
        $userNode = new Node('user'); // Pas de handler
        $this->rootNode->addChild($userNode);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = new UriSlicer('/user');
        
        $this->expectException(InvalidRouteException::class);
        $router->dispatch($uriSlicer);
    }

    public function testHandlesDeepNestedRoutes()
    {
        $result = null;
        
        $api = new Node('api');
        $v1 = new Node('v1');
        $users = new Node('users');
        $posts = new Node('posts', function() use (&$result) { $result = "api-v1-users-posts"; });
        
        $this->rootNode->addChild($api);
        $api->addChild($v1);
        $v1->addChild($users);
        $users->addChild($posts);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = new UriSlicer('/api/v1/users/posts');
        
        $router->dispatch($uriSlicer);
        
        $this->assertEquals('api-v1-users-posts', $result);
    }

    public function testFailsWhenIntermediateNodeIsMissing()
    {
        $caughtException = null;
        
        $api = new Node('api');
        $users = new Node('users', function() { return "users"; });
        
        $this->rootNode->addChild($api);
        $api->addChild($users);
        
        $failureHandler = function($e) use (&$caughtException) {
            $caughtException = $e;
        };
        
        $router = new Router($this->nodeTree, $failureHandler);
        $uriSlicer = new UriSlicer('/api/v1/users'); // 'v1' n’existe pas
        
        $router->dispatch($uriSlicer);
        
        $this->assertInstanceOf(RouteNotFoundException::class, $caughtException);
    }

    public function testFailureHandlerReceivesCorrectExceptionType()
    {
        $exceptionClass = null;
        $exceptionMessage = null;
        
        $failureHandler = function($e) use (&$exceptionClass, &$exceptionMessage) {
            $exceptionClass = get_class($e);
            $exceptionMessage = $e->getMessage();
        };
        
        $router = new Router($this->nodeTree, $failureHandler);
        $uriSlicer = new UriSlicer('/invalid/route');
        
        $router->dispatch($uriSlicer);
        
        $this->assertEquals(RouteNotFoundException::class, $exceptionClass);
    }

    public function testCanHandleRoutesWithSpecialCharacters()
    {
        $result = null;
        $node = new Node('user-profile', function() use (&$result) { $result = 'special'; });
        $this->rootNode->addChild($node);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = new UriSlicer('/user-profile');
        
        $router->dispatch($uriSlicer);
        
        $this->assertEquals('special', $result);
    }

    public function testMaintainsNodeTreeStateAfterRouting()
    {
        $homeNode = new Node('home', function() { return "home"; });
        $aboutNode = new Node('about', function() { return "about"; });
        
        $this->rootNode->addChild($homeNode);
        $this->rootNode->addChild($aboutNode);
        
        $router = new Router($this->nodeTree);
        
        $uriSlicer1 = new UriSlicer('/home');
        $router->dispatch($uriSlicer1);
        
        $this->assertEquals('home', $this->nodeTree->getActiveNode()->getKey());
        
        $this->nodeTree = new NodeTree($this->rootNode); // Reset
        $router->setNodeTree($this->nodeTree);
        
        $uriSlicer2 = new UriSlicer('/about');
        $router->dispatch($uriSlicer2);
        
        $this->assertEquals('about', $this->nodeTree->getActiveNode()->getKey());
    }

    public function testHandlesHandlerReturnValues()
    {
        $node = new Node('test', function() { return 'return-value'; });
        $this->rootNode->addChild($node);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = new UriSlicer('/test');
        
        ob_start();
        $router->dispatch($uriSlicer);
        $output = ob_get_clean();
        
        $this->assertEquals('', $output);
    }

    public function testWorksWithUriSlicerFactoryMethod()
    {
        $result = null;
        $node = new Node('api', function() use (&$result) { $result = 'api-called'; });
        $this->rootNode->addChild($node);
        
        $router = new Router($this->nodeTree);
        $uriSlicer = UriSlicer::fromSegments(['api']);
        
        $router->dispatch($uriSlicer);
        
        $this->assertEquals('api-called', $result);
    }

    public function testNodeTreeIsResetAfterDispatch(){
        $users = new Node('users', parent : $this->rootNode, handler: fn() => "users");
        $list = new Node('list', parent : $users, handler: fn() => "list");
        $listPath = $this->nodeTree->getPathKeys($list);
        $slicer = UriSlicer::fromSegments($listPath);

        //
        $route = new Router($this->nodeTree);
        $result1 = $route->dispatch($slicer->reset());
        $result2 = $route->dispatch($slicer->reset());
        $this->assertEquals($result1, $result2, "The result 1 : $result1 is not equal to the result 2 : $result2");
    }
}
