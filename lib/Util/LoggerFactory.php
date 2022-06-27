<?php

namespace Citrus\DHFi\Util;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Citrus\DHFi\Config;
use Citrus\DHFi\Util\Log\SalePaySystemHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggerFactory
{
	public static function getLogPath(string $channel): string
	{
		return Loader::getDocumentRoot() . sprintf(BX_ROOT . '/modules/dhfi-%s.log', $channel);
	}

	public static function create(string $channel): LoggerInterface
	{
		return Config::isLoggingEnabled()
			? (new Logger($channel))
				->pushHandler(
					new RotatingFileHandler(
						static::getLogPath($channel),
						(int)Option::get('main', 'event_log_cleanup_days', 7)
					)
				)
				->pushHandler(new SalePaySystemHandler())
			: new NullLogger();
	}
}