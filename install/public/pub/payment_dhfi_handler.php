<?php

use Bitrix\Main;
use Citrus\DHFi\Integration\Invoice;
use Citrus\DHFi\Util\LoggerFactory;

define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC", "Y");
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

if (!Main\Loader::includeModule('citrus.dhfi')) {
	die();
}

$request = Main\Context::getCurrent()->getRequest();
LoggerFactory::create('invoice')
	->debug('Incoming request to dhfi handler', [
		'uri' => $request->getRequestUri(),
		'input' => $request->getInput(),
	]);

/** @var Main\HttpApplication $app */
$app = Main\HttpApplication::getInstance();
$app->runController(Invoice::class, 'confirmPayment');

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
