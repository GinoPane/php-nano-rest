PHP Nano Rest
=============

<sub>Powered by [composer-package-template](https://github.com/GinoPane/composer-package-template)</sub>

[![Latest Stable Version](https://poser.pugx.org/gino-pane/nano-rest/v/stable)](https://packagist.org/packages/gino-pane/nano-rest)
[![Build Status](https://travis-ci.org/GinoPane/php-nano-rest.svg?branch=master)](https://travis-ci.org/GinoPane/php-nano-rest)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/GinoPane/php-nano-rest.svg)](https://codeclimate.com/github/GinoPane/php-nano-rest/maintainability)
[![Test Coverage](https://img.shields.io/codeclimate/coverage/github/GinoPane/php-nano-rest.svg)](https://codeclimate.com/github/GinoPane/php-nano-rest/test_coverage)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/a31125f1-ff97-41c9-b0f1-9e6b5eb58470.svg)](https://insight.sensiolabs.com/projects/a31125f1-ff97-41c9-b0f1-9e6b5eb58470)
[![License](https://poser.pugx.org/gino-pane/nano-rest/license)](https://packagist.org/packages/gino-pane/nano-rest)
[![composer.lock](https://poser.pugx.org/gino-pane/nano-rest/composerlock)](https://packagist.org/packages/gino-pane/nano-rest)
[![Total Downloads](https://poser.pugx.org/gino-pane/nano-rest/downloads)](https://packagist.org/packages/gino-pane/nano-rest)

Easy-to-use self-containing lightweight package to deal with CURL requests.

Such packages as [Guzzle](https://github.com/guzzle/guzzle) are great of course, but they are too heavy for small projects. You don't need an overkill package for some easy stuff, and that's when something small and neat may help.

Requirements
============

* PHP >= 7.1;
* CURL extension.

Installation
============

Require the package using command line:

`composer require "gino-pane/nano-rest:1.*"`

or just put a new dependency in your existing `composer.json` and run `composer install` after that:

```
"require": {
    ...
    "gino-pane/nano-rest": "1.*"
},
```

Usage
=====

Project's philosophy implies usage of `RequestContext` and `ResponseContext` objects. `RequestContext` aggregates request settings
whilst `ResponseContext` contains response data.

Response context can be typed. Currently only `JsonResponseContext` available for JSON responses. Response type must be set explicitly by
user. If no response type set, `DummyResponseContext` will be used.

Please take a look at examples below, which may clarify everything.

```
require './vendor/autoload.php';

$nanoRest = new NanoRest();

//explicitly set expected response type
$nanoRest->setResponseContext(ResponseContext::getByType(ResponseContext::RESPONSE_TYPE_JSON));

//create request context
$requestContext = (new RequestContext('http://httpbin.org/post')) //pass URL to constructor
    ->setMethod(RequestContext::METHOD_POST) //set request method. GET is default
    ->setRequestParameters([ //set some request parameters. They will be attached to URL
        'foo' => 'bar'
    ])
    ->setData('Hello world!') //set request data for body
    ->setContentType(RequestContext::CONTENT_TYPE_TEXT_PLAIN) //being set by default
    ->setHeaders([ // set some headers for request
        'bar' => 'baz'
    ]);

$responseContext = $nanoRest->sendRequest($requestContext);

$responseContext->getHttpStatusCode(); //200
$responseContext->hasHttpError() //false

$responseContext->getArray();

/**
array(8) {
  'args' =>
  array(1) {
    'foo' =>
    string(3) "bar"
  }
  'data' =>
  string(12) "Hello world!"
  'files' =>
  array(0) {
  }
  'form' =>
  array(0) {
  }
  'headers' =>
  array(8) {
    'Accept' =>
    string(3) "*/*"
    'Accept-Encoding' =>
    string(13) "deflate, gzip"
    'Bar' =>
    string(3) "baz"
    'Connection' =>
    string(5) "close"
    'Content-Length' =>
    string(2) "12"
    'Content-Type' =>
    string(25) "text/plain; charset=UTF-8"
    'Host' =>
    string(11) "httpbin.org"
    'User-Agent' =>
    string(13) "php-nano-rest"
  }
  'json' =>
  NULL
  'origin' =>
  string(12) "93.85.47.181"
  'url' =>
  string(31) "http://httpbin.org/post?foo=bar"
}
*/

```

`RequestContext` provides `setCurlOption`/`setCurlOptions` which allow to override default CURL options
and customize request for all your needs. Please examine source code and provided `IntegrationTest` carefully
to get the whole idea.

Changelog
=========

To keep track, please refer to [CHANGELOG.md](https://github.com/GinoPane/php-nano-rest/blob/master/CHANGELOG.md).

Contributing
============

Please refer to [CONTRIBUTION.md](https://github.com/GinoPane/php-nano-rest/blob/master/CONTRIBUTION.md).

License
=======

Please refer to [LICENSE](https://github.com/GinoPane/php-nano-rest/blob/master/LICENSE).
