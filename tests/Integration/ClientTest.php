<?php

namespace Citrus\DHFi\Tests\Integration;

use DHF\Pay\DHFPay;
use DHF\Pay\Exception\DHFUnauthorisedException;

class ClientTest extends TestCase
{
	public function testClient(): void
	{
		$result = $this->dhfi->payments()->getAll();
		$this->assertIsArray($result);
	}

	/**
	 * @test
	 */
	public function itFailsWithWrongToken(): void
	{
		$this->expectException(DHFUnauthorisedException::class);

		$dhfi = new DHFPay($_ENV['TEST_SERVER_API'], 'wrong_token');
		$dhfi->payments()->add([]);
	}

}