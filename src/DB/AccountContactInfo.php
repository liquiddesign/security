<?php

namespace Security\DB;

use StORM\Entity;

/**
 * @table
 * @index{"name":"security_account_contact_info","unique":true,"columns":["fk_account", "type", "value"]}
 */
class AccountContactInfo extends Entity
{
	/**
	 * @var 'email'|'phone'|'mobile'
	 * @column{"type":"enum","length":"'email','phone','mobile'"}
	 */
	public string $type;

	/**
	 * @column
	 */
	public string $value;

	/**
	 * @column
	 */
	public string|null $externalId;

	/**
	 * @column{"type":"datetime", "default":"CURRENT_TIMESTAMP"}
	 */
	public string $createdTs;

	/**
	 * @column{"type":"datetime"}
	 */
	public ?string $updatedTs;

	/**
	 * @relation
	 * @constraint{"onUpdate":"CASCADE","onDelete":"CASCADE"}
	 */
	public Account $account;
}
