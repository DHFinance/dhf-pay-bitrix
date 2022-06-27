<?php

/** @var CUpdater $updater */

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\UrlRewriter;

if (Loader::includeModule($updater->moduleID)
	&& $updater->CanUpdateDatabase()) {
	$updater->Query("alter table citrus_dhfi_payments add DATE_UPDATE timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP not null after ID");
	$updater->Query("alter table citrus_dhfi_payments add DATE_CREATE timestamp default CURRENT_TIMESTAMP not null after DATE_UPDATE");
	$updater->Query("alter table citrus_dhfi_payments change ENTITY_ID ACCOUNT_NUMBER varchar(255) not null");
	$updater->Query("alter table citrus_dhfi_payments change ENTITY_TYPE REGISTRY varchar(255) not null");
	$updater->Query("alter table citrus_dhfi_payments add PAYSYSTEM_ID int null after ACCOUNT_NUMBER");

	$eventManager = EventManager::getInstance();
	$eventManager->unRegisterEventHandler('crm', 'onCrmInvoiceListItemBuildMenu', $updater->moduleID,
		\Citrus\DHFi\Integration\EventHandlers::class, 'onCrmInvoiceListItemBuildMenu');
	$eventManager->unRegisterEventHandler('crm', 'onCrmDynamicItemAdd', $updater->moduleID,
		\Citrus\DHFi\Integration\EventHandlers::class, 'onCrmDynamicItemAdd');
	$eventManager->unRegisterEventHandler('intranet', 'onBuildBindingMenu', $updater->moduleID,
		\Citrus\DHFi\Integration\EventHandlers::class, 'onBuildBindingMenu');
	$eventManager->registerEventHandler('intranet', 'onBuildBindingMenu', $updater->moduleID,
		\Citrus\DHFi\Integration\EventHandlers::class, 'onBuildBindingMenu');
}

if (Loader::includeModule($updater->moduleID) && $updater->CanUpdatePersonalFiles()) {
	DeleteDirFilesEx('/pub/payment_dhfi.php');
	DeleteDirFilesEx('/pub/payment_dhfi_handler.php');

	UrlRewriter::delete('s1', [
		'ID' => 'citrus.dhfi.payment',
	]);
}

if (Loader::includeModule($updater->moduleID) && $updater->CanUpdateKernel()) {
	DeleteDirFilesEx(\Citrus\DHFi\Util\ModuleLocation::getBxRoot() . "/js/citrus/dhfi/");
}
