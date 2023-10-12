<?php

declare(strict_types=1);

namespace Security\DB;

use Base\ShopsConfig;

/**
 * Trait AdministratorRepository
 * @mixin \StORM\Repository
 */
trait UserRepositoryTrait
{
	public function getByAccountLogin(string $login): ?IUser
	{
		$accountCollection = $this->getConnection()->findRepository(Account::class)->many()->where('login', $login);

		if (isset($this->shopsConfig) && $this->shopsConfig instanceof ShopsConfig) {
			$this->shopsConfig->filterShopsInShopEntityCollection($accountCollection);
		}

		/** @var \Security\DB\Account|null $account */
		$account = $accountCollection->first();

		if (!$account) {
			return null;
		}

		/** @var \Security\DB\IUser $user */
		$user = $this->many()->where('accounts.uuid', $account->getPK())->first();

		if (!$user) {
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
