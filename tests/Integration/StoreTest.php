<?php

namespace Citrus\DHFi\Tests\Integration;

use Citrus\DHFi\Store;

class StoreTest extends TestCase
{
	public function testStoreGet(): void
	{
		$storeApi = new Store($this->dhfi);
		$result = $storeApi->get($_ENV['TEST_SERVER_SHOP']);
		$this->assertEquals($_ENV['TEST_SERVER_SHOP'], $result->id);
	}
}