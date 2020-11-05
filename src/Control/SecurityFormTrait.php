<?php

declare(strict_types=1);

namespace Security\Control;

use Nette\Security\User;

trait SecurityFormTrait
{
	private User $user;
	/**
	 * For debugging use.
	 * @param \Nette\Security\User $user
	 */
	public function setUser(User $user)
	{
		$this->user=$user;
	}
}