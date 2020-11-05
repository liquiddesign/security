<?php

declare(strict_types=1);

namespace Security\DB;

use Security\Authenticator;

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
	
	public function getByEmail(string $email): ?IUser
	{
		return $this->one(['email' => $email]);
	}
	
	public function changePassword(string $uuid, string $newPassword): ?IUser
	{
		/** @var \Security\DB\IUser $user */
		$user = $this->one($uuid);
		
		if (!$user)
			return null;
		
		$user->getAccount()->update(['password' => Authenticator::setCredentialTreatment($newPassword)]);
		
		return $user;
	}
	
	public function checkPassword(string $uuid, string $password): bool
	{
		/** @var \Security\DB\IUser $user */
		$user = $this->one($uuid);
		
		if (!$user)
			return false;
		
		return Authenticator::passwordVerify($password, $user->account->password);
	}
	
}
