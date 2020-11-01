<?php

declare(strict_types=1);

namespace Security\DB;

/**
 * @table
 */
class Permission extends \StORM\Entity
{
	/**
	 * @column
	 */
	public string $resource;
	
	/**
	 * @column
	 */
	public string $privilege;
}
