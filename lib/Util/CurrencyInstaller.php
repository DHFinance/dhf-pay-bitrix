<?php

namespace Citrus\DHFi\Util;

use Bitrix\Main\Loader;
use CCrmCurrency;

use RuntimeException;

use const Citrus\DHFi\CSPR_CURRENCY_CODE;

class CurrencyInstaller
{
	public static function installOrUpdate(): void
	{
		Loader::requireModule('crm');
		Loader::requireModule('currency');

		switch (CCrmCurrency::GetBaseCurrency()) {
			case 'RUB':
				$rate = 1.42;
				break;
			case 'USD':
				$rate = 0.0247;
				break;
			case 'EUR':
				$rate = 0.02469;
				break;
			default:
				$rate = 1;
		}

		$fields = [
			'CURRENCY' => CSPR_CURRENCY_CODE,
			'AMOUNT_CNT' => 1,
			'AMOUNT' => $rate,
			'LANG' => [
				'ru' => [
					'IS_EXIST' => \CCurrencyLang::GetByID(CSPR_CURRENCY_CODE, 'ru') ? 'Y' : 'N',
					'LID' => 'ru',
					'FULL_NAME' => 'Casper (CSPR)',
					'FORMAT_STRING' => '# CSPR',
					'DEC_POINT' => ',',
					'THOUSANDS_VARIANT' => 'S',
					'DECIMALS' => 2,
					'HIDE_ZERO' => 'Y',
				],
				'en' => [
					'IS_EXIST' => \CCurrencyLang::GetByID(CSPR_CURRENCY_CODE, 'en') ? 'Y' : 'N',
					'LID' => 'en',
					'FULL_NAME' => 'Casper (CSPR)',
					'FORMAT_STRING' => '# CSPR',
					'DEC_POINT' => ',',
					'THOUSANDS_VARIANT' => 'D',
					'DECIMALS' => 2,
					'HIDE_ZERO' => 'Y',
				],
			],
		];
		if (CCrmCurrency::GetByID(CSPR_CURRENCY_CODE)) {
			$success = CCrmCurrency::Update(CSPR_CURRENCY_CODE, $fields);
		} else {
			$success = CCrmCurrency::Add($fields);
		}
		if (!$success) {
			throw new RuntimeException(CCrmCurrency::GetLastError());
		}
	}
}