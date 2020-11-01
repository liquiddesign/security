<?php

declare(strict_types=1);

namespace Security\DB;

interface IUserRepository
{
	public function getByAccountLogin(string $login): ?IUser;
}
