<?php

declare(strict_types=1);

namespace Security\DB;

/**
 * @table
 */
class Role extends \StORM\Entity
{
	/**
	 * @column
	 */
	public string $name;
}
