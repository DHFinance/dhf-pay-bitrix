<?php

namespace Citrus\DHFi\Integration;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service;
use Bitrix\Crm\Timeline;
use Bitrix\Intranet\Binding\Menu;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use DHF\Pay\Exception\DHFBadRequestException;

use CCrmOwnerType;

Loc::loadMessages(__FILE__);

class EventHandlers
{
	/**
	 * Добавляет пункт в контекстное меню списка старой версии счетов
	 */
	public static function onCrmInvoiceListItemBuildMenu(string $where, array $params, array &$actions): void
	{
		if ($where !== 'CRM_INVOICE_LIST_MENU') {
			return;
		}

		$actions[] = [
			'SEPARATOR' => true,
		];
		$actions[] = [
			'TITLE' => Loc::getMessage('CITRUS_DHFI_GENERATE_LINK_MENU_ITEM_TITLE'),
			'TEXT' => Loc::getMessage('CITRUS_DHFI_GENERATE_LINK_MENU_ITEM_TEXT'),
			'ONCLICK' => sprintf('BX.loadExt("citrus.dhfi.payment-link").then(function () { BX.Citrus.DHFi.PaymentLink.getInstance().show(%d); })',
				$params['ID'])
		];
	}

	/**
	 * Добавляет кнопку на тулбар страницы просмотра старой версии счета
	 */
	public static function onBuildBindingMenu(Event $event): void
	{
		$places = [
			[
				'bindings' => [
					'crm_documents' => [
						'include' => [
							'invoice',
						],
						//'extension' => 'citrus.dhfi.payment-link',
					],
				],
				'items' => [
					[
						'id' => 'citrus_dhfi_create_payment',
						'system' => false,
						'sectionCode' => Menu::SECTIONS['other'],
						'text' => Loc::getMessage('CITRUS_DHFI_GENERATE_LINK_MENU_ITEM_TEXT'),
						'onclick' => 'BX.loadExt("citrus.dhfi.payment-link").then(function () { BX.Citrus.DHFi.PaymentLink.getInstance().showForDetail(); })'
					]
				],
			],
		];
		$event->addResult(new EventResult(EventResult::SUCCESS, $places));
	}

	/**
	 * При создании счета на смарт-процессах, добавляет в таймлайн счета ссылку на страницу оплаты
	 *
	 * @param Event $event
	 * @return void
	 */
	public static function onCrmDynamicItemAdd(Event $event)
	{
		/** @var Item $item */
		$item = $event->getParameter('item');
		if (!$item instanceof Item\SmartInvoice) {
			return;
		}

		if ($item->getOpportunity() <= 0) {
			return;
		}

		$invoice = new SmartInvoiceIntegration($item->getId());
		$text = null;
		try {
			if (!$invoice->fetchPayment()) {
				$invoice->createPayment();
				$text = Loc::getMessage('CITRUS_DHFI_TIMELINE_PAYMENT_TEXT', ['#URL#' => $invoice->getPublicUrl()]);
			}
		} catch (DHFBadRequestException $e) {
			$text = Loc::getMessage('CITRUS_DHFI_TIMELINE_ERROR_CREATING_PAYMENT') . $e->getMessage();
		}

		$timelineEntryFacade = Service\Container::getInstance()->getTimelineEntryFacade();
		$timelineEntryFacade->create(Timeline\CommentEntry::class, [
			'ENTITY_TYPE_ID' => CCrmOwnerType::SmartInvoice,
			'ENTITY_ID' => $item->getId(),
			'BINDINGS' => [
				[
					'ENTITY_TYPE_ID' => CCrmOwnerType::SmartInvoice,
					'ENTITY_ID' => $item->getId(),
				],
			],
			'AUTHOR_ID' => $item->getCreatedBy(),
			'TEXT' => $text,
		]);
	}
}