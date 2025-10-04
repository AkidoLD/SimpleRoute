<?php

require_once __DIR__."/../vendor/autoload.php";

use SimpleRoute\Router\Node;
use SimpleRoute\Router\NodeTree;
use SimpleRoute\Router\Router;
use SimpleRoute\Router\UriSlicer;

// ========================================
// FAKE DATA
// ========================================

$products = [
    ["id" => 1, "name" => "Laptop", "price" => 750.00, "quantity" => 10],
    ["id" => 2, "name" => "Smartphone", "price" => 500.00, "quantity" => 25],
    ["id" => 3, "name" => "Tablet", "price" => 300.00, "quantity" => 15],
    ["id" => 4, "name" => "Headphones", "price" => 80.00, "quantity" => 50],
    ["id" => 5, "name" => "Keyboard", "price" => 40.00, "quantity" => 40],
    ["id" => 6, "name" => "Mouse", "price" => 25.00, "quantity" => 60],
    ["id" => 7, "name" => "Monitor", "price" => 200.00, "quantity" => 20],
    ["id" => 8, "name" => "Printer", "price" => 150.00, "quantity" => 12],
    ["id" => 9, "name" => "Camera", "price" => 450.00, "quantity" => 8],
    ["id" => 10, "name" => "Speaker", "price" => 120.00, "quantity" => 18],
    ["id" => 11, "name" => "External HDD", "price" => 95.00, "quantity" => 30],
    ["id" => 12, "name" => "USB Stick", "price" => 15.00, "quantity" => 100],
    ["id" => 13, "name" => "Smartwatch", "price" => 220.00, "quantity" => 22],
    ["id" => 14, "name" => "Microphone", "price" => 110.00, "quantity" => 14],
    ["id" => 15, "name" => "Router", "price" => 70.00, "quantity" => 35],
];

$users = [
    ["id" => 1, "name" => "Alice", "age" => 25, "gender" => "Female", "email" => "alice@example.com"],
    ["id" => 2, "name" => "Bob", "age" => 30, "gender" => "Male", "email" => "bob@example.com"],
    ["id" => 3, "name" => "Charlie", "age" => 28, "gender" => "Male", "email" => "charlie@example.com"],
    ["id" => 4, "name" => "Diana", "age" => 22, "gender" => "Female", "email" => "diana@example.com"],
    ["id" => 5, "name" => "Ethan", "age" => 35, "gender" => "Male", "email" => "ethan@example.com"],
    ["id" => 6, "name" => "Fiona", "age" => 27, "gender" => "Female", "email" => "fiona@example.com"],
    ["id" => 7, "name" => "George", "age" => 40, "gender" => "Male", "email" => "george@example.com"],
    ["id" => 8, "name" => "Hannah", "age" => 29, "gender" => "Female", "email" => "hannah@example.com"],
    ["id" => 9, "name" => "Ian", "age" => 33, "gender" => "Male", "email" => "ian@example.com"],
    ["id" => 10, "name" => "Julia", "age" => 26, "gender" => "Female", "email" => "julia@example.com"],
];

// Session simulation
session_start();

// ========================================
// HANDLERS
// ========================================

// Handler pour la page d'accueil (root)
$handleRoot = function () {
    echo '<h1>🏠 Bienvenue sur SimpleRoute Demo</h1>';
    echo '<p>Cette démo illustre le système de routing avec Nodes.</p>';
    echo '<nav>';
    echo '<ul>';
    echo '<li><a href="/auth/login">🔑 Se connecter</a></li>';
    echo '<li><a href="/auth/register">📝 S\'inscrire</a></li>';
    echo '<li><a href="/dashboard">📊 Dashboard</a></li>';
    echo '</ul>';
    echo '</nav>';
};

// Handler pour le login
$handleLogin = function () {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Simulation d'authentification
        if ($email && $password) {
            $_SESSION['user'] = ['email' => $email, 'name' => 'John Doe'];
            header('Location: /dashboard');
            exit;
        }
    }
    
    echo '<h1>🔑 Connexion</h1>';
    echo '<form method="POST">';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label>Email: <input type="email" name="email" required style="display: block; margin-top: 5px; padding: 8px; width: 300px;"></label>';
    echo '</div>';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label>Mot de passe: <input type="password" name="password" required style="display: block; margin-top: 5px; padding: 8px; width: 300px;"></label>';
    echo '</div>';
    echo '<button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px;">Se connecter</button>';
    echo '</form>';
    echo '<p style="margin-top: 15px;"><a href="/auth/register">Pas encore de compte ? S\'inscrire</a></p>';
};

