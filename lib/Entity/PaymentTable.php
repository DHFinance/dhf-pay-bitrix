<?php

namespace Citrus\DHFi\Entity;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DecimalField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Validators\EnumValidator;

use CCrmOwnerType;

use Citrus\DHFi\Config;

Loc::loadMessages(__FILE__);

class PaymentTable extends DataManager
{
	public static function getTableName()
	{
		return 'citrus_dhfi_payments';
	}

	public static function getMap()
	{
		Loader::requireModule('crm');

		return [
			(new IntegerField('ID',
				[]
			))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_ID_FIELD'))
				->configureRequired()
				->configurePrimary(),

			(new IntegerField('ENTITY_ID',
				[]
			))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_ENTITY_ID_FIELD'))
				->configureRequired(),

			(new EnumField('ENTITY_TYPE',
				[
					'values' => array_filter([
						CCrmOwnerType::Invoice,
						Config::hasSmartInvoiceSupport() ? CCrmOwnerType::SmartInvoice : null
					]),
				]
			))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_ENTITY_TYPE_FIELD'))
				->configureRequired(),

			(new DecimalField('AMOUNT',
				[]
			))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_AMOUNT_FIELD'))
				->configurePrecision(2)
				->configureRequired(),

			(new EnumField('STATUS',
				[
					'validation' => [__CLASS__, 'validateStatus'],
					'values' => [
						'Not_paid',
						'Particularly_paid',
						'Paid',
					],
				]
			))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_STATUS_FIELD'))
				->configureDefaultValue('Not_paid'),
		];
	}

	public static function validateStatus(): array
	{
		return [
			new EnumValidator()
		];
	}
}