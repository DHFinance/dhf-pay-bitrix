<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PriceMaths;
use Citrus\DHFi\Integration\Factory;
use Citrus\DHFi\Util\ModuleLocation;

use const Citrus\DHFi\CSPR_CURRENCY_CODE;

define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC", "Y");
define('SKIP_TEMPLATE_AUTH_ERROR', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php";

/**
 * @var CMain $APPLICATION
 */

if (!Main\Loader::includeModule('citrus.dhfi')) {
	die();
}

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . ModuleLocation::getModuleDir() . "/pub/payment_dhfi.php");

Main\UI\Extension::load(['ui.bootstrap4']);

/** @todo Вынести в компонент публичную страницу платежа */
try {
	$integration = Factory::createFromPublicPage($_REQUEST['payment']);
	$payment = $integration->fetchPayment();

	if (!$payment) {
		throw new RuntimeException(Loc::getMessage('CITRUS_DHFI_PUB_PAYMENT_NOT_FOUND'));
	}

	$dhfiPayment = $integration->getDhfiPaymentInfo();
} catch (RuntimeException $e) {
	ShowError($e->getMessage());
	Main\Application::getInstance()->terminate(404);
} catch (\Throwable $e) {
	ShowError('Unhandled ' . get_class($e));
	Main\Application::getInstance()->getExceptionHandler()->writeToLog($e);
	Main\Application::getInstance()->terminate(404);
}

$title = Loc::getMessage("CITRUS_DHFI_PUB_PAYMENT_TITLE", ['#NUM#' => $integration->fetchPayment()->getId()]);
$APPLICATION->SetTitle($title);

$amountInBaseCurrency = \CCrmCurrency::ConvertMoney($payment->getAmount(), CSPR_CURRENCY_CODE, CCrmCurrency::GetBaseCurrencyID());
$invoice = $integration->loadInvoice();

?>
	<div style="padding: 2em">
		<h1><?=$title?></h1>
		<dl>
			<dt><?=Loc::getMessage("CITRUS_DHFI_PUB_PAYMENT_SUM")?></dt>
			<dd>
				<?=CCrmCurrency::MoneyToString($payment->getAmount(), CSPR_CURRENCY_CODE)?>
				<?php
				if (CSPR_CURRENCY_CODE !== CCrmCurrency::GetBaseCurrencyID()) {
					?>(<?=CCrmCurrency::MoneyToString($amountInBaseCurrency, CCrmCurrency::GetBaseCurrencyID())?>)<?
				}
				?>
			</dd>

			<dt><?=Loc::getMessage("CITRUS_DHFI_PUB_PAYMENT_INVOICE_NUM")?></dt>
			<dd><?=$invoice->number?></dd>

			<dt><?=Loc::getMessage("CITRUS_DHFI_PUB_PAYMENT_STATUS")?></dt>
			<dd><?=Loc::getMessage("CITRUS_DHFI_PUB_PAYMENT_STATUS_" . strtoupper($payment->getStatus()))?></dd>

			<dt><?=Loc::getMessage("CITRUS_DHFI_PUB_PAYMENT_COMMENT")?></dt>
			<dd><?=$dhfiPayment->comment?></dd>

			<dt><?=Loc::getMessage("CITRUS_DHFI_PUB_PAYMENT_WALLET")?></dt>
			<dd><?=$dhfiPayment->store->wallet?></dd>
		</dl>
		<?php
		if (count($invoice->products)) {
			?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th><?=Loc::getMessage('CITRUS_DHFI_PUB_ROWS_TITLE')?></th>
						<th class="text-right"><?=Loc::getMessage('CITRUS_DHFI_PUB_ROWS_PRICE')?></th>
						<th class="text-right"><?=Loc::getMessage('CITRUS_DHFI_PUB_ROWS_QUANTITY')?></th>
						<th class="text-right"><?=Loc::getMessage('CITRUS_DHFI_PUB_ROWS_SUM')?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				/** @var \Bitrix\Crm\Invoice\BasketItem $item */
				$totals = 0;
				foreach ($invoice->products as $item) {
					$sum = PriceMaths::roundPrecision($item->price * $item->quantity);
					$totals += CCrmCurrency::ConvertMoney($sum, $item->currency, $invoice->currency);
					?>
					<tr>
						<td><?=$item->title?></td>
						<td class="text-right"><?=CCrmCurrency::MoneyToString($item->price, $item->currency)?>
						<td class="text-right"><?=$item->quantity?></td>
						<td class="text-right"><?=CCrmCurrency::MoneyToString($sum, $item->currency)?>
					</tr>
					<?php
				}
				?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="3"></th>
						<th class="text-right"><?=CCrmCurrency::MoneyToString($totals, $invoice->currency);?></th>
					</tr>
				</tfoot>
			</table>
			<?php
		}
		?>
	</div>
<?php

require $_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php";
