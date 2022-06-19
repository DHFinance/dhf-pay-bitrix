<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = [
	'NAME' => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_NAME'),
	'SORT' => 100,
	'CODES' => [
		"DHFI_API_KEY" => [
			"NAME" => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_API_KEY'),
			"GROUP" => "GENERAL_SETTINGS",
			"DESCRIPTION" => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_API_KEY_DESC'),
			"SORT" => 100,
		],
		'DHFI_PAYMENT_GATEWAY' => [
			'NAME' => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_PAYMENT_GATEWAY'),
			'SORT' => 200,
			'GROUP' => 'GENERAL_SETTINGS',
			'DEFAULT' => [
				'PROVIDER_VALUE' => 'pay.dhfi.online',
				'PROVIDER_KEY' => 'VALUE'
			]
		],

		'PAYMENT_DESCRIPTION' => [
			'NAME' => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_PAYMENT_DESCRIPTION'),
			'DESCRIPTION' => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_PAYMENT_DESCRIPTION_DESC'),
			'SORT' => 300,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => [
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_PAYMENT_DESCRIPTION_TEMPLATE'),
			],
		],

	]
];

