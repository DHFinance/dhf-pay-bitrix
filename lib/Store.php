<?php

namespace Citrus\DHFi;

use DHF\Pay\DHFPay;

class Store
{
	private DHFPay $client;

	public function __construct(DHFPay $client)
	{
		$this->client = $client;
	}

	public function get(int $id): DTO\Store
	{
		return new DTO\Store(
			$this->client->request('GET', sprintf('store/%d', $id), [])
		);
	}
}