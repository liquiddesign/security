<?php

declare(strict_types=1);

namespace Security;

use Nette\Security\IAuthorizator;
use Security\DB\PermissionRepository;

class Authorizator implements IAuthorizator
{
	private ?string $superRole;
	
	private PermissionRepository $permissionRepo;
	
	public function __construct(PermissionRepository $permissionRepo)
	{
		$this->permissionRepo = $permissionRepo;
	}
	
	public function isAllowed($role, $resource, $privilege): bool
	{
		if ($role === $this->superRole) {
			return true;
		}
		
		return $this->permissionRepo->isAllowed($role, $resource, $privilege === null ? null : \intval($privilege));
	}
	
	public function setSuperRole(?string $role): void
	{
		$this->superRole = $role;
	}
}
