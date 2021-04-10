<?php

declare(strict_types=1);

namespace Security\DB;

/**
 * Trait AdministratorRepository
 * @mixin \StORM\Repository
 */
trait UserRepositoryTrait
{
	public function getByAccountLogin(string $login): ?IUser
	{
		return $this->many()->where('accounts.login', $login)->select(['role' => 'accounts.fk_role'])->first();
	}
	
	public function getByEmail(string $email): ?IUser
	{
		return $this->one(['email' => $email]);
	}
}