// Handler pour le register
$handleRegister = function () {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($name && $email && $password) {
            $_SESSION['user'] = ['email' => $email, 'name' => $name];
            echo '<div style="color: green; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">';
            echo '✅ Compte créé avec succès !';
            echo '</div>';
            echo '<p><a href="/dashboard" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">Aller au Dashboard</a></p>';
            return;
        }
    }
    
    echo '<h1>📝 Inscription</h1>';
    echo '<form method="POST">';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label>Nom: <input type="text" name="name" required style="display: block; margin-top: 5px; padding: 8px; width: 300px;"></label>';
    echo '</div>';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label>Email: <input type="email" name="email" required style="display: block; margin-top: 5px; padding: 8px; width: 300px;"></label>';
    echo '</div>';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label>Mot de passe: <input type="password" name="password" required style="display: block; margin-top: 5px; padding: 8px; width: 300px;"></label>';
    echo '</div>';
    echo '<button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px;">S\'inscrire</button>';
    echo '</form>';
    echo '<p style="margin-top: 15px;"><a href="/auth/login">Déjà un compte ? Se connecter</a></p>';
};

// Handler pour le dashboard
$handleDashboard = function () {
    if (!isset($_SESSION['user'])) {
        header('Location: /auth/login');
        exit;
    }
    
    $userName = $_SESSION['user']['name'] ?? 'Utilisateur';
    
    echo '<h1>📊 Dashboard - Bienvenue ' . htmlspecialchars($userName) . '</h1>';
    echo '<nav style="margin: 20px 0;">';
    echo '<ul style="list-style: none; padding: 0;">';
    echo '<li style="margin-bottom: 10px;"><a href="/dashboard/users_list" style="padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">👥 Liste des utilisateurs</a></li>';
    echo '<li style="margin-bottom: 10px;"><a href="/dashboard/product_list" style="padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">📦 Liste des produits</a></li>';
    echo '<li style="margin-bottom: 10px;"><a href="/?logout=1" style="padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">🚪 Se déconnecter</a></li>';
    echo '</ul>';
    echo '</nav>';
    
    echo '<div style="margin-top: 20px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
    echo '<h3 style="margin-top: 0;">📈 Statistiques rapides</h3>';
    echo '<div style="display: flex; gap: 20px;">';
    echo '<div style="flex: 1; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 4px;">';
    echo '<p style="margin: 0; font-size: 14px;">Total utilisateurs</p>';
    echo '<p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold;">10</p>';
    echo '</div>';
    echo '<div style="flex: 1; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 4px;">';
    echo '<p style="margin: 0; font-size: 14px;">Total produits</p>';
    echo '<p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold;">15</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
};

// Handler pour la liste des utilisateurs
$handleUserList = function () use ($users) {
    if (!isset($_SESSION['user'])) {
        header('Location: /auth/login');
        exit;
    }
    
    echo '<h1>👥 Liste des Utilisateurs</h1>';
    echo '<p><a href="/dashboard" style="color: #007bff; text-decoration: none;">← Retour au Dashboard</a></p>';
    
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
    echo '<thead>';
    echo '<tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">';
    echo '<th style="padding: 15px;">ID</th><th style="padding: 15px;">Nom</th><th style="padding: 15px;">Âge</th><th style="padding: 15px;">Genre</th><th style="padding: 15px;">Email</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($users as $index => $user) {
        $bgColor = $index % 2 === 0 ? '#f8f9fa' : 'white';
        echo '<tr style="background: ' . $bgColor . ';">';
        echo '<td style="padding: 12px;">' . $user['id'] . '</td>';
        echo '<td style="padding: 12px; font-weight: bold;">' . htmlspecialchars($user['name']) . '</td>';
        echo '<td style="padding: 12px;">' . $user['age'] . '</td>';
        echo '<td style="padding: 12px;">' . $user['gender'] . '</td>';
        echo '<td style="padding: 12px; color: #007bff;">' . htmlspecialchars($user['email']) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '<div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 4px;">';
    echo '<strong>📊 Total: ' . count($users) . ' utilisateurs</strong>';
    echo '</div>';
};

