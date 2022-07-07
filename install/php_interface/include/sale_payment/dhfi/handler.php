<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem\Service;
use Bitrix\Sale\PaySystem\ServiceResult;

use DHF\Pay\DHFPay;
use DHF\Pay\Exception\DHFBadRequestException;
use DHF\Pay\Exception\DHFUnauthorisedException;

use Citrus\DHFi\DTO\Payment as PaymentDTO;
use Citrus\DHFi\Payment as PaymentAPI;
use Citrus\DHFi\Entity\PaymentTable;
use Citrus\DHFi\Util\DHFPayWithLogs;
use Citrus\DHFi\Util\LoggerFactory;
use Citrus\DHFi\PaymentException;

use const Citrus\DHFi\CSPR_CURRENCY_CODE;

Main\Localization\Loc::loadMessages(__FILE__);

class DhfiHandler extends Sale\PaySystem\ServiceHandler
{
	/** @var \Psr\Log\LoggerInterface */
	protected $logger;

	public function __construct($type, Service $service)
	{
		Main\Loader::requireModule('citrus.dhfi');

		$this->logger = LoggerFactory::create('handler');

		parent::__construct($type, $service);
	}

	/**
	 * @param Sale\Payment $payment
	 * @param Main\Request|null $request
	 * @return ServiceResult
	 */
	public function initiatePay(Sale\Payment $payment, Main\Request $request = null)
	{
		$result = new ServiceResult();

		$params = [];
		if ($payment->getField('PS_INVOICE_ID')) {
			$checkPaymentResult = $this->checkCreatedPayment($payment);
			$this->logger->debug(__FUNCTION__ . ' existing payment', $checkPaymentResult->getData());
			if ($checkPaymentResult->isSuccess()) {
				$params = $checkPaymentResult->getData();
			} else {
				$result->setErrors($checkPaymentResult->getErrors());
				return $result;
			}
		}

		if (!isset($params['URL'], $params['PAYMENT'])) {
			$createPaymentResult = $this->createDhfiPayment($payment);
			$this->logger->debug(__FUNCTION__ . ' created payment', $createPaymentResult->getData());
			if ($createPaymentResult->isSuccess()) {
				$result->setPsData($createPaymentResult->getPsData());
				$params = $createPaymentResult->getData();
			} else {
				$params['ERROR_DETAILS'] = implode(', ', $createPaymentResult->getErrorMessages());
			}
		}

		$this->setExtraParams($params);
		if (isset($params['URL'])) {
			$this->logger->debug(__FUNCTION__ . ' settings payment url', [
				'url' => $params['URL'],
			]);
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
		return [CSPR_CURRENCY_CODE];
	}

	private function getPaymentDescription(Sale\Payment $payment): string
	{
		/** @var Sale\PaymentCollection $collection */
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

	protected function getApiClient(Sale\Payment $payment): DHFPay
	{
		$endpoint = sprintf('https://%s/api/', $this->getBusinessValue($payment, 'DHFI_PAYMENT_GATEWAY'));
		$token = $this->getBusinessValue($payment, 'DHFI_API_KEY');
		return new DHFPayWithLogs($endpoint, $token);
	}

	protected function createDhfiPayment(Sale\Payment $payment): ServiceResult
	{
		$result = new ServiceResult();

		try {
			$dto = $this->makePaymentDto($payment);
			$createdPayment = (new PaymentAPI(
				$this->getApiClient($payment)
			))->create($dto);
		} catch (DHFBadRequestException|DHFUnauthorisedException $e) {
			$this->logger->error(__FUNCTION__ . ': failed to create payment', [
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			]);
			$result->addError(Sale\PaySystem\Error::createForBuyer(Main\Localization\Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_ERROR_HAPPENED'),
				$e->getCode()));
			return $result;
		}
		catch (PaymentException $e) {
			$result->addError(Sale\PaySystem\Error::createForBuyer($e->getMessage()));
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
			$this->logger->error(__FUNCTION__ . ': failed to save created to db', [
				'messages' => $paymentAddResult->getErrorMessages(),
			]);
		}

		$result->setPsData(['PS_INVOICE_ID' => $dto->id]);
		$result->setData([
			'URL' => $this->makePaymentUrl($payment, $dto),
			'PAYMENT' => $dto->toArray(),
		]);

		return $result;
	}

	private function makePaymentUrl(Sale\Payment $payment, PaymentDTO $dto): string
	{
		return sprintf(
			'https://%s/bill/%d',
			$this->getBusinessValue($payment, 'DHFI_PAYMENT_GATEWAY'),
			$dto->id
		);
	}

	/**
	 * Возвращает сумму счета. Если валюта счета отличается от CSPR,
	 * производит конвертацию по текущему курсу с помощью модуля «Валюты»
	 *
	 * @param Sale\Payment $payment
	 * @return float
	 */
	protected function getPaymentAmountInCSPR(Sale\Payment $payment): float
	{
		$currency = $payment->getField('CURRENCY');
		if ($currency === CSPR_CURRENCY_CODE) {
			return $payment->getSum();
		}
		return \CCrmCurrency::ConvertMoney($payment->getSum(), $currency, CSPR_CURRENCY_CODE);
	}

	/**
	 * @param Sale\Payment $payment
	 * @return PaymentDTO
	 */
	protected function makePaymentDto(Sale\Payment $payment): PaymentDTO
	{
		return new PaymentDTO([
			'amount' => Sale\PriceMaths::roundPrecision($this->getPaymentAmountInCSPR($payment)),
			'comment' => $this->getPaymentDescription($payment),
		]);
	}

	private function isSumCorrect(Sale\Payment $payment, PaymentDTO $dto): bool
	{
		return Sale\PriceMaths::roundPrecision($dto->amount) === Sale\PriceMaths::roundPrecision($payment->getSum());
	}

	protected function checkCreatedPayment(Sale\Payment $payment): ServiceResult
	{
		$result = new ServiceResult();

		try {
			$paymentApi = new PaymentAPI($this->getApiClient($payment));
			$existingPayment = $paymentApi->get($payment->getField('PS_INVOICE_ID'));
		} catch (\Exception $e) {
			$this->logger->error(__FUNCTION__ . ': failed to check existing payment. ', [
				'message' => $e->getMessage(),
			]);
			return $result;
		}

		if (!$this->isSumCorrect($payment, $existingPayment)) {
			$this->logger->error(__FUNCTION__ . ': existing payment sum mismatch. Should create new one', [
				'payment' => $existingPayment->toArray(),
			]);
			return $result;
		}

		if ($existingPayment->status !== 'Not_paid') {
			$this->logger->debug(__FUNCTION__ . ': existing payment have already been payed. Should create new one. ', [
				'payment' => $existingPayment->toArray(),
			]);
			return $result;
		}

		$result->setData([
			'URL' => $this->makePaymentUrl($payment, $existingPayment),
			'PAYMENT' => $existingPayment->toArray(),
		]);
		return $result;
	}

	/**
	 * @param Sale\Payment $payment
	 * @param Main\Request $request
	 * @return ServiceResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function processRequest(Sale\Payment $payment, Main\Request $request)
	{
		$result = new ServiceResult();
		$payload = (new Main\Engine\JsonPayload())->getData();
		$dto = new PaymentDTO($payload);

		$this->logger->debug(__FUNCTION__, compact('payload'));

		if ($dto->store->apiKey !== $this->getBusinessValue($payment, 'DHFI_API_KEY')) {
			$message = Main\Localization\Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_INCORRECT_API_KEY', [
				'#GOT#' => $dto->store->apiKey,
				'#EXPECTED#' => $this->getBusinessValue($payment, 'DHFI_API_KEY'),
				'#REQUEST#' => self::jsonEncode($payload),
			]);
			$result->addError(new Main\Error($message));
			$this->logger->error(__FUNCTION__ . ': ' . $message);
			return $result;
		}

		if ($dto->status === 'Paid') {
			$fields = [
				'PS_INVOICE_ID' => $dto->id,
				'PS_STATUS_CODE' => $dto->status,
				'PS_SUM' => $dto->amount,
				'PS_STATUS' => 'N',
				'PS_RESPONSE_DATE' => new Main\Type\DateTime(),
				'PS_STATUS_DESCRIPTION' => Main\Localization\Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_PS_STATUS_DESCRIPTION', [
					'#ID#' => $dto->id
				]),
			];

			if ($this->isSumCorrect($payment, $dto)) {
				$fields['PS_STATUS'] = 'Y';
				$result->setOperationType(ServiceResult::MONEY_COMING);
			} else {
				$error = Main\Localization\Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_INCORRECT_SUM_RECEIVED', [
					'#GOT#' => $dto->amount,
					'#EXPECTED#' => $payment->getSum(),
				]);
				$this->logger->error(__FUNCTION__ . ': ' . $error, [
					'dto' => $dto->toArray(),
				]);
				$result->addError(new Sale\PaySystem\Error($error));
				$fields['PS_STATUS_DESCRIPTION'] .= '. ' . $error;
				return $result;
			}
			$result->setPsData($fields);
		} else {
			$error = Main\Localization\Loc::getMessage('CITRUS_DHFI_PAYSYSTEM_INCORRECT_STATUS_RECEIVED', [
				'#STATUS#' => $dto->status,
			]);
			$this->logger->error(__FUNCTION__ . ': ' . $error, [
				'dto' => $dto->toArray(),
			]);
			$result->addError(new Sale\PaySystem\Error($error));
		}

		return $result;
	}

	/**
	 * @param Main\Request $request
	 * @return string|int|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getPaymentIdFromRequest(Main\Request $request)
	{
		$jsonPayload = (new Main\Engine\JsonPayload())->getData();
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
		]);
	}

	/**
	 * @param Main\Request $request
	 * @param int $paySystemId
	 * @return bool
	 */
	public static function isMyResponse(Main\Request $request, $paySystemId)
	{
		try {
			$jsonPayload = (new Main\Engine\JsonPayload())->getData();
			$dto = new PaymentDTO($jsonPayload);
			$service = Sale\PaySystem\Manager::getObjectById($paySystemId);

			$paymentInfo = self::getPaymentInfo($dto);
			return $paymentInfo
				&& $paymentInfo['PAYSYSTEM_ID'] == $paySystemId
				&& $paymentInfo['REGISTRY'] == $service->getField('ENTITY_REGISTRY_TYPE');
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function jsonEncode(array $data)
	{
		return Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
	}
}
