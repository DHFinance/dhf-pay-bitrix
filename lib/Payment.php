<?php

namespace Citrus\DHFi;

use Bitrix\Main\Localization\Loc;
use Spatie\DataTransferObject\Arr;
use DHF\Pay\DHFPay;

Loc::loadMessages(__FILE__);

class Payment
{
	private DHFPay $client;

	public function __construct(DHFPay $client)
	{
		$this->client = $client;
	}

	public function get(int $id): DTO\Payment
	{
		return new DTO\Payment(
			$this->client->payments()->getOne($id)
		);
	}

	public function create(DTO\Payment $payment): DTO\CreatePaymentResponse
	{
		if ($payment->amount < 2.5) {
			throw new PaymentException(Loc::getMessage('CITRUS_DHFI_PAYMENT_ERROR_INSUFFICIENT_AMOUNT'));
		}

		$paymentParam = Arr::only($payment->toArray(), ['amount', 'comment']);

		return new DTO\CreatePaymentResponse(
			$this->client->payments()->add($paymentParam)
		);
	}

}