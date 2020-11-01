<?php

declare(strict_types=1);

namespace Security\DB;

/**
 * @table
 */
class Group extends \StORM\Entity
{
	/**
	 * @column
	 */
	public string $name;
}
