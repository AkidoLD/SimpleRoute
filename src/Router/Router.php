<?php

namespace SimpleRoute\Router;

use App\Exceptions\RouteException;

class Router{
    private NodeTree $nodeTree;

    public function __construct(NodeTree $nodeTree){
        $this->nodeTree = $nodeTree;
    }

    public function setnodeTree(NodeTree $nodeTree){
        $this->nodeTree = $nodeTree;
    }

    public function getnodeTree(): NodeTree{
        return $this->nodeTree;
    }

    public function makeRoute(UriSlicer $uriSlicer){
        //Make the loop while the actual Noda have children
        while(($this->nodeTree->getActiveNode())->haveChildren()){
            if(!$route = $uriSlicer()){
                throw new RouteException('Invalid URI');
            }
            $node = ($this->nodeTree)($route);
        }
        $args = $uriSlicer->getUnusedSegments();
        $node(...$args);
    }

    public function __invoke(UriSlicer $uriSlicer){
        $this->makeRoute($uriSlicer);
    }
}