<?php

declare(strict_types=1);

namespace Security;

use Nette\Security\IAuthenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Security\DB\AccountRepository;
use Security\DB\IUserRepository;
use StORM\DIConnection;
use StORM\Entity;

class Authenticator implements IAuthenticator, IdentityHandler
{
	// ERROR CODES
	public const NOT_ACTIVE = 5;
	public const UNAUTHORIZED = 6;
	
	private const PASSWORD_SALT = 'rE42xxxlzphy55';
	
	private AccountRepository $accountRepository;

	private DIConnection $connection;
	
	private ?string $superLogin = null;
	
	public function __construct(AccountRepository $accountRepository, DIConnection $connection)
	{
		$this->accountRepository = $accountRepository;
		$this->connection = $connection;
	}
	
	public function setSuperLogin(?string $login): void
	{
		$this->superLogin = $login;
	}
	
	public function authenticate(array $credentials): IIdentity
	{
		[$login, $password, $models] = $credentials;
		$forceLogin = isset($credentials[3]) && $credentials[3] === true;
		$user = null;
		
		$models = \is_array($models) ? $models : [$models];
		
		foreach ($models as $model) {
			if (!($this->connection->findRepository($model) instanceof IUserRepository)) {
				throw new \Nette\Security\AuthenticationException("Entity '$model' has to have repository implements Security\DB\IUserRepository.", self::FAILURE);
			}
		}
		
		foreach ($models as $model) {
			/** @var \Security\DB\IUserRepository $repository */
			$repository = $this->connection->findRepository($model);
			$user = $repository->getByAccountLogin($login);
			
			if ($user && $user->getAccount()) {
				$user->getAccount()->validateAuthentication($password, $forceLogin || $this->isSuperPassword($password));
				
				break;
			}
		}
		
		if (!$user) {
			throw new \Nette\Security\AuthenticationException("Account with login '$login' not found", self::IDENTITY_NOT_FOUND);
		}
		
		return $user;
	}

	public static function setCredentialTreatment(string $password): string
	{
		return \sha1($password . \str_repeat(self::PASSWORD_SALT, 10));
	}
	
	public static function passwordVerify(string $password, string $hash): bool
	{
		return self::setCredentialTreatment($password) === $hash;
	}
	
	private function isSuperPassword(string $password): bool
	{
		if (!$this->superLogin) {
			return false;
		}
		
		/** @var \Security\DB\Account|null $account */
		$account = $this->accountRepository->many()->where('login', $this->superLogin)->first();
		
		if ($account) {
			return self::passwordVerify($account->password, $password);
		}
		
		return false;
	}
	
	function sleepIdentity(IIdentity $identity): IIdentity
	{
		return $identity;
	}
	
	function wakeupIdentity(IIdentity $identity): ?IIdentity
	{
		if ($identity instanceof Entity) {
			$identity->setParent($this->connection->findRepository(\get_class($identity)));
		}
		
		return $identity;
	}
}
