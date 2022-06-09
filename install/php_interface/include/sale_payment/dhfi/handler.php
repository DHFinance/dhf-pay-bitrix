<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Web\Json;
use Bitrix\Sale;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;
use Citrus\DHFi\DTO\Payment as PaymentDTO;
use Citrus\DHFi\Entity\PaymentTable;
use Citrus\DHFi\Util\DHFPayWithLogs;
use DHF\Pay\DHFPay;
use DHF\Pay\Exception\DHFBadRequestException;
use DHF\Pay\Exception\DHFUnauthorisedException;

Loc::loadMessages(__FILE__);

class DhfiHandler extends PaySystem\ServiceHandler
{
	/**
	 * @param Sale\Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Sale\Payment $payment, Request $request = null)
	{
		$result = new PaySystem\ServiceResult();

		$params = [];
		if ($payment->getField('PS_INVOICE_ID')) {
			$checkPaymentResult = $this->checkCreatedPayment($payment);
			if ($checkPaymentResult->isSuccess()) {
				$params = $checkPaymentResult->getData();
			} else {
				$result->setErrors($checkPaymentResult->getErrors());
				return $result;
			}
		}

		if (!isset($params['URL'], $params['PAYMENT'])) {
			$createPaymentResult = $this->createDhfiPayment($payment);
			if ($createPaymentResult->isSuccess()) {
				$this->setPsData($createPaymentResult->getPsData());
				$params = $createPaymentResult->getData();
			} else {
				$params['ERROR_DETAILS'] = implode(', ', $createPaymentResult->getErrorMessages());
			}
		}

		$this->setExtraParams($params);
		if (isset($params['URL'])) {
			$result->setPaymentUrl($params['URL']);
		}

		$showTemplateResult = $this->showTemplate($payment, 'template');
		if ($showTemplateResult->isSuccess()) {
			$result->setTemplate($showTemplateResult->getTemplate());
		} else {
			$result->addErrors($showTemplateResult->getErrors());
		}

		return $result;
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

	protected function getApiClient(Payment $payment): DHFPay
	{
		$endpoint = sprintf('https://%s/api/', $this->getBusinessValue($payment, 'DHFI_PAYMENT_GATEWAY'));
		$token = $this->getBusinessValue($payment, 'DHFI_API_KEY');
		return new DHFPayWithLogs($endpoint, $token);
	}

	protected function createDhfiPayment(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		try {
			$dto = $this->makePaymentDto($payment);
			$createdPayment = (new \Citrus\DHFi\Payment(
				$this->getApiClient($payment)
			))->create($dto);
		} catch (DHFBadRequestException|DHFUnauthorisedException $e) {
			PaySystem\Logger::addError(__METHOD__ . ': failed to create payment. ' . self::jsonEncode([
					'message' => $e->getMessage(),
					'code' => $e->getCode(),
				]));
			$result->addError(PaySystem\Error::createForBuyer(Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_ERROR_HAPPENED'),
				$e->getCode()));
			return $result;
		}

		$dto->id = $createdPayment->id;

		$paymentAddResult = PaymentTable::add([
			'ID' => $createdPayment->id,
			'PAYSYSTEM_ID' => $payment->getPaymentSystemId(),
			'ACCOUNT_NUMBER' => $payment->getField('ACCOUNT_NUMBER'),
			'REGISTRY' => $payment->getPaySystem()->getField('ENTITY_REGISTRY_TYPE'),
			'AMOUNT' => $dto->amount,
		]);
		if (!$paymentAddResult->isSuccess()) {
			PaySystem\Logger::addError(__METHOD__ . ': failed to save created to db. ' . implode(', ',
					$paymentAddResult->getErrorMessages()));
		}

		$result->setPsData(['PS_INVOICE_ID' => $dto->id]);
		$result->setData([
			'URL' => $result->getPaymentUrl(),
			'PAYMENT' => $dto->toArray(),
		]);

		return $result;
	}

	private function makePaymentUrl(Payment $payment, PaymentDTO $dto): string
	{
		return sprintf(
			'https://%s/bill/%d',
			$this->getBusinessValue($payment, 'DHFI_PAYMENT_GATEWAY'),
			$dto->id
		);
	}

	/**
	 * @param Payment $payment
	 * @return PaymentDTO
	 */
	protected function makePaymentDto(Payment $payment): PaymentDTO
	{
		return new PaymentDTO([
			'amount' => PriceMaths::roundPrecision($payment->getSum()),
			'comment' => $this->getPaymentDescription($payment),
		]);
	}

