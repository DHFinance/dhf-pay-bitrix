<?php

namespace Citrus\DHFi\Util;

use DHF\Pay\DHFPay;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;

class DHFPayWithLogs extends DHFPay
{

	public function __construct(string $endpoint, string $token)
	{
		parent::__construct($endpoint, $token);

		$this->client = $this->configureLogging([
			'base_uri' => $this->endpoint,
		]);
	}

	protected function configureLogging(array $config): Client
	{
		$messageFormats = [
			'REQUEST: {method} - {uri} - HTTP/{version} -- {req_body} -- {req_headers}',
			'RESPONSE: {code} -- {res_body} -- {res_headers}',
		];

		$stack = HandlerStack::create();

		foreach ($messageFormats as $messageFormat) {
			// We'll use unshift instead of push, to add the middleware to the bottom of the stack, not the top
			$stack->unshift(
				Middleware::log(
					LoggerFactory::create('api'),
					new MessageFormatter($messageFormat)
				)
			);
		}

		return new Client(array_merge($config, ['handler' => $stack]));
	}

}