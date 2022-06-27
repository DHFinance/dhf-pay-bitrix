<?php

namespace Citrus\DHFi\Tests\Integration;

use Citrus\DHFi\DTO\Payment as PaymentDto;
use Citrus\DHFi\Payment;

use Citrus\DHFi\PaymentException;

class PaymentTest extends TestCase
{
	protected $validPayment = [
		'amount' => 2.6,
		'comment' => 'Test payment'
	];

	protected $insufficientAmountPayment = [
		'amount' => 1.0,
		'comment' => 'Payment with insufficient amount'
	];

	protected Payment $paymentApi;

	protected function setUp(): void
	{
		parent::setUp();

		$this->paymentApi = new Payment($this->dhfi);
	}

	public function testCreate(): void
	{
		$paymentDto = new PaymentDto($this->validPayment);
		$result = $this->paymentApi->create($paymentDto);

		$this->assertGreaterThan(1, $result->id);

		$createdPayment = $this->paymentApi->get($result->id);
		$this->assertSame('Not_paid', $createdPayment->status, 'New payment status should be Not_paid');
		$this->assertSame($paymentDto->amount, $createdPayment->amount, 'New payment amount should match the value passed to api');
		$this->assertSame($paymentDto->comment, $createdPayment->comment, 'New payment comment should match the value passed to api');
	}

	public function testInsufficientAmount(): void
	{
		$this->expectException(PaymentException::class);
		$this->expectExceptionMessage('Минимальная сумма для оплаты: 2.5 CSPR');

		$paymentDto = new PaymentDto($this->insufficientAmountPayment);
		$result = $this->paymentApi->create($paymentDto);
	}
}