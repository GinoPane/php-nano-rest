PHP Nano Rest
=============

[![Latest Stable Version](https://poser.pugx.org/gino-pane/nano-rest/v/stable)](https://packagist.org/packages/gino-pane/nano-rest)
[![Build Status](https://travis-ci.org/GinoPane/php-nano-rest.svg?branch=master)](https://travis-ci.org/GinoPane/php-nano-rest)
[![Maintainability](https://api.codeclimate.com/v1/badges/f87cf0eef8aad99c488c/maintainability)](https://codeclimate.com/github/GinoPane/php-nano-rest/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/f87cf0eef8aad99c488c/test_coverage)](https://codeclimate.com/github/GinoPane/php-nano-rest/test_coverage)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/GinoPane/php-nano-rest/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/GinoPane/php-nano-rest/?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/a31125f1-ff97-41c9-b0f1-9e6b5eb58470.svg)](https://insight.sensiolabs.com/projects/a31125f1-ff97-41c9-b0f1-9e6b5eb58470)
[![License](https://poser.pugx.org/gino-pane/nano-rest/license)](https://packagist.org/packages/gino-pane/nano-rest)
[![Total Downloads](https://poser.pugx.org/gino-pane/nano-rest/downloads)](https://packagist.org/packages/gino-pane/nano-rest)

Easy-to-use self-containing lightweight package to deal with cURL requests.

Such packages as [Guzzle](https://github.com/guzzle/guzzle) are great of course, but they are too heavy for small projects. You don't need an overkill package for some easy stuff, and that's when something small and neat may help.

Requirements
============

* PHP >= 7.1;
* cURL extension.

Installation
============

Require the package using command line:

    composer require "gino-pane/nano-rest:1.*"

or just put a new dependency in your existing `composer.json` and run `composer install` after that:


    "require": {
        ...
        "gino-pane/nano-rest": "1.*"
    }


Usage
=====

Project's philosophy implies usage of `RequestContext` and `ResponseContext` objects. `RequestContext` aggregates request settings
whilst `ResponseContext` contains response data.

Response context can be typed. Currently only `JsonResponseContext` available for JSON responses. Response type must be set explicitly by
user. If no response type set, `DummyResponseContext` will be used.

Please take a look at examples below, which may clarify everything.

#### POST some data to endpoint


    require './vendor/autoload.php';
    
    $nanoRest = new NanoRest();
    
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
        ])
        ->setResponseContextClass(JsonResponseContext::class); //explicitly set expected response type
    
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


`RequestContext` provides `setCurlOption`/`setCurlOptions` which allow to override default cURL options
and customize request for all your needs. Please examine source code and provided `IntegrationTest` carefully
to get the whole idea.

#### Change the way how request query is generated

By default `http_build_query` encodes arrays using PHP square brackets syntax, like this:

    ?text[0]=1&text[1]=2&text[2]=3

But sometimes you'll want it to work like this instead:

    ?text=1&text=2&text=3

Or even in some other custom-defined way.

That's why `setEncodeArraysUsingDuplication` and `setHttpQueryCustomProcessor` methods were added to `RequestContext`:


    $url = "http://some.url";
    $data = ['text' => [1,2,3]];
    
    $request = (new RequestContext($url))
                ->setRequestParameters($data)
                ->setEncodeArraysUsingDuplication(false);
    
    $requestUrl = $request->getRequestUrl(); //http://some.url?text%5B0%5D=1&text%5B1%5D=2&text%5B2%5D=3
    
    $request = (new RequestContext($url))
                ->setRequestParameters($data)
                ->setEncodeArraysUsingDuplication(true);
    
    $requestUrl = $request->getRequestUrl(); //http://some.url?text=1&text=2&text=3
    

Method `setHttpQueryCustomProcessor` allows you to set your custom `Closure` that will be called on HTTP query string so you could process it as you wish. Initial request `$data` array will be passed to it as a second parameter.

    $url = "http://some.url";
    $data = ['text' => [1,2,3]];
    
    $request = (new RequestContext($url))
                ->setRequestParameters($data)
                ->setEncodeArraysUsingDuplication(true);
                
    $request->setHttpQueryCustomProcessor(
        function (string $query, array $data) {
            return str_replace('text', 'data', $query);
        }
    );
    
    $requestUrl = $request->getRequestUrl(); //http://some.url?data=1&data=2&data=3


Useful Tools
============

Running Tests:
--------

    php vendor/bin/phpunit
 
 or 
 
    composer test

Code Sniffer Tool:
------------------

    php vendor/bin/phpcs --standard=PSR2 src/
 
 or
 
    composer psr2check

Code Auto-fixer:
----------------

    php vendor/bin/phpcbf --standard=PSR2 src/ 
    
 or
 
    composer psr2autofix
 
 
Building Docs:
--------

    php vendor/bin/phpdoc -d "src" -t "docs"
 
 or 
 
    composer docs
    
Updating Cacert.pem:
--------
    
        php bin/update-cacert.php
     
     or
     
        composer update-cacert


Changelog
=========

To keep track, please refer to [CHANGELOG.md](https://github.com/GinoPane/php-nano-rest/blob/master/CHANGELOG.md).

Contributing
============

Please refer to [CONTRIBUTING.md](https://github.com/GinoPane/php-nano-rest/blob/master/CONTRIBUTING.md).

License
=======

Please refer to [LICENSE](https://github.com/GinoPane/php-nano-rest/blob/master/LICENSE).

Notes
=====

Powered by [composer-package-template](https://github.com/GinoPane/composer-package-template)
