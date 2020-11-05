<?php

declare(strict_types=1);

namespace App\User\Controls;

interface ILostPasswordFormFactory
{
	public function create(): LostPasswordForm;
}