// Handler pour la liste des produits
$handleProductList = function () use ($products) {
    if (!isset($_SESSION['user'])) {
        header('Location: /auth/login');
        exit;
    }
    
    echo '<h1>📦 Liste des Produits</h1>';
    echo '<p><a href="/dashboard" style="color: #007bff; text-decoration: none;">← Retour au Dashboard</a></p>';
    
    $totalValue = array_reduce($products, function($sum, $product) {
        return $sum + ($product['price'] * $product['quantity']);
    }, 0);
    
    echo '<div style="margin: 20px 0; padding: 20px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
    echo '<h3 style="margin: 0 0 10px 0;">💰 Valeur totale du stock</h3>';
    echo '<p style="margin: 0; font-size: 36px; font-weight: bold;">$' . number_format($totalValue, 2) . '</p>';
    echo '</div>';
    
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
    echo '<thead>';
    echo '<tr style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">';
    echo '<th style="padding: 15px;">ID</th><th style="padding: 15px;">Produit</th><th style="padding: 15px;">Prix</th><th style="padding: 15px;">Quantité</th><th style="padding: 15px;">Total</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($products as $index => $product) {
        $rowTotal = $product['price'] * $product['quantity'];
        $bgColor = $index % 2 === 0 ? '#f8f9fa' : 'white';
        $stockColor = $product['quantity'] < 15 ? 'color: #dc3545; font-weight: bold;' : 'color: #28a745;';
        
        echo '<tr style="background: ' . $bgColor . ';">';
        echo '<td style="padding: 12px;">' . $product['id'] . '</td>';
        echo '<td style="padding: 12px; font-weight: bold;">' . htmlspecialchars($product['name']) . '</td>';
        echo '<td style="padding: 12px; color: #28a745; font-weight: bold;">$' . number_format($product['price'], 2) . '</td>';
        echo '<td style="padding: 12px; ' . $stockColor . '">' . $product['quantity'] . ' unités</td>';
        echo '<td style="padding: 12px; font-weight: bold; font-size: 16px;">$' . number_format($rowTotal, 2) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '<div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #28a745; border-radius: 4px;">';
    echo '<strong>📊 Total: ' . count($products) . ' produits en stock</strong>';
    echo '</div>';
};

// Handler d'erreur 404
$errorHandler = function (Exception $e) {
    echo '<div style="text-align: center; padding: 50px;">';
    echo '<h1 style="font-size: 72px; margin: 0; color: #dc3545;">404</h1>';
    echo '<h2>❌ Page non trouvée</h2>';
    echo '<p style="color: #6c757d; font-size: 18px;">La route <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">' . htmlspecialchars($e->getMessage()) . '</code> n\'existe pas.</p>';
    echo '<p style="margin-top: 30px;"><a href="/" style="padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">🏠 Retour à l\'accueil</a></p>';
    echo '</div>';
};

// ========================================
// CRÉATION DES NODES ET ASSIGNATION DES HANDLERS
// ========================================

// Create the root node
$root = new Node('root', $handleRoot);

// Authentification routes
$auth = new Node('auth');
$login = new Node('login', $handleLogin);
$register = new Node('register', $handleRegister);

// Dashboard routes
$dashboard = new Node('dashboard', $handleDashboard);
$userList = new Node('users_list', $handleUserList);
$productList = new Node('product_list', $handleProductList);

// Connect Nodes
$root->addChildren([$auth, $dashboard]);
$auth->addChildren([$login, $register]);
$dashboard->addChildren([$userList, $productList]);

// Create the NodeTree
$nodeTree = new NodeTree($root);

// ========================================
// ROUTING LOGIC
// ========================================

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /');
    exit;
}

// Get current URI
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$slicer = new UriSlicer($currentUri);

// CSS global
echo '<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: #f5f7fa;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    a {
        transition: all 0.3s ease;
    }
    a:hover {
        opacity: 0.8;
    }
    table tr:hover {
        background: #e9ecef !important;
    }
</style>';

echo '<div class="container">';

// Display some debugging info
echo '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; margin-bottom: 20px; border-radius: 8px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
echo '<h2 style="margin: 0 0 10px 0;">🔍 SimpleRoute Debug Info</h2>';
echo '<p style="margin: 5px 0;"><strong>Current URI:</strong> <code style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 4px;">' . htmlspecialchars($currentUri) . '</code></p>';
echo '<p style="margin: 5px 0; font-size: 14px; opacity: 0.9;">Système de routing basé sur NodeTree</p>';
echo '</div>';

// Content area
echo '<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); min-height: 400px;">';

// Le Router gère automatiquement le matching et l'exécution du handler
$router = new Router($nodeTree, $errorHandler);
$router->makeRoute($slicer);

echo '</div>';
echo '</div>';