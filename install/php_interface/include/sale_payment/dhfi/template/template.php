<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Main\Localization\Loc;
use Citrus\DHFi\Util\LoggerFactory;

Loc::loadMessages(__FILE__);

/** @var array $params */

if (!empty($params['ERROR_DETAILS'])) {
	echo Loc::getMessage('DHFI_TEMPLATE_ERROR', ['#DETAILS#' => $params['ERROR_DETAILS']]);
	return;
}

$logger = LoggerFactory::create('handler');
$logger->debug('Payment template', compact('params'));

?>
<div class="mb-4">
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-lg btn-success rounded-pill"
			   href="<?=$params['URL']?>"><?=Loc::getMessage('DHFI_TEMPLATE_BUTTON_PAID')?></a>
		</div>
		<div class="col pr-0"><?=Loc::getMessage('DHFI_TEMPLATE_REDIRECT_MESS')?></div>
	</div>
</div>
