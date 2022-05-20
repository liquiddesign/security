<?php

declare(strict_types=1);

namespace Security;

use Nette\Security\Passwords;

class LegacyPasswords extends Passwords
{
	private string $salt;
	
	private int $power;
	
	private string $function;
	
	public function __construct(string $function, string $salt, int $power = 10)
	{
		$this->salt = $salt;
		$this->power = $power;
		$this->function = $function;
	}
	
	/**
	 * Computes password´s hash. The result contains the algorithm ID and its settings, cryptographical salt and the hash itself.
	 */
	public function hash(string $password): string
	{
		$function = $this->function;
		
		if (!\is_callable($function)) {
			throw new \DomainException('Function is not callable');
		}
		
		return $function($password . \str_repeat($this->salt, $this->power));
	}
	
	/**
	 * Finds out, whether the given password matches the given hash.
	 */
	public function verify(string $password, string $hash): bool
	{
		return $this->hash($password) === $hash;
	}
	
	/**
	 * Finds out if the hash matches the options given in constructor.
	 */
	public function needsRehash(string $hash): bool
	{
		return !(bool) \preg_match('/^[0-9a-f]{40}$/i', $hash);
	}
}
