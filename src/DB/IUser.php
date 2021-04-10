<?php

declare(strict_types=1);

namespace Security\DB;

use Nette\Security\IIdentity;

interface IUser extends IIdentity
{
	public function getAccount(): ?Account;
	
	public function setAccount(Account $account): void;
}
