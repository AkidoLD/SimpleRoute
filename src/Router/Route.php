<?php

namespace Router;

use App\Exceptions\RouteException;

class Route{
    private NodeMap $nodeMap;

    public function __construct(NodeMap $nodeMap){
        $this->nodeMap = $nodeMap;
    }

    public function setNodeMap(NodeMap $nodeMap){
        $this->nodeMap = $nodeMap;
    }

    public function getNodeMap(): NodeMap{
        return $this->nodeMap;
    }

    public function makeRoute(RouteMap $routeMap){
        //Make the loop while the actual Noda have children
        while(($this->nodeMap->getActiveNode())->haveChildren()){
            if(!$route = $routeMap()){
                throw new RouteException('Invalid URI');
            }
            $node = ($this->nodeMap)($route);
        }
        $args = $routeMap->getUnusedSegments();
        $node(...$args);
    }

    public function __invoke(RouteMap $routeMap){
        $this->makeRoute($routeMap);
    }
}