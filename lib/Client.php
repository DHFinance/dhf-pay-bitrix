<?php

namespace Citrus\DHFi;

use DHF\Pay\DHFPay;

trait Client
{
	protected DHFPay $client;

	public function __construct(DHFPay $client)
	{
		$this->client = $client;
	}
}