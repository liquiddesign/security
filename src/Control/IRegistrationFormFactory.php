<?php

declare(strict_types=1);

namespace Security\Control;

interface IRegistrationFormFactory
{
	public function create(): RegistrationForm;
}
