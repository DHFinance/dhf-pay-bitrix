<?php

namespace Citrus\DHFi;

use Citrus\DHFi\Util\DHFPayWithLogs;

return [
	'controllers' => [
		'value' => [
			'defaultNamespace' => '\Citrus\DHFi\Controller',
			'namespaces' => [
				'\Citrus\DHFi\Integration' => 'integration',
			],
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'citrus.dhfi.client' => [
				'className' => DHFPayWithLogs::class, // DHF\Pay\DHFPay::class
				'constructorParams' => static function () {
					return [Config::getApiEndpoint(), Config::getApiKey()];
				},
			],
		],
		'readonly' => true,
	],
];
