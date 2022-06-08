<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem;
use Citrus\DHFi\DTO\Payment as PaymentDTO;
use Citrus\DHFi\Entity\PaymentTable;
use Citrus\DHFi\Util\DHFPayWithLogs;
use DHF\Pay\Exception\DHFBadRequestException;

Loc::loadMessages(__FILE__);

class DhfiHandler extends PaySystem\BaseServiceHandler
{
	/**
	 * @param Sale\Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Sale\Payment $payment, Request $request = null)
	{
		try {
			$paymentDto = new PaymentDTO([
				'amount' => $this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY'),
				'comment' => $this->getPaymentDescription($payment),
			]);

			$paymentId = $this->createPayment($payment, $paymentDto);
		} catch (DHFBadRequestException $e) {
			$this->setExtraParams([
				'error' => $e->getMessage(),
			]);
			return $this->showTemplate($payment, 'template');
		}

		$url = sprintf('https://%s/bill/%d', $this->getBusinessValue($payment, 'DHFI_PAYMENT_GATEWAY'), $paymentId);
		$this->setExtraParams([
			'payment' => $paymentDto->toArray(),
			'url' => $url,
		]);

		return $this->showTemplate($payment, 'template');
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return ['CSPR'];
	}

	private function getPaymentDescription(Payment $payment)
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();
		$userEmail = $order->getPropertyCollection()->getUserEmail();

		return str_replace(
			[
				'#PAYMENT_NUMBER#',
				'#ORDER_NUMBER#',
				'#PAYMENT_ID#',
				'#ORDER_ID#',
				'#USER_EMAIL#'
			],
			[
				$payment->getField('ACCOUNT_NUMBER'),
				$order->getField('ACCOUNT_NUMBER'),
				$payment->getId(),
				$order->getId(),
				($userEmail) ? $userEmail->getValue() : ''
			],
			$this->getBusinessValue($payment, 'PAYMENT_DESCRIPTION')
		);
	}

	protected function createPayment(Payment $payment, PaymentDTO $paymentDto): int
	{
		$endpoint = sprintf('https://%s/api/', $this->getBusinessValue($payment, 'DHFI_PAYMENT_GATEWAY'));
		$token = $this->getBusinessValue($payment, 'DHFI_API_KEY');
		$apiClient = new DHFPayWithLogs($endpoint, $token);

		$createdPayment = (new \Citrus\DHFi\Payment($apiClient))->create($paymentDto);
		return $createdPayment->id;
		// @todo Сохранять в PaymentTable, чтобы избежать создания повторных платежей и для обработки уведомлений об успешной оплате
		/*$result = PaymentTable::add([
			'ID' => $createdPayment->id,
			'ENTITY_ID' => $this->getInvoiceId(),
			'ENTITY_TYPE' => $this->entityTypeId,
			'AMOUNT' => $paymentDto->amount,
		]);
		if (!$result->isSuccess()) {
			throw new \RuntimeException(implode(', ', $result->getErrorMessages()));
		}
		return $result->getId();*/
	}
}