	private function isSumCorrect(Payment $payment, PaymentDTO $dto): bool
	{
		return PriceMaths::roundPrecision($dto->amount) === PriceMaths::roundPrecision($payment->getSum());
	}

	protected function checkCreatedPayment(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		try {
			$paymentApi = new \Citrus\DHFi\Payment($this->getApiClient($payment));
			$existingPayment = $paymentApi->get($payment->getField('PS_INVOICE_ID'));
		} catch (\Exception $e) {
			PaySystem\Logger::addError(__METHOD__ . ': failed to check existing payment. ' . $e->getMessage());
			return $result;
		}

		if (!$this->isSumCorrect($payment, $existingPayment)) {
			PaySystem\Logger::addError(__METHOD__ . ': existing payment sum mismatch. Should create new one. ' . self::jsonEncode($existingPayment->toArray()));
			return $result;
		}

		if ($existingPayment->status !== 'Not_paid') {
			PaySystem\Logger::addDebugInfo(__METHOD__ . ': existing payment have already been payed. Should create new one. ' . self::jsonEncode($existingPayment->toArray()));
			return $result;
		}

		$result->setData([
			'URL' => $this->makePaymentUrl($payment, $existingPayment),
			'PAYMENT' => $existingPayment->toArray(),
		]);
		return $result;
	}

	public function processRequest(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();
		$payload = (new JsonPayload())->getData();
		$dto = new PaymentDTO($payload);

		PaySystem\Logger::addDebugInfo(__METHOD__ . ': request: ' . self::jsonEncode($payload));

		if ($dto->store->apiKey !== $this->getBusinessValue($payment, 'DHFI_API_KEY')) {
			$result->addError(new Error(Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_INCORRECT_API_KEY', [
				'#GOT#' => $dto->store->apiKey,
				'#EXPECTED#' => $this->getBusinessValue($payment, 'DHFI_API_KEY'),
				'#REQUEST#' => self::jsonEncode($payload),
			])));
			return $result;
		}

		if ($dto->status === 'Paid') {
			$fields = [
				'PS_INVOICE_ID' => $dto->id,
				'PS_STATUS_CODE' => $dto->status,
				'PS_SUM' => $dto->amount,
				'PS_STATUS' => 'N',
				'PS_RESPONSE_DATE' => new \Bitrix\Main\Type\DateTime(),
				'PS_STATUS_DESCRIPTION' => Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_PS_STATUS_DESCRIPTION', [
					'#ID#' => $dto->id
				]),
			];

			if ($this->isSumCorrect($payment, $dto)) {
				$fields['PS_STATUS'] = 'Y';
				$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
			} else {
				$error = Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_INCORRECT_SUM_RECEIVED', [
					'#GOT#' => $dto->amount,
					'#EXPECTED#' => $payment->getSum(),
				]);
				$result->addError(new Error($error));
				$fields['PS_STATUS_DESCRIPTION'] .= '. ' . $error;
				return $result;
			}
			$result->setPsData($fields);
		} else {
			$error = Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_INCORRECT_STATUS_RECEIVED', [
				'#STATUS#' => $dto->status,
			]);
			$result->addError(new Error($error));
		}

		return $result;
	}

	public function getPaymentIdFromRequest(Request $request)
	{
		$jsonPayload = (new JsonPayload())->getData();
		$dto = new PaymentDTO($jsonPayload);

		$paymentInfo = self::getPaymentInfo($dto);
		if (!$paymentInfo) {
			return null;
		}

		return $paymentInfo['ACCOUNT_NUMBER'];
	}

	protected static function getPaymentInfo(PaymentDTO $payment, array $additionalFilter = []): ?array
	{
		return PaymentTable::getRow([
			'select' => ['*'],
			'filter' => array_merge([
				'ID' => $payment->id,
			], $additionalFilter),
		]) ?: null;
	}

	public static function isMyResponse(Request $request, $paySystemId)
	{
		try {
			$jsonPayload = (new JsonPayload())->getData();
			$dto = new PaymentDTO($jsonPayload);
			$service = PaySystem\Manager::getObjectById($paySystemId);

			$paymentInfo = self::getPaymentInfo($dto);
			return $paymentInfo
				&& $paymentInfo['PAYSYSTEM_ID'] == $paySystemId
				&& $paymentInfo['REGISTRY'] == $service->getField('ENTITY_REGISTRY_TYPE');
		} catch (\Exception $e) {
			return false;
		}
	}

	public static function jsonEncode(array $data)
	{
		return Json::encode($data, JSON_UNESCAPED_UNICODE);
	}
}
