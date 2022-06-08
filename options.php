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

$moduleOptions = new Options\OptionsManager(MODULE_ID);
$moduleOptions->addTab(
	(new Options\OptionsTab('edit1', Loc::getMessage('CITRUS_DHFI_OPTIONS_TAB1'),
		Loc::getMessage('CITRUS_DHFI_OPTIONS_TAB1_TITLE')))
		->add((new Options\CheckboxOption(OPTION_ENABLE_LOG))
			->label(Loc::getMessage('CITRUS_DHFI_OPTIONS_ENABLE_LOG') ?: '')
			->defaultValue(OPTION_ENABLE_LOG_DEFUALT)
		)
		->add(new Options\NoteOption(Loc::getMessage("CITRUS_DHFI_OPTIONS_ENABLE_LOG_NOTE", [
			'#PATH#' => LoggerFactory::getLogPath('*'),
		])))
);

$moduleOptions->show();
