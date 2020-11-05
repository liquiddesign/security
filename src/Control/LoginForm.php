<?php

declare(strict_types=1);

namespace Security\Control;

use Nette;
use Security\DB\Account;
use Security\DB\IUser;

/**
 * @method onLogin(\Security\Control\LoginForm $form)
 * @method onLoginFail(\Security\Control\LoginForm $form, int $errorCode)
 */
class LoginForm extends \Nette\Application\UI\Form
{
	/**
	 * @var callable[]&callable(\Security\Control\LoginForm): void; Occurs after login
	 */
	public $onLogin;
	
	/**
	 * @var callable[]&callable(\Security\Control\LoginForm): void; Occurs after login fail
	 */
	public $onLoginFail;
	
	private Nette\Security\User $user;
	
	private string $class;
	
	public function __construct(Nette\Security\User $user, Nette\Localization\ITranslator $translator, string $class)
	{
		parent::__construct();
		
		$this->setTranslator($translator);
		$this->addText('login', 'loginForm.login')->setRequired(true);
		$this->addPassword('password', 'loginForm.password')->setRequired(true);
		$this->addSubmit('submit', 'loginForm.submit');
		$this->onSuccess[] = [$this, 'submit'];
		
		$this->user = $user;
		
		if (!isset($class) || !is_subclass_of($class,IUser::class) || !is_subclass_of($class,Nette\Security\IIdentity::class)) {
			throw new \DomainException("Wrong or empty class: $class");
		}
		
		$this->class = $class;
	}
	
	protected function submit(): void
	{
		try {
			$values = $this->getValues();
			$this->user->login($values->login, $values->password, $this->class);
			$this->onLogin($this);
		} catch (Nette\Security\AuthenticationException $exception) {
			$this->onLoginFail($this, $exception->getCode());
		}
	}
	
}