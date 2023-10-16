<?php

declare(strict_types=1);

namespace Security;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Security\IIdentity;
use Nette\Security\UserStorage;
use Security\DB\AccountRepository;
use Security\DB\IUser;
use StORM\DIConnection;

class JwtUserStorage implements UserStorage
{
	private const COOKIE_NAME = 'jwt_token';
	
	private int $tokenExpiration;
	
	private string $jwtSecret = '';

	/**
	 * @var \Security\DB\AccountRepository<\Security\DB\Account>
	 */
	private AccountRepository $accountRepository;
	
	private DIConnection $connection;
	
	private IResponse $response;
	
	private IRequest $request;

	/**
	 * @param \Security\DB\AccountRepository<\Security\DB\Account> $accountRepository
	 * @param \StORM\DIConnection $connection
	 * @param \Nette\Http\IResponse $response
	 * @param \Nette\Http\IRequest $request
	 */
	public function __construct(AccountRepository $accountRepository, DIConnection $connection, IResponse $response, IRequest $request)
	{
		$this->accountRepository = $accountRepository;
		$this->connection = $connection;
		$this->response = $response;
		$this->request = $request;
	}
	
	public function saveAuthentication(IIdentity $identity): void
	{
		if (!$identity instanceof IUser) {
			throw new \InvalidArgumentException('Object $identity does not implement IUser');
		}
		
		if ($identity->getAccount() === null) {
			throw new \InvalidArgumentException('Object $identity does not have set account');
		}
		
		if (!$this->jwtSecret) {
			throw new \DomainException('Property $jwtSecret cannot be empty');
		}
		
		$userData = [
			'account' => $identity->getAccount()->getPK(),
			'identityType' => \get_class($identity),
			'expiration' => $this->tokenExpiration,
		];
		$jwt = JWT::encode($userData, $this->jwtSecret);
		
		$this->response->setCookie(self::COOKIE_NAME, $jwt, $this->tokenExpiration, '/', null, true);
	}
	
	public function clearAuthentication(bool $clearIdentity): void
	{
		unset($clearIdentity);
		$this->response->deleteCookie(self::COOKIE_NAME, '/', null);
	}
	
	/**
	 * @return array{bool, ?\Nette\Security\IIdentity, ?int}
	 * @throws \StORM\Exception\NotFoundException
	 */
	public function getState(): array
	{
		$token = $this->request->getCookie(self::COOKIE_NAME) ?? null;
		
		if (!$token) {
			return [false, null, null];
		}
		
		if (!$this->jwtSecret) {
			throw new \DomainException('Property $jwtSecret cannot be empty');
		}
		
		/** @var \stdClass $decode */
		$decode = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
		
		if ($decode->expiration <= \time()) {
			$this->response->deleteCookie(self::COOKIE_NAME, '/', null);
			
			return [false, null, 2];
		}
		
		/** @var class-string<\StORM\Entity> $class */
		$class = $decode->identityType;
		
		/** @var \Security\DB\IUser $identity */
		$identity = $this->connection->findRepository($class)->many()->where('accounts.uuid', $decode->account)->first(true);
		
		/** @var \Security\DB\Account $account */
		$account = $this->accountRepository->one($decode->account, true);
		$identity->setAccount($account);
		
		return [true, $identity, null];
	}
	
	public function setExpiration(?string $expire, bool $clearIdentity): void
	{
		unset($expire);
		unset($clearIdentity);
	}
	
	/**
	 * @param string $expire
	 * @internal
	 */
	public function setTokenExpiration(string $expire): void
	{
		$this->tokenExpiration = Carbon::parse($expire)->getTimestamp();
	}
	
	public function setJwtSecret(string $secret): void
	{
		$this->jwtSecret = $secret;
	}
}
