# CardPointe Gateway REST API Client for PHP

A simple PHP http client for the CardPointe Gateway REST API. Optimized for JSON and authentication via username and password. Based on [Guzzle][].

`CardPointeGatewayRestClient` is an extended `GuzzleHttp\Client`, so it can do all the things that Guzzle can and a bit more.

Be sure to refer to the [CardPointe Gateway API documentation][] for details.

## Installation

Via composer:

```
composer require ilrwebservices/cardpointe-gateway-rest-api-client
```

## Usage

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use CardPointeGateway\CardPointeGatewayRestClient;

$client = new CardPointeGatewayRestClient([
  'cp_user' => $_ENV['CARDPOINTE_GATEWAY_API_USER'],
  'cp_pass' => $_ENV['CARDPOINTE_GATEWAY_API_PASS'],
  'cp_site' => 'fts',
]);
```

Note how these settings are passed into the client constructor options along with other Guzzle-compatible options. In this example, some sensitive settings are stored in environment variables since they should not be stored in code.

The `CardPointeGatewayRestClient` will set the `base_uri` option to `https://<cp_site>.cardconnect.com/cardconnect/rest/` automatically.

You can now make API calls just as you would in Guzzle:

```php
$client_response = $client->request('GET', 'https://<cp_site>.cardconnect.com/cardconnect/rest/inquireMerchant/<merchid>');
```

Since the `base_uri` option is pre-configured, you can use a relative URL (be sure not to add a leading `/`, though):

```php
$client_response = $client->request('GET', 'inquireMerchant/<merchid>');
```

And because it's Guzzle, there are shorthand methods:

```php
$client_response = $client->get('inquireMerchant/<merchid>');
```

Responses are [PSR-7] response messages, just like Guzzle, so you can get returned data via `getBody()`:

```php
$client_data_raw = (string) $client_response->getBody();

// Returns:
// {
//   "site": "fts",
//   "acctupdater": "N",
//   "cvv": "N",
//   "cardproc": "RPCT",
//   "fee_type": "N",
//   "enabled": true,
//   "echeck": "N",
//   "merchid": "xxxxxxxxxxxx",
//   "avs": "N"
// }
```

For `application/json` responses, `CardPointeGatewayRestClient` adds a non-standard `getData()` method that decodes the JSON response for you:

```php
$client_data = $client_response->getData();

// Returns:
// Array
// (
//     [site] => fts
//     [acctupdater] => N
//     [cvv] => N
//     [cardproc] => RPCT
//     [fee_type] => N
//     [enabled] => 1
//     [echeck] => N
//     [merchid] => xxxxxxxxxxxx
//     [avs] => N
// )
```

Be careful with large responses, however, as they can consume too much memory.

Sending JSON data to API endpoints can be done using the `json` request option from Guzzle:

```php
$new_client_response = $client->post('auth', [
  'json' => [
    'merchid' => 'xxxxxxxxxxxx',
    'amount' => '20.01',
    'expiry' => 'MMYY',
    'account' => '4111111111111111',
    'cvv2' => '123',
  ],
]);
```

### Error handling

You can use [Guzzle exceptions][] for error handling. CardPointe Gateway API endpoints generally use 4xx codes for errors, so `GuzzleHttp\Exception\ClientException` is a good match for catching those errors.

```php
try {
  $new_client_response = $client->post('auth', [
    'json' => [
      'merchid': 'xxxxxxxxxxxx',
      'amount': '20.01',
      'expiry': 'MMYY',
      'account': '4111111111111111',
      'cvv2': '123',
    ],
  ]);
}
// Could not connect to server or other network issue.
catch (\GuzzleHttp\Exception\ConnectException $e) {
  print_r($e->getMessage());
}
// 4xx error. This is either a 400 Bad Request (e.g. invalid syntax) or
// 401 Unauthorized (e.g. bad credentials).
catch (\GuzzleHttp\Exception\ClientException $e) {
  print_r($e->getResponse()->getData());
  print_r($e->getMessage());
}
// 5xx error. This is an 'Internal Server Error'.
catch (\GuzzleHttp\Exception\ServerException $e) {
  print_r($e->getResponse()->getData());
  print_r($e->getMessage());
}
```

Inspired by [drewm/mailchimp-api][].


[Guzzle]: https://github.com/guzzle/guzzle
[CardPointe Gateway API documentation]: https://developer.cardpointe.com/cardconnect-api/
[PSR-7]: https://www.php-fig.org/psr/psr-7/
[Guzzle exceptions]: https://docs.guzzlephp.org/en/stable/quickstart.html#exceptions
[drewm/mailchimp-api]: https://github.com/drewm/mailchimp-api
