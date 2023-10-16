<?php

declare(strict_types=1);

namespace Security\DB;

use Base\Entity\ShopEntity;
use Carbon\Carbon;
use Security\Authenticator;
use StORM\RelationCollection;

/**
 * @table
 * @index{"name":"security_account_login","unique":true,"columns":["login", "fk_shop"]}
 * @method \StORM\RelationCollection<\Security\DB\AccountContactInfo> getAccountContactInfos()
 */
class Account extends ShopEntity
{
	/**
	 * @column{"length":180}
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
	public ?string $tsRegisteredEmailSent;
	
	/**
	 * @column{"type":"timestamp"}
	 */
	public ?string $tsLastLogin;
	
	/**
	 * @column{"type":"timestamp"}
	 */
	public ?string $tsLastActivity;
	
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

	/**
	 * @column
	 */
	public ?string $externalId;

	/**
	 * @column
	 */
	public ?string $externalCode;

	/**
	 * @relation
	 * @var \StORM\RelationCollection<\Security\DB\AccountContactInfo>
	 */
	public RelationCollection $accountContactInfos;
	
	public function validateAuthentication(string $password, bool $skipPasswordCheck = false): void
	{
		/** @var \Security\DB\AccountRepository<\Security\DB\Account> $repository */
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
		/** @var \Security\DB\AccountRepository<\Security\DB\Account> $repository */
		$repository = $this->getRepository();
		
		$this->update(['password' => $repository->getPasswords()->hash($newPassword)]);
	}
	
	public function checkPassword(string $password): bool
	{
		/** @var \Security\DB\AccountRepository<\Security\DB\Account> $repository */
		$repository = $this->getRepository();
		
		return $repository->getPasswords()->verify($password, $this->password);
	}

	public function getPreferredMutation(): ?string
	{
		return $this->preferredMutation;
	}
}
