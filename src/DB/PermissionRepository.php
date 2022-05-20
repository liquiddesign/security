<?php

declare(strict_types=1);

namespace Security\DB;

/**
 * Class RoleRepository
 * @extends \StORM\Repository<\Security\DB\Permission>
 */
class PermissionRepository extends \StORM\Repository
{
	public function isAllowed(string $role, string $resource, ?int $privilege = null): bool
	{
		return !$this->many()
			->where("'$resource' LIKE CONCAT(REPLACE(this.resource, ':*', ''),'%')")
			->where('this.privilege IS NULL OR :privilege IS NULL OR this.privilege & :privilege', ['privilege' => $privilege])
			->where('fk_role', $role)->isEmpty();
	}
}
