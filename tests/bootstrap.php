<?php

use Bitrix\Main\Diag\ExceptionHandlerFormatter;

$_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__, 4);

define("LANGUAGE_ID", "ru");
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("LOG_FILENAME", 'php://stderr');

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Replaces exception handler to output errors to STDERR

class PhpunitFileExceptionHandlerLog extends Bitrix\Main\Diag\FileExceptionHandlerLog
{
	public function write($exception, $logType)
	{
		$text = ExceptionHandlerFormatter::format($exception);
		$msg = date("Y-m-d H:i:s") . " - Host: " . $_SERVER["HTTP_HOST"] . " - " . static::logTypeToString($logType) . " - " . $text . "\n";
		fwrite(STDERR, $msg);
	}
}

$handler = new PhpunitFileExceptionHandlerLog;

$bitrixExceptionHandler = \Bitrix\Main\Application::getInstance()->getExceptionHandler();

$reflection = new \ReflectionClass($bitrixExceptionHandler);
$property = $reflection->getProperty('handlerLog');
$property->setAccessible(true);
$property->setValue($bitrixExceptionHandler, $handler);

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

\Bitrix\Main\Loader::requireModule('citrus.dhfi');

//#region Configure services
$serviceLocator = \Bitrix\Main\DI\ServiceLocator::getInstance();
$serviceLocator->addInstanceLazy('citrus.dhfi.client', [
	'className' => \DHF\Pay\DHFPay::class, // DHF\Pay\DHFPay::class
	'constructorParams' => static function () {
		return [$_ENV['TEST_SERVER_API'], $_ENV['TEST_SERVER_TOKEN']];
	},
]);
//#endregion