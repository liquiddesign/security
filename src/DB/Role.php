<?php

declare(strict_types=1);

namespace Security\DB;

use StORM\RelationCollection;

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
