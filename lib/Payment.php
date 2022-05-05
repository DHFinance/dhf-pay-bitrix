<?php

namespace Citrus\DHFi;

use Spatie\DataTransferObject\Arr;

class Payment
{
	public static function get(int $id): DTO\Payment
	{
		return new DTO\Payment(
			Config::getClient()->payments()->getOne($id)
		);
	}

	public function create(DTO\Payment $payment): DTO\CreatePaymentResponse
	{
		$paymentParam = Arr::only($payment->toArray(), ['amount', 'comment']);

		return new DTO\CreatePaymentResponse(
			Config::getClient()->payments()->add($paymentParam)
		);
	}

}