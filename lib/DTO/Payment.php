<?php

namespace Citrus\DHFi\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Payment extends FlexibleDataTransferObject
{
	public ?int $id;

	/** The store to which the payment belongs, you can specify the id or the object itself */
	public ?Store $store;
	/**
	 * We're forced to treat amount as a string in certain cases:
	 *
	 * - payment confirmation hook receives amount as a string {@see \Citrus\DHFi\Integration\Invoice::confirmPaymentAction Invoice::confirmPaymentAction}.
	 * - api responses also specify amount as a string. {@see \DHF\Pay\Payments::getOne()}
	 *
	 * @var float|string|int $amount Number of tokens needed to close the payment
	 */
	public $amount;
	/** Payment status Not_paid when creating, Particularly_paid if not paid in full (maybe in theory), Paid - paid in full */
	public string $status = 'Not_paid';
	/** Payment comment */
	public string $comment = 'Tips';
	/** Button template number associated with the payment (if the payment has a button) */
	public int $type = 1;
	/** The text of the button associated with the payment (if the payment has a button) */
	public string $text = 'Pay';
}
