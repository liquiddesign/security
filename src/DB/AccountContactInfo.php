<?php

namespace Security\DB;

use StORM\Entity;

/**
 * @table
 */
class AccountContactInfo extends Entity
{
	/**
	 * @var 'email'|'phone'
	 * @column{"type":"enum","length":"'email','phone'"}
	 */
	public string $type;

	/**
	 * @column
	 */
	public string $value;

	/**
	 * @relation
	 * @constraint{"onUpdate":"CASCADE","onDelete":"CASCADE"}
	 */
	public Account $account;
}
