<?php

use SimpleRoute\Router\Node;
use SimpleRoute\Router\NodeTree;

require_once __DIR__."/../bootstrap.php";

//Creating test Node

$initNode = new Node('init');
$testNode1 = new Node('tnode1');
$testNode2 = new Node('tnode2');
$testNode3 = new Node('tnode3');
$testNode4 = new Node('tnode4');

//Connect Node
$initNode->addChildren([$testNode1, $testNode2]);

$testNode1->addChild($testNode3);

$testNode2->addChild($testNode4);

$nodeTree = new NodeTree($initNode);

echo $nodeTree->getActiveNode();

echoBR();

echo $nodeTree->nextNode('tnode1');

echoBR();

echo $nodeTree->getActiveNode();

echoBR();

echo $nodeTree->nextNode('tnode3');


