<?php

declare(strict_types=1);

namespace Security\Control;

interface IRegistrationFormFactory
{
	/**
	 * @param string $class
	 * @param string $shopperClass Must have minimal structure defined by documentation!
	 * @param string $addressClass Must have minimal structure defined by documentation!
	 * @return \Security\Control\RegistrationForm
	 */
	public function create(string $class, string $shopperClass, string $addressClass): RegistrationForm;
}