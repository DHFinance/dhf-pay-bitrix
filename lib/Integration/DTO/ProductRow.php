<?php

namespace Citrus\DHFi\Integration\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class ProductRow extends DataTransferObject
{
	public string $title;
	public float $quantity;
	public float $price;
	public string $currency;
}