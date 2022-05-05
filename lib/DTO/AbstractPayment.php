<?php

namespace Citrus\DHFi\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class AbstractPayment extends DataTransferObject
{
	/** Number of tokens needed to close the payment */
	public string $amount;
	/** Payment status Not_paid when creating, Particularly_paid if not paid in full (maybe in theory), Paid - paid in full */
	public string $status = 'Not_paid';
	/** Payment comment */
	public string $comment = 'Tips';
	/** Button template number associated with the payment (if the payment has a button) */
	public int $type = 1;
	/** The text of the button associated with the payment (if the payment has a button) */
	public string $text = 'Pay';
}
