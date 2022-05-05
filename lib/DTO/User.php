<?php

namespace Citrus\DHFi\DTO;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

class User extends FlexibleDataTransferObject
{
	protected array $exceptKeys = ['email', 'role'];

	public int $id;
	public string $name;
	public string $lastName;
	public string $email;
	public string $role;
	public string $company;
	public bool $blocked;
}
