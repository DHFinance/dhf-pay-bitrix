<?php

namespace Citrus\DHFi\Tests\Integration;

use DHF\Pay\DHFPay;

class TestCase extends \PHPUnit\Framework\TestCase
{
	protected DHFPay $dhfi;

	protected function setUp(): void
	{
		$this->dhfi = new DHFPay($_ENV['TEST_SERVER_API'], $_ENV['TEST_SERVER_TOKEN']);
	}
}
