<?php

declare(strict_types=1);

namespace Security\Control;

use Nette\Security\User;

trait SecurityFormTrait
{
	protected User $user;
	/**
	 * For debugging use.
	 * @param \Nette\Security\User $user
	 */
	public function setUser(User $user): void
	{
		$this->user=$user;
	}
}
