<?php

namespace Citrus\DHFi\Util;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Crm extends Controller
{
	public function configureActions()
	{
		return [
			'getInvoiceCurrency' => [
				"prefilters" => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_GET]
					),
				],
			],
			'setInvoiceCurrency' => [
				"prefilters" => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
				],
			],
		];
	}

	/**
	 * Получает валюту старых счетов CRM
	 *
	 * citrus.dhfi.util.Crm.getInvoiceCurrency
	 *
	 * @param CurrentUser $user
	 * @return AjaxJson
	 */
	public static function getInvoiceCurrencyAction(CurrentUser $user): AjaxJson
	{
		if (!$user->isAdmin()) {
			return AjaxJson::createDenied(new ErrorCollection([
				new Error(Loc::getMessage('ACCESS_DENIED')),
			]));
		}

		Loader::requireModule('crm');

		return AjaxJson::createSuccess([
			'value' => \CCrmCurrency::getInvoiceDefault(),
		]);
	}

	/**
	 * Устанавливает валюту для старых счетов CRM
	 *
	 * citrus.dhfi.util.Crm.setInvoiceCurrency
	 *
	 * @param string $id Код валюты
	 * @param CurrentUser $user
	 * @return AjaxJson В ключе prev возвращает прежнее значение этой настройки
	 */
	public static function setInvoiceCurrencyAction(string $id, CurrentUser $user): AjaxJson
	{
		if (!$user->isAdmin()) {
			return AjaxJson::createDenied(new ErrorCollection([
				new Error(Loc::getMessage('ACCESS_DENIED')),
			]));
		}

		Loader::requireModule('crm');

		if (!\CCrmCurrency::IsExists($id)) {
			return AjaxJson::createDenied(new ErrorCollection([
				new Error('Unkown currency specified'),
			]));
		}

		$prev = \CCrmCurrency::getInvoiceDefault();
		\CCrmCurrency::setInvoiceDefault($id);

		return AjaxJson::createSuccess(compact('prev'));
	}
}
