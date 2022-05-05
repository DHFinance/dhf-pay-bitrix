<?php

namespace Citrus\DHFi\Integration;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;
use Citrus\DHFi\Config;
use Citrus\DHFi\Entity\EO_Payment;
use Citrus\DHFi\Entity\PaymentTable;
use Citrus\DHFi\Payment;
use Citrus\DHFi\DTO\Payment as PaymentDTO;

use CHTTP;
use RuntimeException;

Loc::loadMessages(__FILE__);

abstract class AbstractInvoiceIntegration
{
	private const PUBLIC_PATH = '/pub/dhfi-pay/%s';

	protected int $invoiceId;
	protected int $entityTypeId;

	/**
	 * @param int $invoiceId
	 * @throws LoaderException
	 */
	public function __construct(int $invoiceId)
	{
		Loader::requireModule('crm');
		$this->invoiceId = $invoiceId;
	}

	public function getPublicUrl(): string
	{
		if ($payment = $this->fetchPayment()) {
			$paymentId = $payment->getId();
		} else {
			$paymentId = $this->createPayment();
		}
		return sprintf(Config::getPaymentUrlTempalte(), $paymentId);
	}

	public function fetchPayment(): ?EO_Payment
	{
		return PaymentTable::query()
			->where('ENTITY_ID', $this->getInvoiceId())
			->where('ENTITY_TYPE', $this->entityTypeId)
			->setSelect(['*'])
			->setCacheTtl(30)
			->fetchObject();
	}

	public function getDhfiPaymentInfo(): PaymentDTO
	{
		return Payment::get($this->fetchPayment()->getId());
	}

	/**
	 * @return int
	 */
	public function getInvoiceId(): int
	{
		return $this->invoiceId;
	}

	public function createPayment(): int
	{
		$dto = $this->createPaymentDTO();
		$createdPayment = (new Payment())->create($dto);

		$result = PaymentTable::add([
			'ID' => $createdPayment->id,
			'ENTITY_ID' => $this->getInvoiceId(),
			'ENTITY_TYPE' => $this->entityTypeId,
			'AMOUNT' => $dto->amount,
		]);
		if (!$result->isSuccess()) {
			throw new RuntimeException(implode(', ', $result->getErrorMessages()));
		}
		return $result->getId();
	}

	abstract protected function createPaymentDTO();

	abstract public function loadInvoice(): DTO\Invoice;

	abstract public function confirmPayment(): void;
}