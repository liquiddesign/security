<?php

declare(strict_types=1);

namespace Security\Bridges;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Security\Authenticator;
use Security\Authorizator;
use Security\DB\AccountRepository;
use Security\DB\PermissionRepository;
use Security\DB\RoleRepository;

class SecurityDI extends \Nette\DI\CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'superLogin' => Expect::string(null),
		]);
	}
	
	public function loadConfiguration(): void
	{
		$config = (array) $this->getConfig();
		
		/** @var \Nette\DI\ContainerBuilder $builder */
		$builder = $this->getContainerBuilder();
		
		// add repositories
		$builder->addDefinition($this->prefix('accounts'))->setType(AccountRepository::class);
		$builder->addDefinition($this->prefix('roles'))->setType(RoleRepository::class);
		$builder->addDefinition($this->prefix('permissions'))->setType(PermissionRepository::class);
		
		// add authenticator
		$authenticator = $builder->addDefinition('authenticator')->setType(Authenticator::class);
		$authenticator->addSetup('setSuperLogin', [$config['superLogin']]);
		
		// add authorizator
		$builder->addDefinition('authorizator')->setType(Authorizator::class);
		
		return;
	}
}
