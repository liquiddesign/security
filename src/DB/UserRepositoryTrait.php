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
		return $this->many()->where('account.login', $login)->select(['role' => 'account.fk_role'])->first();
	}
}
