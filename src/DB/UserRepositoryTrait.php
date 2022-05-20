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
		/** @var \Security\DB\IUser $user */
		$user = $this->many()->where('accounts.login', $login)->first();
		
		if (!$user) {
			return null;
		}
		
		/** @var \Security\DB\Account|null $account */
		$account = $this->getConnection()->findRepository(Account::class)->one(['login' => $login], false);
		
		if (!$account) {
			return null;
		}
		
		$user->setAccount($account);
		
		return $user;
	}
	
	public function getByEmail(string $email): ?IUser
	{
		return $this->one(['email' => $email]);
	}
	
	/**
	 * @param array<mixed> $userData
	 * @param array<mixed> $accountData
	 */
	public function register(array $userData, array $accountData): IUser
	{
		$this->getConnection()->getLink()->beginTransaction();
		
		$account = $this->getConnection()->findRepository(Account::class)->createOne($accountData);
		
		$return = $this->createOne(
			$userData + ['accounts' => [$account->getPK()]],
		);
		
		$return->setAccount($account);
		
		$this->getConnection()->getLink()->commit();
		
		return $return;
	}
}
