<?php

namespace Citrus\DHFi\Tests\Integration;

use PHPUnit\Framework\TestCase;

use Citrus\DHFi\Store;

class StoreTest extends TestCase
{
	public function testStoreGet(): void
	{
		$result = Store::get($_ENV['TEST_SERVER_SHOP']);
		$this->assertEquals($_ENV['TEST_SERVER_SHOP'], $result->id);
	}
}