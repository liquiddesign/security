<?php

declare(strict_types=1);

namespace Security\Bridges;

use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Security\Authenticator;
use Security\DB\AccountContactInfoRepository;
use Security\DB\AccountRepository;
use Security\DB\PermissionRepository;
use Security\DB\RoleRepository;

class SecurityDI extends \Nette\DI\CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'superLogin' => Expect::string(),
			'serviceMode' => Expect::bool(false),
			'api' => Expect::structure([
				'tokenExpiration' => Expect::string('1 day'),
				'jwtSecret' => Expect::string(''),
			]),
		]);
	}
	
	public function loadConfiguration(): void
	{
		/** @var \stdClass $config */
		$config = $this->getConfig();

		$builder = $this->getContainerBuilder();
		
		// add repositories
		$builder->addDefinition($this->prefix('accounts'), new ServiceDefinition())->setType(AccountRepository::class);
		$builder->addDefinition($this->prefix('roles'), new ServiceDefinition())->setType(RoleRepository::class);
		$builder->addDefinition($this->prefix('permissions'), new ServiceDefinition())->setType(PermissionRepository::class);
		$builder->addDefinition(null, new ServiceDefinition())->setType(AccountContactInfoRepository::class);

		//api user storage
		if ($config->api->jwtSecret) {
			$builder->getDefinition('security.userStorage')->setAutowired(false);
			$jwtUserStorage = $builder->addDefinition($this->prefix('jwtUserStorage'), new ServiceDefinition())->setType(\Security\JwtUserStorage::class);
			$jwtUserStorage->addSetup('setTokenExpiration', [$config->api->tokenExpiration]);
			$jwtUserStorage->addSetup('setJwtSecret', [$config->api->jwtSecret]);
		}
		
		// add authenticator
		$authenticator = $builder->addDefinition('authenticator', new ServiceDefinition())->setType(Authenticator::class);
		
		if (!$config->serviceMode) {
			return;
		}
		
		$authenticator->addSetup('setSuperLogin', [$config->superLogin]);
	}
}
