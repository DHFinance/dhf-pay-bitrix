<?php
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

namespace Citrus\DHFi\Integration\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class Invoice extends DataTransferObject
{
	/** @var string|int $number */
	public $number;
	public string $currency;

	/** @var \Citrus\DHFi\Integration\DTO\ProductRow[] */
	public array $products;
}