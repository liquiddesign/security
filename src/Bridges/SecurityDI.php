<?php

declare(strict_types=1);

namespace Security\Bridges;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Security\Authenticator;
use Security\Authorizator;
use Security\Control\IChangePasswordFormFactory;
use Security\Control\ILoginFormFactory;
use Security\Control\ILostPasswordFormFactory;
use Security\Control\IRegistrationFormFactory;
use Security\DB\AccountRepository;
use Security\DB\PermissionRepository;
use Security\DB\RoleRepository;

class SecurityDI extends \Nette\DI\CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'superLogin' => Expect::string(null),
			'superRole' => Expect::string(null),
			'registration' => Expect::structure([
				'enabled' =>  Expect::bool(true),
				'default' => Expect::structure([
					'type' => Expect::string('retail'),
				]),
				'confirmation' => Expect::bool(false),
				'confirmationEmail' => Expect::string(),
				'emailAuthorization' => Expect::bool(true),
			]),
		]);
	}
	
	public function loadConfiguration(): void
	{
		$config = (array)$this->getConfig();
		
		/** @var \Nette\DI\ContainerBuilder $builder */
		$builder = $this->getContainerBuilder();
		
		// add controls
		$builder->addFactoryDefinition($this->prefix('loginFormFactory'))->setImplement(ILoginFormFactory::class);
		$builder->addFactoryDefinition($this->prefix('changePasswordFormFactory'))->setImplement(IChangePasswordFormFactory::class);
		$builder->addFactoryDefinition($this->prefix('lostPasswordFormFactory'))->setImplement(ILostPasswordFormFactory::class);
		
		$registrationForm = $builder->addFactoryDefinition($this->prefix('registrationFormFactory'))->setImplement(IRegistrationFormFactory::class);
		$registrationForm->getResultDefinition()->addSetup('setRegistrationConfig',[$config['registration']]);
		
		// add repositories
		$builder->addDefinition($this->prefix('accounts'))->setType(AccountRepository::class);
		$builder->addDefinition($this->prefix('roles'))->setType(RoleRepository::class);
		$builder->addDefinition($this->prefix('permissions'))->setType(PermissionRepository::class);
		
		// add authenticator
		$authenticator = $builder->addDefinition('authenticator')->setType(Authenticator::class);
		$authenticator->addSetup('setSuperLogin', [$config['superLogin']]);
		
		// add authorizator
		$authorizator = $builder->addDefinition('authorizator')->setType(Authorizator::class);
		$authorizator->addSetup('setSuperRole', [$config['superRole']]);
		
		return;
	}
}
