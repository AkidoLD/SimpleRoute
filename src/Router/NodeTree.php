<?php

namespace SimpleRoute\Router;

class NodeTree {
    private ?Node $activeNode;
    private Node $nodeTree;

    public function __construct(Node $nodeTree){
        $this->nodeTree = $nodeTree;
        $this->activeNode = $nodeTree;
    }

    public function getActiveNode(): Node{
        return $this->activeNode;
    }

    public function nextNode(string $key): ?Node{
        return $this->activeNode = $this->activeNode[$key];
    }

    public function getNodeTree(): Node{
        return $this->nodeTree;
    }

    public function setNodeTree(Node $nodeTree){
        $this->nodeTree = $nodeTree;
    }

    public function __invoke(string $key): ?Node{
        return $this->nextNode($key);
    }
}