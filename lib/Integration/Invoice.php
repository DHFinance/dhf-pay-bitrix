<?php /** @noinspection PhpUnused */

namespace Citrus\DHFi\Integration;

use Bitrix\Main;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Citrus\DHFi\Config;
use Citrus\DHFi\DTO\Payment;
use Citrus\DHFi\Util\LoggerFactory;

use DHF\Pay\Exception\DHFBadRequestException;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

Loc::loadMessages(__FILE__);

class Invoice extends Engine\JsonController implements LoggerAwareInterface
{
	protected LoggerInterface $logger;

	public function __construct(Request $request = null)
	{
		parent::__construct($request);

		$this->logger = LoggerFactory::create('invoice');
	}

	public function configureActions()
	{
		return [
			'getLink' => [],
			'confirmPayment' => [
				"prefilters" => [
					new Engine\ActionFilter\ContentType([Engine\ActionFilter\ContentType::JSON])
				],
			],
		];
	}

	/**
	 * Создает платеж на основе счёта (старой версии) и возвращает ссылку на него
	 *
	 * @param int $invoiceId ID счета
	 * @return AjaxJson{url: string}
	 */
	public function getLinkAction(int $invoiceId)
	{
		$invoice = new InvoiceIntegration($invoiceId);
		try {
			if (!$invoice->fetchPayment()) {
				$invoice->createPayment();
			}
		} catch (DHFBadRequestException $e) {
			throw new \RuntimeException(Loc::getMessage('CITRUS_DHFI_INVOICE_ERROR_CREATING_PAYMENT') . $e->getMessage());
		}

		return AjaxJson::createSuccess([
			'url' => $invoice->getPublicUrl(),
		]);
	}

	protected function log(string $level, $message, array $context = []): void
	{
		$this->logger->log($level, $message, array_merge([
			'remote_addr' => $this->request->getRemoteAddress(),
			'user_agent' => $this->request->getUserAgent(),
		], $context));
	}

	public function confirmPaymentAction()
	{
		try {
			$json = (new Main\Engine\JsonPayload())->getData();
			$paymentDto = new Payment($json);
			if ($paymentDto->store->apiKey !== Config::getApiKey()) {
				throw new Main\ArgumentException('Wrong store api key');
			}
		} catch (Main\ArgumentException $e) {
			$this->log(LogLevel::ERROR, 'Failed to decode json request');
			throw new \RuntimeException("Bad request");
		}

		$invoice = Factory::createForPayment($paymentDto->id);
		$result = $invoice->fetchPayment()
			->setStatus($paymentDto->status)
			->save();
		if (!$result->isSuccess()) {
			$this->log(LogLevel::ERROR, 'Failed to update payment', ['errors' => $result->getErrorMessages()]);
			throw new \RuntimeException('Failed to update payment: ' . implode(', ', $result->getErrorMessages()));
		}

		if ($paymentDto->status === 'Paid') {
			$invoice->confirmPayment();
		}

		$this->log(LogLevel::INFO, 'Successully processed request');

		return AjaxJson::createSuccess();
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}
