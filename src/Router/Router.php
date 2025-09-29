<?php

namespace SimpleRoute\Router;

use SimpleRoute\Exceptions\InvalidRouterUri;
use SimpleRoute\Exceptions\RouteNotFoundException;

/**
 * Core routing engine for matching URIs against a tree of `Node` objects.
 *
 * The `Router` consumes a `UriSlicer` (which breaks down a URI into segments)
 * and traverses the `NodeTree` step by step until a leaf node is reached.
 * 
 * Responsibilities:
 * - Start traversal from a root `NodeTree`.
 * - Consume URI segments and descend into matching child nodes.
 * - Throw explicit exceptions when the URI does not match the route tree.
 * - Once a leaf node is reached, invoke its handler with any unused URI segments as arguments.
 *
 * Typical usage:
 * ```php
 * $root = new Node('root', function($id) {
 *     echo "User ID: $id";
 * });
 * $root->addChild(new Node('user'));
 *
 * $tree = new NodeTree($root);
 * $router = new Router($tree);
 *
 * $uri = new UriSlicer('/user/42');
 * $router($uri); // Calls handler with "42"
 * ```
 *
 * Exceptions:
 * - {@see InvalidRouterUri} if the URI is incomplete.
 * - {@see RouteNotFoundException} if no matching child node exists.
 *
 * @package SimpleRoute\Router
 */
class Router {
    /**
     * The tree of routes to be traversed.
     *
     * @var NodeTree
     */
    private NodeTree $nodeTree;

    /**
     * Initialize the router with a `NodeTree`.
     *
     * @param NodeTree $nodeTree The tree of nodes representing routes.
     */
    public function __construct(NodeTree $nodeTree){
        $this->nodeTree = $nodeTree;
    }

    /**
     * Replace the current `NodeTree`.
     *
     * @param NodeTree $nodeTree
     * @return void
     */
    public function setnodeTree(NodeTree $nodeTree){
        $this->nodeTree = $nodeTree;
    }

    /**
     * Get the current `NodeTree`.
     *
     * @return NodeTree
     */
    public function getnodeTree(): NodeTree{
        return $this->nodeTree;
    }

    /**
     * Match the given URI against the route tree.
     *
     * Traverses the `NodeTree` using the segments from the `UriSlicer`.
     * - While the active node is not a leaf, consume a segment.
     * - If the segment is missing → throws {@see InvalidRouterUri}.
     * - If the segment does not match any child node → throws {@see RouteNotFoundException}.
     * - When a leaf is reached, invoke its handler with any unused segments as parameters.
     *
     * @param UriSlicer $uriSlicer The URI to match and slice into segments.
     * @throws InvalidRouterUri If the URI ends before reaching a leaf.
     * @throws RouteNotFoundException If a segment does not match any child node.
     * @return void
     */
    public function makeRoute(UriSlicer $uriSlicer){
        // Traverse until a leaf is reached
        while(!($this->nodeTree->getActiveNode())->isLeaf()){
            if(!$route = $uriSlicer()){
                throw new InvalidRouterUri('This URI is Invalid');
            }
            // Ensure the segment matches an existing child node
            if(!$node = ($this->nodeTree)($route)){
                throw new RouteNotFoundException("No route matches segment '$route'");
            }
        }
        // Pass any remaining segments as arguments to the leaf handler
        $args = $uriSlicer->getUnusedSegments();
        $node(...$args);
    }

    /**
     * Shortcut to call {@see makeRoute()} directly with `()` syntax.
     *
     * @param UriSlicer $uriSlicer
     * @return void
     */
    public function __invoke(UriSlicer $uriSlicer){
        $this->makeRoute($uriSlicer);
    }
}
