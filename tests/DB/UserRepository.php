<?php

declare(strict_types=1);

namespace Security\Tests\DB;

use Security\DB\IUser;
use Security\DB\IUserRepository;

/**
 * Class UserRepository
 * @extends \StORM\Repository<\Security\Tests\DB\User>
 */
class UserRepository extends \StORM\Repository implements IUserRepository
{
	public function getByAccountLogin(string $login): ?IUser
	{
		return $this->many()->where('account.login', $login)->first();
	}
}
