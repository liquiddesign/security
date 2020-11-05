<?php

declare(strict_types=1);

namespace Security\DB;

interface IUserRepository
{
	public function getByAccountLogin(string $login): ?IUser;
	
	//public function getByEmail(string $email): ?IUser
	
	//public function changePassword(string $uuid, string $newPassword): ?IUser;
	
	//public function checkPassword(string $uuid, string $password): bool;
}
