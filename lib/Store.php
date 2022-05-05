<?php

namespace Citrus\DHFi;

class Store
{
	public static function get(int $id): DTO\Store
	{
		return new DTO\Store(
			Config::getClient()->request('GET', sprintf('store/%d', $id), [])
		);
	}
}