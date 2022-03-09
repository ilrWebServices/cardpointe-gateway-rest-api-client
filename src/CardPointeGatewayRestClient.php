<?php

namespace CardPointeGateway;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use CardPointeGateway\Psr7\DataAwareResponse;

/**
 * CardPointe Gateway Rest Client.
 */
class CardPointeGatewayRestClient extends GuzzleHttpClient {

  /**
   * CardPointeGatewayRestClient constructor.
   *
   * @param array $config
   *   Guzzle client configuration settings plus required options:
   *   - `cp_user` and `cp_pass` credentials
   *   - `cp_site` for the REST URL (e.g https://<cp_site>.cardconnect.com)
   */
  public function __construct(array $config = []) {
    if (empty($config['cp_user'])) {
      throw new \Exception('Missing cp_user config option.');
    }
    elseif (empty($config['cp_pass'])) {
      throw new \Exception('Missing cp_pass config option.');
    }
    elseif (empty($config['cp_site'])) {
      throw new \Exception('Missing cp_site config option.');
    }
    elseif (!empty($config['base_uri'])) {
      throw new \Exception('Please use cp_site setting to automatically configure the base_uri.');
    }

    // Set auth headers in config.
    $config['headers']['Authorization'] = 'Basic ' . base64_encode($config['cp_user'] . ':' . $config['cp_pass']);
    $config['base_uri'] = "https://{$config['cp_site']}.cardconnect.com/cardconnect/rest/";

    parent::__construct($config);

    // Add a data-aware middleware to automatically decode data format (e.g.
    // JSON) responses from the API.
    $handler = $this->getConfig('handler');
    $handler->push(Middleware::mapResponse(function (ResponseInterface $response) {
      return new DataAwareResponse(
        $response->getStatusCode(),
        $response->getHeaders(),
        $response->getBody(),
        $response->getProtocolVersion(),
        $response->getReasonPhrase()
      );
    }), 'data_decode_middleware');
  }

}
