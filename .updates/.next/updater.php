<?php

/** @var CUpdater $updater */

use Bitrix\Main\Loader;

if (Loader::includeModule($updater->moduleID)
	&& $updater->CanUpdateDatabase()) {
	$updater->Query("alter table citrus_dhfi_payments add DATE_UPDATE timestamp default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP not null after ID");
	$updater->Query("alter table citrus_dhfi_payments add DATE_CREATE timestamp default CURRENT_TIMESTAMP not null after DATE_UPDATE");
	$updater->Query("alter table citrus_dhfi_payments change ENTITY_ID ACCOUNT_NUMBER varchar(255) not null");
	$updater->Query("alter table citrus_dhfi_payments change ENTITY_TYPE REGISTRY varchar(255) not null");
	$updater->Query("alter table citrus_dhfi_payments add PAYSYSTEM_ID int null after ACCOUNT_NUMBER");
}