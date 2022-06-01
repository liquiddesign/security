<?php

declare(strict_types=1);

namespace Security\DB;

use Carbon\Carbon;
use Security\Authenticator;

/**
 * @table
 */
class Account extends \StORM\Entity
{
	/**
	 * @column{"unique":true,"length":180}
	 */
	public string $login;

	/**
	 * Jméno účtu
	 * @column
	 */
	public ?string $fullname;
	
	/**
	 * @column
	 */
	public string $password;
	
	/**
	 * @column{"type":"datetime"}
	 */
	public ?string $activeFrom;
	
	/**
	 * @column{"type":"datetime"}
	 */
	public ?string $activeTo;
	
	/**
	 * @column
	 */
	public bool $active = true;
	
	/**
	 * @column
	 */
	public bool $authorized = true;
	
	/**
	 * @column{"type":"timestamp","default":"CURRENT_TIMESTAMP"}
	 */
	public ?string $tsRegistered;
	
	/**
	 * @column{"type":"timestamp"}
	 */
	public ?string $tsLastLogin;
	
	/**
	 * Token pro registraci a obnovu hesla
	 * @column
	 */
	public ?string $confirmationToken;

	/**
	 * Preferovaná mutace
	 * @column
	 */
	public ?string $preferredMutation;
	
	public function validateAuthentication(string $password, bool $skipPasswordCheck = false): void
	{
		/** @var \Security\DB\AccountRepository $repository */
		$repository = $this->getRepository();
		
		if (!$repository->getPasswords()->verify($password, $this->password) && !$skipPasswordCheck) {
			throw new \Nette\Security\AuthenticationException('Password not match', Authenticator::INVALID_CREDENTIAL);
		}
		
		if (!$this->isActive()) {
			throw new \Nette\Security\AuthenticationException('Account is not active', Authenticator::NOT_ACTIVE);
		}
		
		if (!$this->authorized) {
			throw new \Nette\Security\AuthenticationException('Account is not authorized', Authenticator::UNAUTHORIZED);
		}
		
		return;
	}
	
	public function isActive(): bool
	{
		return $this->active && (($this->activeFrom === null || Carbon::parse($this->activeFrom)->isPast()) && ($this->activeTo === null || Carbon::parse($this->activeTo)->isFuture()));
	}
	
	public function changePassword(string $newPassword): void
	{
		/** @var \Security\DB\AccountRepository $repository */
		$repository = $this->getRepository();
		
		$this->update(['password' => $repository->getPasswords()->hash($newPassword)]);
	}
	
	public function checkPassword(string $password): bool
	{
		/** @var \Security\DB\AccountRepository $repository */
		$repository = $this->getRepository();
		
		return $repository->getPasswords()->verify($password, $this->password);
	}

	public function getPreferredMutation(): ?string
	{
		return $this->preferredMutation;
	}
}
