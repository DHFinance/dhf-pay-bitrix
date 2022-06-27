<?php

namespace Citrus\DHFi\Util\Log;

use Bitrix\Main\Web\Json;
use Bitrix\Sale\PaySystem\Logger as SaleLogger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

class SalePaySystemHandler extends AbstractProcessingHandler
{
	protected static $levels = [
		Logger::DEBUG     => 'addDebugInfo',
		Logger::INFO      => 'addDebugInfo',
		Logger::NOTICE    => 'addDebugInfo',
		Logger::WARNING   => 'addDebugInfo',
		Logger::ERROR     => 'addError',
		Logger::CRITICAL  => 'addError',
		Logger::ALERT     => 'addError',
		Logger::EMERGENCY => 'addError',
	];

	protected function write(array $record): void
	{
		$method = $this->getLogMethod($record['level']);
		SaleLogger::$method(sprintf(
			'%s %s %s',
			Logger::getLevelName($record['level']),
			$record['formatted'],
			$this->jsonEncode($record['context'])
		));
	}

	/**
	 * @param int $level
	 * @return string
	 */
	private function getLogMethod(int $level): string
	{
		return static::$levels[$level] ?? LogLevel::ERROR;
	}

	private function jsonEncode(array $data)
	{
		return Json::encode($data, JSON_UNESCAPED_UNICODE);
	}
}