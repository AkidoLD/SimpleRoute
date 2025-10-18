# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.1.1] - 2025-10-18
### Removed
- Removed `utils` because it is not used

## [1.1.0] - 2025-10-05
### Added
- `tracePathKeys()` static method in `NodeTree` class to trace path keys from a node
- `getPathKeys()` instance method in `NodeTree` class to get path keys excluding root
- `toString()` method in `UriSlicer` class
- `dispatch()` method as replacement for `makeRoute()`
- `NodeKeyIsEmptyException` exception to prevent creating a `Node` with an empty key
- `setKey()` private method to validate and prevent invalid keys
- Ability to set the parent of a `Node` at construction time
- Automatic parent cleanup when removing a `Node` from its parent
- `InvalidRouteException` in `Router` class. It is called when a route is found but no handler is attached

### Changed
- Namespace changed from `SimpleRoute\\` to `AkidoLd\\SimpleRoute\\` for better PSR-4 consistency  
  > ⚠️ All class imports must now use `AkidoLd\\SimpleRoute\\` instead of `SimpleRoute\\`.
- Updated `composer.json` autoload section to match the new namespace
- `Node::__toString()` now returns only the node's key
- `Node::removeChild()` now sets the removed child's parent to null
- `Node` keys are now immutable after construction
- Improved test coverage for `NodeTree` class (37 tests)
- Improved test coverage for `Node` class (55 tests)
- Improved code documentation
- `UriSlicer::reset()` now resets itself for cascade calling
- Reset the `NodeTree` before `Router::dispatch()` method is called
- `Router::dispatch()` method only catches `RouterException`
- Improved test coverage for `Router` class (19 tests)

### Deprecated
- `traceNodeParent()` method in `NodeTree`: Use `tracePathKeys()` instead (will be removed in 2.0.0)
- `nextNode()` method in `NodeTree`: Use `moveToChild()` instead (will be removed in 2.0.0)
- `makeRoute()` method: Use `dispatch()` instead (will be removed in 2.0.0)

### Removed
- Exception when trying to remove a non-existent Node child
- `ramsey/uuid` dependency and all UUID-related functionality
- Node UUID property and related methods


## [1.0.2] - 2025-10-03
### Removed
- `bootstrap.php` file

## [1.0.1] - 2025-10-03
### Added
- `.gitattributes` to filter unwanted elements when downloading the library

## [1.0.0] - 2025-10-03
### Added
- First stable release
- Static routing (no dynamic parameters yet)
- Unit tests for all available features

---

## Links

[Unreleased]: https://github.com/AkidoLD/SimpleRoute/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/AkidoLD/SimpleRoute/compare/v1.0.2...v1.1.0  
[1.0.2]: https://github.com/AkidoLD/SimpleRoute/compare/v1.0.1...v1.0.2  
[1.0.1]: https://github.com/AkidoLD/SimpleRoute/compare/v1.0.0...v1.0.1  
[1.0.0]: https://github.com/AkidoLD/SimpleRoute/releases/tag/v1.0.0