<?php

use SimpleRoute\Router\Node;
use SimpleRoute\Router\NodeTree;
use SimpleRoute\Router\Router;
use SimpleRoute\Router\UriSlicer;
use SimpleRoute\Exceptions\RouteNotFoundException;
use SimpleRoute\Exceptions\InvalidUriException;

require_once __DIR__."/../bootstrap.php";

function separator($title) {
    echo "<hr><h2>$title</h2>";
}

// Handlers de test
$handler = function(...$args){
    prettyPrint("Handler générique exécuté", "p");
    echo "Arguments reçus : ";
    echoPre();
    var_dump($args);
    echoPre();
};

$loginHandler = function(){
    prettyPrint("Page de connexion", "h3");
    echo "Veuillez entrer vos informations.";
};

$userHandler = function($userId = null){
    prettyPrint("Page utilisateur", "h3");
    if ($userId) {
        echo "Profil de l’utilisateur avec ID : $userId";
    } else {
        echo "Liste des utilisateurs";
    }
};

$articleHandler = function($id, $slug = null){
    prettyPrint("Page article", "h3");
    echo "Article ID : $id<br>";
    if ($slug) {
        echo "Slug : $slug";
    }
};

// Construction de l'arbre
$root = new Node('init');
$login = new Node('login', $loginHandler);
$dashboard = new Node('dashboard', $handler);
$users = new Node('users', $userHandler);
$articles = new Node('articles', $articleHandler);

$root->addChildren([$login, $dashboard]);
$dashboard->addChildren([$users, $articles]);

$nodeTree = new NodeTree($root);
$router = new Router($nodeTree);

// ----------------------------
// Tests
// ----------------------------

separator("Test 1 : Accès simple à /login/");
$router(new UriSlicer("/login/"));

separator("Test 2 : Accès à /dashboard/");
$router(new UriSlicer("/dashboard/"));

separator("Test 3 : Accès à /dashboard/users/");
$router(new UriSlicer("/dashboard/users/"));

separator("Test 4 : Accès à /dashboard/users/42/");
$router(new UriSlicer("/dashboard/users/42/"));

separator("Test 5 : Accès à /dashboard/articles/99/php-routing/");
$router(new UriSlicer("/dashboard/articles/99/php-routing/"));

separator("Test 6 : URI invalide (vide)");
try {
    $router(new UriSlicer("/"));
} catch (InvalidUriException $e) {
    prettyPrint("Exception capturée : " . $e->getMessage(), "h3");
}

separator("Test 7 : Route inconnue");
try {
    $router(new UriSlicer("/toto/"));
} catch (RouteNotFoundException $e) {
    prettyPrint("Exception capturée : " . $e->getMessage(), "h3");
}
