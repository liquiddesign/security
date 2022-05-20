<?php

declare(strict_types=1);

namespace Security\DB;

use Nette\Security\Passwords;
use StORM\DIConnection;
use StORM\SchemaManager;

/**
 * Class AccountRepository
 * @extends \StORM\Repository<\Security\DB\Account>
 */
class AccountRepository extends \StORM\Repository
{
	private Passwords $passwords;
	
	public function __construct(DIConnection $connection, SchemaManager $schemaManager, Passwords $passwords)
	{
		parent::__construct($connection, $schemaManager);
		
		$this->passwords = $passwords;
	}
	
	public function getPasswords(): Passwords
	{
		return $this->passwords;
	}
}
