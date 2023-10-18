<?php

declare(strict_types=1);

namespace Security\DB;

use Base\ShopsConfig;
use Nette\Security\Passwords;
use StORM\DIConnection;
use StORM\SchemaManager;

/**
 * Class AccountRepository
 * @template T of \Security\DB\Account
 * @extends \StORM\Repository<T>
 */
class AccountRepository extends \StORM\Repository
{
	public function __construct(DIConnection $connection, SchemaManager $schemaManager, protected readonly Passwords $passwords, protected readonly ShopsConfig $shopsConfig)
	{
		parent::__construct($connection, $schemaManager);
	}
	
	public function getPasswords(): Passwords
	{
		return $this->passwords;
	}

	public function findByLogin(string $login): ?Account
	{
		$query = $this->many()->where('this.login', $login);

		$this->shopsConfig->filterShopsInShopEntityCollection($query);

		return $query->first();
	}
}
