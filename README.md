Slim Api Wrapper
================

[![Build Status](https://travis-ci.org/anothy/slim-api-wrapper.svg?branch=master)](https://travis-ci.org/anothy/slim-api-wrapper)

Used as a way of accessing Slim App APIs internally.  There are two types of
accessing the APIs, directly where it skips the traversal of middleware(s),
or using the full route where it traverses the middleware(s) attached to the
Slim app.

### Installation

Install with [Composer](https://getcomposer.org/).

```bash
$ composer require anothy/slim-api-wrapper
```

### Usage

Setup container:
```php
$container['slim-api-wrapper'] = function (\Slim\Container $c) {
    return new \Anothy\SlimApiWrapper($c);
};
```

#### Calling a named-route using the `call` method.

The `call` method will skip the traversal of the middleware (in and out) and go
straight to the Slim application.

```php
$app = new \Slim\App();

$app->get('/books', function ($request, $response, $args) {
    // Show all books
})->setName('api-books');

$slimApiWrapper = $app->getContainer()->get('slim-api-wrapper');

$result = $slimApiWrapper->call('GET', 'api-books', [
   'queryParams' => [
       'page' => 1,
   ],
]);
```

The above example would look for the named-route `api-books` with `GET` method
and with QueryString of `page=1`.

#### Calling a named-route using the `callMiddlewareStack` method.

The `callMiddlewareStack` method will traverse middleware(s) (in and out) added
to the Slim application.

```php
$result = $slimApiWrapper->callMiddlewareStack('GET', 'api-books', [
    'queryParams' => [
        'page' => 1,
    ],
]);
```

#### Adding route pattern placeholders

If your route has any pattern placeholders, you can add the `namedArgs` to the 
call. 

```php
$app->get('/books/{id:[0-9]+}', function ($request, $response, $args) {
    // Show all books by `id`
})->setName('api-books-by-id');

$result = $slimApiWrapper->call('GET', 'api-books-by-id', [
    'namedArgs' => [
        'id' => 1234,
    ],
]);
``` 

The above call is equivalent to doing a `GET /books/1234`.

#### Adding additional headers

Here is an example with the named-route `api-books` that require an Authorization
header. A `headers` option is added with the `HTTP_AUTHORIZATION` key and value.
The value is for an OAuth token.

```php
$result = $slimApiWrapper->call('GET', 'api-books', [
    'headers' => [
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
    ],
    'queryParams' => [
        'page' => 1,
    ],
]);
```

#### Adding a payload (body) to a request

```php
$result = $slimApiWrapper->call('POST', 'api-books-post', [
    'headers' => [
        'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
    ],
    'payload' => [
        'name'   => 'The Name of the Wind',
        'author' => 'Patrick Rothfuss',
    ],
]);
```

This adds `payload` to the request body as a JSON string.
