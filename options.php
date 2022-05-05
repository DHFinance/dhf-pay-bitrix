<?php

namespace Citrus\DHFi;

use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Citrus\Core\ModuleOptions as Options;
use Citrus\DHFi\Util\LoggerFactory;
use GuzzleHttp\Exception\ServerException;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'citrus.dhfi');

Loc::loadMessages(__FILE__);

/**
 * @var \CUser $USER
 * @var \CMain $APPLICATION
 */
if (!$USER->IsAdmin()) {
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

Loader::requireModule(ADMIN_MODULE_NAME);

$note = new Options\NoteOption(Loc::getMessage("CITRUS_DHFI_OPTIONS_KEY_NOTE", [
	'#CALLBACK_URL#' => \CHTTP::URN2URI('/pub/dhfi-handler/'),
]));
if (Config::hasApiSettings()) {
	try {
		$store = Store::get(Config::getStoreId());

		$note = new Options\NoteOption(Loc::getMessage("CITRUS_DHFI_OPTIONS_KEY_NOTE_CONNECTION_CHECK_SUCCESS", [
			'#STORE_NAME#' => $store->name,
		]));
	} catch (ConfigurationException $e) {
		$note = new Options\NoteOption(Loc::getMessage("CITRUS_DHFI_OPTIONS_KEY_NOTE_CONNECTION_CHECK_FAILED", [
			'#CALLBACK_URL#' => \CHTTP::URN2URI('/bitrix/tools/sale_ps_result.php'),
		]));
	} catch (ServerException $e) {
		echo (new \CAdminMessage([
			"MESSAGE" => Loc::getMessage('CITRUS_DHFI_OPTIONS_KEY_NOTE_CONNECTION_CHECK_FAILED'),
			"TYPE" => "ERROR",
			"DETAILS" => Loc::getMessage('CITRUS_DHFI_API_ERROR', ["#MESSAGE#" => $e->getMessage(), '#CODE#' => $e->getCode()]),
		]))->Show();
	}
}

$moduleOptions = new Options\OptionsManager(MODULE_ID);
$moduleOptions->addTab(
	(new Options\OptionsTab('edit1', Loc::getMessage('CITRUS_DHFI_OPTIONS_TAB1'),
		Loc::getMessage('CITRUS_DHFI_OPTIONS_TAB1_TITLE')))
		->add(($note))
		->add((new Options\TextOption(OPTION_API_KEY))
			->label(Loc::getMessage('CITRUS_DHFI_OPTIONS_API_KEY') ?: '')
			->size(40)
		)
		->add((new Options\TextOption(OPTION_STORE_ID))
			->label(Loc::getMessage('CITRUS_DHFI_OPTIONS_STORE') ?: '')
			->size(10)
		)
		->add((new Options\CheckboxOption(OPTION_ENABLE_LOG))
			->label(Loc::getMessage('CITRUS_DHFI_OPTIONS_ENABLE_LOG') ?: '')
			->defaultValue(OPTION_ENABLE_LOG_DEFUALT)
		)
		->add(new Options\NoteOption(Loc::getMessage("CITRUS_DHFI_OPTIONS_ENABLE_LOG_NOTE", [
			'#PATH#' => LoggerFactory::getLogPath('*'),
		])))
);

$moduleOptions->show();
