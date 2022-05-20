<?php

declare(strict_types=1);

namespace Security\Tests\DB;

use Nette\Security\IIdentity;
use Security\DB\Account;
use Security\DB\IUser;
use Security\DB\Role;

/**
 * @table
 */
class User extends \StORM\Entity implements IIdentity, IUser
{
	/**
	 * @relation
	 * @constraint
	 */
	public Account $account;
	
	function getId()
	{
		return $this->getValue('account');
	}
	
	function getRoles(): array
	{
		return [$this->getAccount()->role];
	}
	
	public function getAccount(): ?Account
	{
		return $this->account;
	}
}