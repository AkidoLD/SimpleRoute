<?php

use SimpleRoute\Router\Node;

require_once __DIR__."/../bootstrap.php";

$handle2 = fn()=> "Je suis la node 2";
$handle3 = fn()=> "Je suis la node 3";
//
$node = new Node('key_1');
$node2 = new Node('key_2', $handle2);
$node3 = new Node('key_3', $handle3);

//Try to add Children
$node->addChild($node2);
$node->addChild($node3);

//Try to count child

echo count($node);

//Try to get one child

foreach($node as $nod) echo $nod();