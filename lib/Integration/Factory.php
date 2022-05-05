<?php

namespace Citrus\DHFi\Integration;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;
use Citrus\DHFi\Config;
use Citrus\DHFi\Entity\PaymentTable;

class Factory
{

	/**
	 * @throws BadSignatureException
	 * @throws ArgumentException
	 */
	public static function createFromPublicPage(string $signedParams): AbstractInvoiceIntegration
	{
		try {
			$signer = new Signer();
			$params = Json::decode(
				base64_decode($signer->unsign($signedParams))
			);
		} catch (BadSignatureException $e) {
			throw new \RuntimeException(Loc::getMessage('CITRUS_DHFI_PAYMENT_NOT_FOUND'));
		}

		return static::create($params['id'], $params['type']);
	}

	public static function createForPayment(int $paymentId)
	{
		$payment = PaymentTable::getById($paymentId)
			->fetchObject();
		if (!$payment) {
			throw new \RuntimeException('Payment not found');
		}

		return static::create($payment->getEntityId(), $payment->getEntityType());
	}

	/**
	 * @param int $invoiceId ID счета
	 * @param int $invoiceType Тип счета: {@see \CCrmOwnerType::Invoice} или {@see \CCrmOwnerType::SmartInvoice}
	 * @return AbstractInvoiceIntegration
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function create(int $invoiceId, int $invoiceType): AbstractInvoiceIntegration
	{
		Loader::requireModule('crm');
		switch ($invoiceType) {
			case \CCrmOwnerType::Invoice:
				return new InvoiceIntegration($invoiceId);
			case \CCrmOwnerType::SmartInvoice:
				if (!Config::hasSmartInvoiceSupport()) {
					throw new \InvalidArgumentException('invoiceType is not supported');
				}
				return new SmartInvoiceIntegration($invoiceId);
		}
		throw new \RuntimeException('Wrong entity type');
	}
}