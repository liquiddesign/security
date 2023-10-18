<?php

declare(strict_types=1);

namespace Security;

use Carbon\Carbon;
use Nette\Application\ApplicationException;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Security\DB\Account;
use Security\DB\AccountRepository;
use Security\DB\IUser;
use Security\DB\IUserRepository;
use StORM\DIConnection;
use StORM\Entity;

class Authenticator implements \Nette\Security\Authenticator, IdentityHandler
{
	// ERROR CODES
	public const NOT_ACTIVE = 5;
	public const UNAUTHORIZED = 6;

	/**
	 * @var \Security\DB\AccountRepository<\Security\DB\Account>
	 */
	private AccountRepository $accountRepository;

	private ?string $superLogin = null;

	/**
	 * @param \Security\DB\AccountRepository<\Security\DB\Account> $accountRepository
	 * @param \Nette\Security\Passwords $passwords
	 * @param \StORM\DIConnection $connection
	 */
	public function __construct(AccountRepository $accountRepository, private readonly Passwords $passwords, private readonly DIConnection $connection)
	{
		$this->accountRepository = $accountRepository;
	}
	
	public function setSuperLogin(?string $login): void
	{
		$this->superLogin = $login;
	}

	public function authenticate(string $user, string $password): IIdentity
	{
		$entities = \func_get_arg(2);
		
		if (!$entities) {
			throw new \InvalidArgumentException('Entities must not be empty, login and password must be strings');
		}
		
		/** @var array<class-string<\StORM\Entity>> $entities */
		$entities = \is_array($entities) ? $entities : [$entities];
		
		foreach ($entities as $model) {
			if (!($this->connection->findRepository($model) instanceof IUserRepository)) {
				throw new \Nette\Security\AuthenticationException("Entity '$model' has to have repository implements Security\DB\IUserRepository.", self::FAILURE);
			}
		}
		
		$identity = null;
		
		foreach ($entities as $model) {
			/** @var \Security\DB\IUserRepository $repository */
			$repository = $this->connection->findRepository($model);
			$identity = $repository->getByAccountLogin($user);
			$account = $this->accountRepository->findByLogin($user);

			if ($identity && $account) {
				$identity->setAccount($account);
				
				if (!$identity->getAccount() instanceof Account) {
					throw new ApplicationException('Set account failed');
				}

				$account->validateAuthentication($password, $this->isSuperPassword($password));

				$account->update(['tsLastLogin' => Carbon::now()->toDateTimeString(), 'tsLastActivity' => Carbon::now()->toDateTimeString(),]);
				
				break;
			}
		}
		
		if (!$identity) {
			throw new \Nette\Security\AuthenticationException("Account with login '$user' not found", self::IDENTITY_NOT_FOUND);
		}
		
		return $identity;
	}

	public function sleepIdentity(IIdentity $identity): IIdentity
	{
		return $identity;
	}

	public function wakeupIdentity(IIdentity $identity): ?IIdentity
	{
		if ($identity instanceof Entity) {
			$identity->setParent($this->connection->findRepository($identity::class));
		}
		
		if ($identity instanceof IUser && $identity->getAccount() !== null) {
			$identity->getAccount()->setParent($this->accountRepository);
		}
		
		return $identity;
	}
	
	private function isSuperPassword(string $password): bool
	{
		$hash = null;
		
		if ($this->superLogin) {
			$hash = $this->accountRepository->many()->where('login', $this->superLogin)->firstValue('password');
		}
		
		if (\is_string($hash)) {
			return $this->passwords->verify($password, $hash);
		}
		
		return false;
	}
}
