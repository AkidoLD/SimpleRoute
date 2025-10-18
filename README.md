# SimpleRoute

> A PHP routing library based on a tree structure

## Why SimpleRoute?

Traditional PHP routers (like Laravel or Symfony) use flat arrays to define routes.

**Problem:** With nested routes, the code quickly becomes repetitive and harder to maintain:

```php
// Classic router
$routes = [
    '/api/users' => $usersHandler,
    '/api/users/profile' => $profileHandler,
    '/api/users/settings' => $settingsHandler,
];
```

**Solution:**
SimpleRoute uses a **tree structure** that naturally reflects the hierarchy of URLs.

You can build your route tree in **two ways** 👇

### 🧱 Classic method (explicit)

```php
$root->addChild($api);
$api->addChild($users);
$users->addChild($profile);
$users->addChild($settings);
```

### ⚡ Simplified method (modern)

```php
$root = new Node('root');
$api = new Node('api', parent: $root);
$users = new Node('users', parent: $api);
$profile = new Node('profile', parent: $users);
$settings = new Node('settings', parent: $users);
```

**Result:** Cleaner code, no duplication, and a clear visual hierarchy.

---

## ✨ Features

* 🌳 **Tree-based structure** – Routes are organized hierarchically (parent/child), just like real URLs
* ⚡ **O(h) performance** – Fast route lookup based on tree depth
* ✅ **119 unit tests** – Reliable code with high coverage
* 🧠 **Type-safe (PHP 8.1+)** – Full type hints to prevent runtime errors
* 🧩 **Typed exceptions** – Each error has its own class for easier debugging
* 🪶 **Lightweight** – Zero external dependencies

---

## 🚀 Installation

### Via Composer (recommended)

```bash
composer require akido-ld/simple-route
```

### Manual installation (for contributors)

```bash
git clone https://github.com/AkidoLD/SimpleRoute.git
cd SimpleRoute
composer install
```

---

## 🧩 Usage Example

```php
use SimpleRoute\Router\{Node, NodeTree, Router, UriSlicer};

// Create nodes
$root = new Node('root');
$api = new Node('api', parent: $root);
$users = new Node('users', function() {
    echo json_encode(['users' => ['Alice', 'Bob']]);
}, parent: $api);

// Router setup
$tree = new NodeTree($root);
$router = new Router($tree);

// Match an URL
$uri = new UriSlicer('/api/users');
$router->dispatch($uri);

// Output: {"users":["Alice","Bob"]}
```

---

## 📚 Documentation

* Check the [`/examples`](examples) directory for more usage examples
* See [`/tests`](tests) for detailed test cases and real-world usage patterns

---

## 🧪 Tests

```bash
./vendor/bin/phpunit
```

**Stats:** 119 tests – high coverage ✅

To generate a coverage report:

```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage
```

---

## 📜 License

[MIT License](LICENSE)

---

## 👤 Author

**Akido LD**
[GitHub: @AkidoLD](https://github.com/AkidoLD)
