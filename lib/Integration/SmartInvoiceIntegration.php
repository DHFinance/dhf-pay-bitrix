<?php

namespace Citrus\DHFi\Integration;

use Bitrix\Crm;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Main\Localization\Loc;
use Citrus\DHFi\DTO\Payment as PaymentDTO;

use CCrmCurrency;
use CCrmOwnerType;
use Citrus\DHFi\Integration\DTO\ProductRow;
use RuntimeException;

use const Citrus\DHFi\CSPR_CURRENCY_CODE;

Loc::loadMessages(__FILE__);

class SmartInvoiceIntegration extends AbstractInvoiceIntegration
{
	private Crm\Service\Factory $factory;

	/**
	 * @param int $invoiceId
	 * @param int $entityType Тип счета: {@see CCrmOwnerType::Invoice} или {@see CCrmOwnerType::SmartInvoice}
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function __construct(int $invoiceId)
	{
		parent::__construct($invoiceId);

		$this->entityTypeId = CCrmOwnerType::SmartInvoice;

		$factory = Crm\Service\Container::getInstance()->getFactory(CCrmOwnerType::SmartInvoice);
		if (!$factory) {
			throw new \RuntimeException('No factory for ' . CCrmOwnerType::SmartInvoiceName);
		}
		$this->factory = $factory;
	}

	protected function createPaymentDTO(): PaymentDTO
	{
		$invoice = $this->fetchInvoice();

		/** @var float $amount Сумма в casper coin */
		$amount = CCrmCurrency::ConvertMoney($invoice->getOpportunityAccount(), $invoice->getAccountCurrencyId(), CSPR_CURRENCY_CODE);

		return new PaymentDTO([
			'amount' => $amount,
			'comment' => Loc::getMessage('CITRUS_DHFI_SMART_INVOICE_COMMENT', ['#NUM#' => $this->getInvoiceId()]),
		]);
	}

	protected function fetchInvoice(): Crm\Item\SmartInvoice
	{
		$invoice = $this->factory->getItem($this->getInvoiceId());
		if (!$invoice) {
			throw new RuntimeException(sprintf('Invoice is not found: %d', $this->getInvoiceId()));
		}
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $invoice;
	}

	public function loadInvoice(): DTO\Invoice
	{
		$invoice = $this->fetchInvoice();
		return new DTO\Invoice([
			'number' => $invoice->getAccountNumber(),
			'currency' => $invoice->getCurrencyId(),
			'products' => ProductRow::arrayOf(
				array_map(function (array $product) use ($invoice) {
					return [
						'title' => $product['PRODUCT_NAME'],
						'quantity' => $product['QUANTITY'],
						'price' => $product['PRICE'],
						'currency' => $invoice->getCurrencyId(),
					];
				}, $invoice->getProductRows()->toArray())
			)
		]);
	}

	public function confirmPayment(): void
	{
		$stages = $this->factory->getStages();
		$index = array_search(PhaseSemantics::SUCCESS, $stages->getSemanticsList());
		if (!$index) {
			throw new RuntimeException('Failed to find payed invoice stage');
		}
		$payedStage = $stages->getAll()[$index]->getStatusId();

		$invoice = $this->fetchInvoice();

		$updated = $this->fetchInvoice()
			->setStageId($payedStage);

		$updateOperation = $this->factory->getUpdateOperation($updated);
		$result = $updateOperation->launch();

		if (!$result->isSuccess()) {
			throw new RuntimeException(implode(', ', $result->getErrorMessages()));
		}
	}
}