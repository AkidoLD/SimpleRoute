# SimpleRoute

> PHP routing library based on a tree structure

## Why SimpleRoute?

Traditional PHP routers (Laravel, Symfony) use flat arrays to define routes.

**Problem:** With nested routes, the code becomes repetitive and harder to read:

```php
// Classic router
$routes = [
    '/api/users' => $usersHandler,
    '/api/users/profile' => $profileHandler,
    '/api/users/settings' => $settingsHandler,
];
```

**Solution:** SimpleRoute uses a tree structure that naturally reflects the hierarchy of URLs:

```php
// SimpleRoute
$root->addChild($api);
$api->addChild($users);
$users->addChild($profile);
$users->addChild($settings);
```

**Result:** Cleaner code, no duplication, and an obvious visual structure.

---

## Features

* **Tree-based structure** – Routes are organized as parent/child, just like real URLs
* **O(h) performance** – Fast lookup based on tree depth
* **47 unit tests** – Reliable code (~95% coverage)
* **Type-safe PHP 8.1+** – Full type hints to prevent bugs
* **Lightweight** – Only dependency: `ramsey/uuid`
* **Typed exceptions** – Each error has its own class for easier debugging

---

## Installation

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

## Usage

```php
use SimpleRoute\Router\{Node, NodeTree, Router, UriSlicer};

// Create nodes
$root = new Node('root');
$api = new Node('api');
$users = new Node('users', function() {
    echo json_encode(['users' => ['Alice', 'Bob']]);
});

// Build the tree
$root->addChild($api);
$api->addChild($users);

// Router
$tree = new NodeTree($root);
$router = new Router($tree);

// Match an URL
$uri = new UriSlicer('/api/users');
$router->makeRoute($uri);
// Output: {"users":["Alice","Bob"]}
```

---

## Documentation

* See `/examples` for more examples
* See `/tests` for complete usage cases

---

## Tests

```bash
./vendor/bin/phpunit
```

**Stats:** 119 tests, ~95% coverage

---

## License

MIT

---

## Author

**Akido LD** – [@AkidoLD](https://github.com/AkidoLD)
