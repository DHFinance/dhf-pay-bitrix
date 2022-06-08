<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = [
	'NAME' => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_NAME'),
	'SORT' => 100,
	'CODES' => [
		"DHFI_STORE_ID" => [
			"NAME" => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_STORE_ID'),
			"GROUP" => "GENERAL_SETTINGS",
			"DESCRIPTION" => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_STORE_ID_DESC'),
			"SORT" => 90,
		],
		"DHFI_API_KEY" => [
			"NAME" => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_API_KEY'),
			"GROUP" => "GENERAL_SETTINGS",
			"DESCRIPTION" => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_API_KEY_DESC'),
			"SORT" => 100,
		],
        "ORDER_NUM" => [
            "NAME" => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_ORDER_NUM'),
            'GROUP' => 'PAYMENT',
            "DESCRIPTION" => "",
            "SORT" => 100,
            'DEFAULT' => [
                "PROVIDER_KEY" => "ORDER",
                "PROVIDER_VALUE" => "ACCOUNT_NUMBER",
            ],
        ],
        "ORDER_SUM" => [
            "NAME" => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_ORDER_SUM'),
            'GROUP' => 'PAYMENT',
            "DESCRIPTION" => "",
            "SORT" => 150,
            'DEFAULT' => [
                "PROVIDER_KEY" => "ORDER",
                "PROVIDER_VALUE" => "SHOULD_PAY",
            ],
        ],
    ]
];

