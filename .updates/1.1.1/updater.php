<?php

/** @var CUpdater $updater */

use Bitrix\Main\Loader;

if (Loader::includeModule($updater->moduleID) && $updater->CanUpdateKernel()) {
	$updater->CopyFiles('install/php_interface', 'php_interface');
}
