<?php

namespace Citrus\DHFi\Entity;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Fields\Validators;
use Bitrix\Main\Type\DateTime;

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
			(new Fields\IntegerField('ID'))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_ID_FIELD'))
				->configureRequired()
				->configurePrimary(),

			(new Fields\DatetimeField('DATE_CREATE'))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_DATE_CREATE_FIELD'))
				->configureDefaultValue(function () {
					return new DateTime();
				}),

			(new Fields\DatetimeField('DATE_UPDATE'))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_DATE_UPDATE_FIELD'))
				->configureDefaultValue(function () {
					return new DateTime();
				}),

			(new Fields\IntegerField('ACCOUNT_NUMBER'))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_ACCOUNT_NUMBER_FIELD')),

			(new Fields\IntegerField('PAYSYSTEM_ID'))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_PAYSYSTEM_ID_FIELD'))
				->configureRequired(),

			(new Fields\StringField('REGISTRY',
				[
					'validation' => static function (): array {
						return [
							new Validators\LengthValidator(null, 255),
						];
					}
				]
			))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_REGISTRY_FIELD'))
				->configureRequired(),

			(new Fields\DecimalField('AMOUNT'))
				->configureTitle(Loc::getMessage('PAYMENTS_ENTITY_AMOUNT_FIELD'))
				->configurePrecision(2)
				->configureRequired(),

			(new Fields\EnumField('STATUS',
				[
					'validation' => static function(): array {
						return [
							new Validators\EnumValidator()
						];
					},
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
}