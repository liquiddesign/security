<?php

declare(strict_types=1);

namespace Security\DB;

/**
 * @table
 */
class AccessLog extends \StORM\Entity
{
	/**
	 * @column
	 */
	public string $login;
	
	/**
	 * @column
	 */
	public string $userAgent;
	
	/**
	 * @column
	 */
	public string $ipAddress;
	
	/**
	 * @column
	 */
	public string $action;
	
	/**
	 * @column
	 */
	public int $processed = 1;
	
	/**
	 * @column{"type":"timestamp","default":"CURRENT_TIMESTAMP"}
	 */
	public int $tsCreated;
}
