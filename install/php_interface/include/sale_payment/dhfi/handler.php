<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;

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
        return $this->showTemplate($payment, 'template');
	}

    /**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return ['CSPR'];
	}
}
