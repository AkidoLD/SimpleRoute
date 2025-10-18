<?php

namespace AkidoLd\SimpleRoute\Router;

use Exception;
use AkidoLd\SimpleRoute\Exceptions\Router\InvalidRouteException;
use AkidoLd\SimpleRoute\Exceptions\Router\RouteNotFoundException;
use AkidoLd\SimpleRoute\Exceptions\Router\RouterException;

/**
 * Router engine for matching URIs against a NodeTree.
 *
 * The Router traverses a NodeTree using segments from a UriSlicer. 
 * When a node with a handler is reached, it executes it. If the route
 * is not found or an exception occurs, an optional failure handler
 * can be executed.
 *
 * Example usage:
 * ```php
 * $root = new Node('root', fn($id) => echo "User $id");
 * $root->addChild(new Node('user'));
 * $tree = new NodeTree($root);
 *
 * $router = new Router($tree, fn($e) => echo "Error: {$e->getMessage()}");
 * $uri = new UriSlicer('/user/42');
 * $router($uri); // Calls handler or failureHandler on error
 * ```
 */
class Router {
    /**
     * The NodeTree representing all routes.
     *
     * @var NodeTree
     */
    private NodeTree $nodeTree;

    /**
     * Optional handler to call when routing fails.
     * Receives the exception as parameter.
     *
     * @var callable|null
     */
    private $failureHandler;

    /**
     * Constructor.
     *
     * @param NodeTree $nodeTree The tree of routes.
     * @param callable|null $failureHandler Optional handler for errors.
     */
    public function __construct(NodeTree $nodeTree, ?callable $failureHandler = null) {
        $this->nodeTree = $nodeTree;
        $this->failureHandler = $failureHandler;
    }

    /**
     * Replace the current NodeTree.
     *
     * @param NodeTree $nodeTree
     */
    public function setNodeTree(NodeTree $nodeTree): void {
        $this->nodeTree = $nodeTree;
    }

    /**
     * Get the current NodeTree.
     *
     * @return NodeTree
     */
    public function getNodeTree(): NodeTree {
        return $this->nodeTree;
    }

    /**
     * Set a new failure handler.
     *
     * @param callable|null $failureHandler
     */
    public function setFailureHandler(?callable $failureHandler): void {
        $this->failureHandler = $failureHandler;
    }

    /**
     * Get the current failure handler.
     *
     * @return callable|null
     */
    public function getFailureHandler(): ?callable {
        return $this->failureHandler;
    }

    /**
     * Dispatch the URI using a NodeTree.
     *
     * This method traverses the NodeTree using the provided UriSlicer.
     * It executes the handler of the matching node when traversal completes.
     * 
     * Behavior:
     * - Throws RouteNotFoundException if a segment does not match any node.
     * - Calls the failure handler, if defined, when any exception occurs.
     *
     * Example:
     * ```php
     * $router->dispatch(new UriSlicer('/api/users'));
     * ```
     *
     * @param UriSlicer $uriSlicer The URI to match and traverse in the NodeTree
     * @return mixed The result of the node handler execution
     * @throws RouteNotFoundException If no matching node is found
     * @throws InvalidRouteException If no failure handler is defined to the node
     */
    public function dispatch(UriSlicer $uriSlicer) {
        $this->nodeTree->resetActiveNode();
        $node = $this->nodeTree->getActiveNode();
        
        try {
            while($uriSlicer->hasNext() && $node !== null){
                $segment = $uriSlicer->next();
                $node = $this->nodeTree->moveToChild($segment);
            }
    
            if($node === null){
                throw new RouteNotFoundException(
                    "Route not found at '{$uriSlicer->getURI()}'"
                );
            }
    
            if(!$node->hasHandler()){
                throw new InvalidRouteException(
                    "Route '{$uriSlicer->getURI()}' exists but has no handler defined"
                );
            }
            
            return $node->execute();
            
        } catch(RouterException $e){
            if($this->failureHandler){
                return ($this->failureHandler)($e);
            }
            throw $e;
        }
    }

    /**
     * Dispatch the URI using a NodeTree.
     *
     * @param UriSlicer $uriSlicer The URI to traverse
     * @return mixed The result of the node handler
     * @throws RouteNotFoundException If the route doesn't exist
     * @throws Exception If an exception occurs and no failure handler is set
     * @deprecated Use dispatch() instead
     */
    #[\ReturnTypeWillChange]
    public function makeRoute(UriSlicer $uriSlicer) {
        trigger_error(
            'makeRoute() is deprecated, use dispatch() instead',
            E_USER_DEPRECATED
        );
        return $this->dispatch($uriSlicer);
    }
    
    /**
     * Shortcut to call `dispatch()` directly using parentheses.
     *
     * @param UriSlicer $uriSlicer
     * @return mixed The return of `dispatch` methode
     */
    public function __invoke(UriSlicer $uriSlicer): mixed {
        return $this->dispatch($uriSlicer);
    }
}
