<?php

/** @var CUpdater $updater */

use Bitrix\Main\Loader;

if (Loader::includeModule($updater->moduleID)
	&& $updater->CanUpdateDatabase()) {
	\Citrus\DHFi\Util\CurrencyInstaller::installOrUpdate();
}
