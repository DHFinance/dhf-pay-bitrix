<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!empty($params['error'])) {
	echo Loc::getMessage('DHFI_TEMPLATE_ERROR', ['#DETAILS#' => $params['error']]);
	return;
}

?>
<div class="mb-4" >
	<div class="d-flex align-items-center mb-3">
		<div class="col-auto pl-0">
			<a class="btn btn-lg btn-success" href="<?= $params['url'] ?>"><?= Loc::getMessage('DHFI_TEMPLATE_BUTTON_PAID') ?></a>
		</div>
		<div class="col pr-0"><?=Loc::getMessage('DHFI_TEMPLATE_REDIRECT_MESS')?></div>
	</div>
</div>
