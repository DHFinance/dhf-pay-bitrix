<?php

namespace Citrus\DHFi\Util;

use const Citrus\DHFi\MODULE_ID;

class ModuleLocation
{
	/**
	 * @return string Top level folder where module is installed relative to DOCUMENT_ROOT: /bitrix or /local
	 */
	public static function getBxRoot()
	{
		return strpos(getLocalPath('modules/' . MODULE_ID), '/local') === 0
			? '/local'
			: BX_ROOT;
	}

	/**
	 * @return string Module path relative to DOCUMENT_ROOT
	 */
	public static function getModuleDir()
	{
		return self::getBxRoot() . '/modules/' . MODULE_ID;
	}
}