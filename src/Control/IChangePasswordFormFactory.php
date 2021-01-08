<?php

declare(strict_types=1);

namespace Security\Control;

interface IChangePasswordFormFactory
{
	public function create(): ChangePasswordForm;
}
