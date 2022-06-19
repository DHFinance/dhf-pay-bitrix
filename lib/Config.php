<?php

namespace Citrus\DHFi;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Config
{
	public static function isLoggingEnabled(): bool
	{
		return 'Y' === (Option::get(MODULE_ID, OPTION_ENABLE_LOG, OPTION_ENABLE_LOG_DEFUALT) ?: OPTION_ENABLE_LOG_DEFUALT);
	}
}