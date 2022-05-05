<?php

namespace Citrus\DHFi\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Store extends FlexibleDataTransferObject
{
	protected array $exceptKeys = ['apiKey', 'user', 'url', 'wallet'];

	public int $id;
	/** The address to which a post request with payment details will be sent when its status changes */
	public ?string $url;
	/** Name of shop */
	public ?string $name;
	/** Shop wallet. All payments created on behalf of this store will have this wallet */
	public ?string $wallet;
	/** Store Description */
	public ?string $description;
	/** Unique shop key. It is used to issue payment and transaction associated with this store */
	public ?string $apiKey;
	/** Store lock status */
	public ?bool $blocked;

	/** The user who owns the store */
	public ?User $user;
}
