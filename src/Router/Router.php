<?php

namespace SimpleRoute\Router;

use SimpleRoute\Exceptions\InvalidUriException;
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
 * - {@see InvalidUriException} if the URI is incomplete.
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
     * @throws InvalidUriException If the URI ends before reaching a leaf.
     * @throws RouteNotFoundException If a segment does not match any child node.
     * @return void
     */
    //Parcourir l'arbre jusqu'a arrive a une feuille ou bien
    //si l'URI s'arrete en cours, verifier si la Node actuel
    //a un handler et si oui, la designer comme Node
    //Je pense ici on va devoir imposer une certaines norme
    //au niveau des URL parceque la methode avec les parametres
    //en plein milieu des URL n'est pas trop possible ici. Ici
    //Il faudrat passe explicitement les parametre par la methodes GET
    //ou POST
    public function makeRoute(UriSlicer $uriSlicer){
        // Traverse until a leaf is reached
        while(!($node = $this->nodeTree->getActiveNode())){
            $slice = $uriSlicer();
            if(!$slice){
                if($node->hadHandler()){
                    break;
                }
                throw new InvalidUriException("The URI $uriSlicer is invalid");
            }
            //Try to get the next node in the tree
            $nextNode = $this->nodeTree->nextNode($slice);
            
            //If no Node found, throw a RouteNotFoundException
            if(!$nextNode){
                throw new RouteNotFoundException("The URI segment $slice had been not found");
            }
        }
        //Execute the Node Handler
        $node->execute();
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
