<?php

namespace Citrus\DHFi;

use DHF\Pay\DHFPay;
use Spatie\DataTransferObject\Arr;

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
		$paymentParam = Arr::only($payment->toArray(), ['amount', 'comment']);

		return new DTO\CreatePaymentResponse(
			$this->client->payments()->add($paymentParam)
		);
	}

}