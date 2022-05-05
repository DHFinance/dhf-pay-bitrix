<?php

namespace Citrus\DHFi\Util;

use const Citrus\DHFi\MODULE_ID;

class ModuleLocation
{
	/**
	 * @return string Путь установки модуля от корня сайта: папка /bitrix или /local
	 */
	public static function getBxRoot()
	{
		return strpos(getLocalPath('modules/' . MODULE_ID), '/local') === 0
			? '/local'
			: BX_ROOT;
	}

	/**
	 * @return string Путь к папке с модулем относительно корня сайта
	 */
	public static function getModuleDir()
	{
		return self::getBxRoot() . '/modules/' . MODULE_ID;
	}
}