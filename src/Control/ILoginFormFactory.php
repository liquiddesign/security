<?php

declare(strict_types=1);

namespace Security\Control;

interface ILoginFormFactory
{
	public function create(array $classes): LoginForm;
}
