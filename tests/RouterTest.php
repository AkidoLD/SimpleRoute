<?php

use SimpleRoute\Router\Node;
use SimpleRoute\Router\NodeTree;
use SimpleRoute\Router\Router;
use SimpleRoute\Router\UriSlicer;

require_once __DIR__."/../bootstrap.php";

//Create a RouteMap
$URI = "/login//";
$uriSlicer = new UriSlicer($URI);

//make fake nodeMap

$handler = function(...$args){
    prettyPrint("Nous somme sur la Node .", "p");
    echo "Les arguments en parametre : ";
    echoPre();
    var_dump($args);
    echoPre();
};

$initNode = new Node('init');

//Login section 
$loginNode = new Node('login', function(){
    prettyPrint("Ceci est la page de connexion", "h3");
    echo "Veuillez entrer vos informations de connexion : ";
    echo "<input>";
});

//Dashboard Section
$dashBoardNode = new Node('dashboard', $handler);
$usersNode = new Node('users', function($userId = null){
    prettyPrint("Ceci est la page destine aux utilisateurs", 'h3');
    if(isset($userId)){
        echo "Vous recherche l'utilisateur avec l'ID ".$userId;
    }else{
        echo "Vous souhaitez afficher la liste des utilisateurs";
    }
});

//Assembly Node
$initNode->addChildren([$loginNode, $dashBoardNode]);

$dashBoardNode->addChild($usersNode);

//Creating a NodeMap
$nodeTree = new NodeTree($initNode);

$router = new Router($nodeTree);

//Make the route
$router($uriSlicer);