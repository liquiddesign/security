<?php

declare(strict_types=1);

namespace Security\DB;

/**
 * @table
 * @index{"name":"permissions","unique":true,"columns":["resource","privilege","fk_role"]}
 */
class Permission extends \StORM\Entity
{
	/**
	 * @column{"length":180}
	 */
	public string $resource;
	
	/**
	 * @column
	 */
	public ?int $privilege;
	
	/**
	 * @constraint{"onUpdate":"CASCADE","onDelete":"CASCADE"}
	 * @relation
	 */
	public ?Role $role;
}
