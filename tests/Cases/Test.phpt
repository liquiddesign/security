<?php

namespace Security\Tests\Cases;

require_once __DIR__ . '/../../vendor/autoload.php';

use Tester\Assert;
use Tester\TestCase;

/**
 * Class Test
 * @package Tests
 */
class Test extends TestCase
{
	public function testExists(): void
	{
		$container = \Security\Tests\Bootstrap::createContainer();

    	Assert::notNull($container->getByType(\Security\DB\RoleRepository::class));
	}
}

(new Test())->run();
