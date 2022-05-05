<?php

namespace Citrus\DHFi\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class CreatePaymentResponse extends FlexibleDataTransferObject
{
	/** Payment id */
	public int $id;
}