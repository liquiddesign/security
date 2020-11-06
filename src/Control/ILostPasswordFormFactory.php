<?php

declare(strict_types=1);

namespace Security\Control;

interface ILostPasswordFormFactory
{
	public function create(string $class): LostPasswordForm;
}
