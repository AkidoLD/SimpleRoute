# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]
### Added
- `toString()` method in `UriSlicer` class
- The key of the Node we want the trace parent with `traceNodeParent()` static method of NodeTree
- Add `dispatch()` method for replace `makeRoute()`.

### Changed
- Only return the key of a Node when `__toString()` method is called
- Deprecate `makeRoute()` method

### Removed
- Exception when trying to remove a non-existent Node child

## [1.0.2] - 2025-10-03
### Removed
- Removed `bootstrap.php` file

## [1.0.1] - 2025-10-03
### Added
- Added `.gitattributes` to filter unwanted elements when downloading the library

## [1.0.0] - 2025-10-03
### Added
- First stable release
- Static routing (no dynamic parameters yet)
- Unit tests for all available features

---

## Links

[Unreleased]: https://github.com/AkidoLD/SimpleRoute/compare/v1.0.2...HEAD  
[1.0.2]: https://github.com/AkidoLD/SimpleRoute/compare/v1.0.1...v1.0.2  
[1.0.1]: https://github.com/AkidoLD/SimpleRoute/compare/v1.0.0...v1.0.1  
[1.0.0]: https://github.com/AkidoLD/SimpleRoute/releases/tag/v1.0.0
