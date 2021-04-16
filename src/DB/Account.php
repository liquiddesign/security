<?php

declare(strict_types=1);

namespace Security\DB;

use Security\Authenticator;

/**
 * @table
 */
class Account extends \StORM\Entity
{
	/**
	 * @column{"unique":true}
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
	
	public function validateAuthentication(string $password, bool $skipPasswordCheck = false): void
	{
		if (!Authenticator::passwordVerify($password, $this->password) && !$skipPasswordCheck) {
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
		return $this->active && ($this->activeFrom === null || \strtotime($this->activeFrom) <= \time()) && ($this->activeTo === null || \strtotime($this->activeTo) >= \time());
	}
	
	public function changePassword(string $newPassword): void
	{
		$this->update(['password' => Authenticator::setCredentialTreatment($newPassword)]);
	}
	
	public function checkPassword(string $password): bool
	{
		return Authenticator::passwordVerify($password, $this->password);
	}
}
