<?php

namespace Citrus\DHFi\Tests\Integration;

use Citrus\DHFi\DTO\Payment as PaymentDto;
use Citrus\DHFi\Payment;
use DHF\Pay\Exception\DHFBadRequestException;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
	protected $validPayment = [
		'amount' => 2.6,
		'comment' => 'Test payment'
	];

	protected $invalidPayment = [
		'amount' => 'hi honey',
		'comment' => 'Spoiled payment'
	];

	protected $insufficientAmountPayment = [
		'amount' => 1.0,
		'comment' => 'Payment with insufficient amount'
	];

	public function testCreate(): void
	{
		$paymentDto = new PaymentDto($this->validPayment);
		$result = (new Payment())->create($paymentDto);

		$this->assertGreaterThan(1, $result->id);

		$createdPayment = Payment::get($result->id);
		$this->assertSame('Not_paid', $createdPayment->status, 'New payment status should be Not_paid');
		$this->assertSame($paymentDto->amount, $createdPayment->amount, 'New payment amount should match the value passed to api');
		$this->assertSame($paymentDto->comment, $createdPayment->comment, 'New payment comment should match the value passed to api');
	}

	public function testInvalidPayment(): void
	{
		$this->expectException(DHFBadRequestException::class);

		$paymentDto = new PaymentDto($this->invalidPayment);
		$result = (new Payment())->create($paymentDto);
	}

	public function testInsufficientAmount(): void
	{
		$this->expectException(DHFBadRequestException::class);
		$this->expectExceptionMessage('amount must not be less than 2.5');

		$paymentDto = new PaymentDto($this->insufficientAmountPayment);
		$result = (new Payment())->create($paymentDto);
	}
}