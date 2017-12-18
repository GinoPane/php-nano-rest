# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

Types of changes

* **Features** for initial package features.
* **Added** for new features.
* **Changed** for changes in existing functionality.
* **Deprecated** for soon-to-be removed features.
* **Removed** for now removed features.
* **Fixed** for any bug fixes.
* **Security** in case of vulnerabilities.

## [Unreleased]

## 1.2.0 - 2017-12-18

### Changed
* Response context handling was removed from NanoRest;
* response context handling was added to RequestContext instead.

## 1.1.3 - 2017-12-14

### Changed
* ROOT_DIRECTORY is defined in NanoRest namespace to avoid conflicts.

## 1.1.2 - 2017-12-12

### Added
* ResponseContext::getHttpStatusMessage which returns HTTP status message.

## 1.1.1 - 2017-12-12

### Added
* Started using NanoHttpStatus to detect HTTP error status;
* composer scripts.

### Changed
* Updated README.

## 1.1.0 - 2017-12-05

### Added
* RequestContext::setEncodeArraysUsingDuplication to build proper query strings in some cases.
* RequestContext::setHttpQueryCustomProcessor to add custom post-processor for query string.

### Changed
* All usages of "uri" were replaced by "url" for consistency.

## 1.0.1 - 2017-12-04

### Added

* Applied Scrutinizer badge.

### Fixed

* Fixed some typos;
* refactored some code for Scrutinizer.

### Security

* Removed error suppression for json_decode in JsonResponseContext

## 1.0.0 - 2017-11-30

### Features

* The initial release of PHP Nano Rest;
* almost all types of synchronous CURL requests are available;
* SSL is being verified using ca-cert bundle which is also included;
* code is fully covered with tests;
* generation of docs failed - PhpDoc cannot build full class hierarchy;
* more to come - short syntax for most common requests, cookies, response type based on content-type header, etc.

[Unreleased]: https://github.com/GinoPane/php-nano-rest/compare/v1.2.0...HEAD
