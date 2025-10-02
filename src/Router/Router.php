<?php

namespace SimpleRoute\Router;

use Exception;
use SimpleRoute\Exceptions\Router\RouteNotFoundException;

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
     * Traverse the NodeTree using a UriSlicer.
     * 
     * Throws RouteNotFoundException if a segment does not match.
     * Executes the node handler when traversal completes.
     * Calls the failure handler if defined when any exception occurs.
     *
     * @param UriSlicer $uriSlicer
     */
    public function makeRoute(UriSlicer $uriSlicer){
        $node = null;
        try{
            while($uriSlicer->hasNext()){
                $node = $this->nodeTree->nextNode($uriSlicer->next());
                if($node === null){
                    throw new RouteNotFoundException('');
                }
            }
    
            if($node === null){
                throw new RouteNotFoundException('');
            }
    
            $node->execute();
    
        }catch(Exception $e){
            if($this->failureHandler){
                ($this->failureHandler)($e);
            }else{
                throw $e;
            }
        }
    }
    
    /**
     * Shortcut to call makeRoute() directly using parentheses.
     *
     * @param UriSlicer $uriSlicer
     */
    public function __invoke(UriSlicer $uriSlicer): void {
        $this->makeRoute($uriSlicer);
    }
}
