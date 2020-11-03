<?php

declare(strict_types=1);

namespace Security;

use Nette\Security\IAuthenticator;
use Nette\Security\IAuthorizator;
use Nette\Security\IIdentity;
use Security\DB\AccountRepository;
use Security\DB\IUserRepository;
use StORM\DIConnection;

class Authorizator implements IAuthorizator
{
	public function isAllowed($role, $resource, $privilege): bool
	{
		dump($role);
		dump($resource);
		dump($privilege);
		
		return true;
	}
}