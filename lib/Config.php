<?php

namespace Citrus\DHFi;

use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Localization\Loc;
use DHF\Pay\DHFPay;

Loc::loadMessages(__FILE__);

class Config
{
	public static function getApiKey(): string
	{
		$value = trim(Option::get(MODULE_ID, OPTION_API_KEY));
		if (!$value) {
			throw new ConfigurationException(Loc::getMessage('CITRUS_DHFI_CONFIG_MISSING_API_KEY_OPTION'));
		}
		return $value;
	}

	/**
	 * @todo Хранить ID магазина как число, поменять type инпута в настройках
	 */
	public static function getStoreId(): string
	{
		$value = trim(Option::get(MODULE_ID, OPTION_STORE_ID));
		if (!$value) {
			throw new ConfigurationException(Loc::getMessage('CITRUS_DHFI_CONFIG_MISSING_STORE_ID_OPTION'));
		}
		return $value;
	}

	public static function getStore(): DTO\Store
	{
		return new DTO\Store([
			'id' => (int)static::getStoreId(),
			'apiKey' => static::getApiKey(),
		]);
	}

	public static function hasApiSettings(): bool
	{
		try {
			return Config::getApiEndpoint() && Config::getStoreId();
		} catch (ConfigurationException $e) {
			return false;
		}
	}

	public static function getApiEndpoint(): string
	{
		return Option::get(MODULE_ID, OPTION_API_ENDPOINT, OPTION_API_ENDPOINT_DEFAULT) ?: OPTION_API_ENDPOINT_DEFAULT;
	}

	public static function getPaymentUrlTempalte(): string
	{
		return Option::get(MODULE_ID, OPTION_PAYMENT_URL_TEMPLATE,
			OPTION_PAYMENT_URL_TEMPLATE_DEFAULT) ?: OPTION_PAYMENT_URL_TEMPLATE_DEFAULT;
	}

	public static function isLoggingEnabled(): bool
	{
		return 'Y' === (Option::get(MODULE_ID, OPTION_ENABLE_LOG, OPTION_ENABLE_LOG_DEFUALT) ?: OPTION_ENABLE_LOG_DEFUALT);
	}

	public static function getClient(): DHFPay
	{
		return ServiceLocator::getInstance()->get(SERVICE_DHFPay);
	}

	public static function hasSmartInvoiceSupport(): bool
	{
		return defined('\CCrmOwnerType::SmartInvoice');
	}
}