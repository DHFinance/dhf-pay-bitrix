<?php

namespace Citrus\DHFi\Integration;

use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Invoice\Invoice;
use Citrus\DHFi\DTO\Payment as PaymentDTO;

use Citrus\DHFi\Integration\DTO\ProductRow;
use RuntimeException;
use CCrmCurrency;
use CCrmOwnerType;

use const Citrus\DHFi\CSPR_CURRENCY_CODE;

Loc::loadMessages(__FILE__);

class InvoiceIntegration extends AbstractInvoiceIntegration
{
	/**
	 * @inheritdoc
	 */
	public function __construct(int $invoiceId)
	{
		parent::__construct($invoiceId);
		$this->entityTypeId = CCrmOwnerType::Invoice;
	}

	protected function fetchInvoice(): Invoice
	{
		$invoice = Invoice::load($this->getInvoiceId());
		if (!$invoice) {
			throw new RuntimeException(sprintf('Invoice is not found: %d', $this->getInvoiceId()));
		}
		return $invoice;
	}

	protected function createPaymentDTO(): PaymentDTO
	{
		$invoice = $this->fetchInvoice();

		/** @var float $amount Сумма в casper coin */
		$amount = CCrmCurrency::ConvertMoney($invoice->getPrice(), $invoice->getCurrency(), CSPR_CURRENCY_CODE);
		$topic = $invoice->getFieldValues()['ORDER_TOPIC'];
		return new PaymentDTO([
			'amount' => $amount,
			'comment' => Loc::getMessage('CITRUS_DHFI_PAYMENT_COMMENT', [
				'#NUM#' => $this->getInvoiceId(),
				'#TOPIC#' => $topic ? ': ' . $topic : '',
			]),
		]);
	}

	public function loadInvoice(): DTO\Invoice
	{
		$invoice = $this->fetchInvoice();
		return new DTO\Invoice([
			'number' => $invoice->getField('ACCOUNT_NUMBER') ?: $invoice->getId(),
			'currency' => $invoice->getCurrency(),
			'products' => ProductRow::arrayOf(
				array_map(function (array $product) {
					return [
						'title' => $product['NAME'],
						'quantity' => (float)$product['QUANTITY'],
						'price' => (float)$product['PRICE'],
						'currency' => $product['CURRENCY'],
					];
				}, $invoice->getBasket()->toArray())
			)
		]);
	}

	protected function getPayedStatus()
	{
		$semantic = \CCrmStatus::GetInvoiceStatusSemanticInfo();

		return $semantic['FINAL_SUCCESS_FIELD'];
	}

	public function confirmPayment(): void
	{
		$invoice = $this->fetchInvoice();
		$invoice->setField('STATUS_ID', $this->getPayedStatus());

		$result = $invoice->save();
		if (!$result->isSuccess()) {
			throw new RuntimeException(implode(', ', $result->getErrorMessages()));
		}
	}
}