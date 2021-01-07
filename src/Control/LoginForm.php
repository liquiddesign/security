<?php

declare(strict_types=1);

namespace Security\Control;

use Nette;
use Security\DB\IUser;

/**
 * @method onLogin(\Security\Control\LoginForm $form)
 * @method onLoginFail(\Security\Control\LoginForm $form, int $errorCode)
 */
class LoginForm extends \Nette\Application\UI\Form
{
	use SecurityFormTrait;
	
	/**
	 * @var callable[]&callable(\Security\Control\LoginForm): void; Occurs after login
	 */
	public $onLogin;
	
	/**
	 * @var callable[]&callable(\Security\Control\LoginForm): void; Occurs after login fail
	 */
	public $onLoginFail;
	
	protected Nette\Security\User $user;
	
	protected array $classes;
	
	public function __construct(array $classes, Nette\Security\User $user, Nette\Localization\ITranslator $translator)
	{
		parent::__construct();
		
		$this->user = $user;
		
		foreach ($classes as $class) {
			if (!\is_subclass_of($class, IUser::class) || !\is_subclass_of($class, Nette\Security\IIdentity::class)) {
				throw new \InvalidArgumentException("Wrong or empty class: $class");
			}
		}
		
		$this->classes = $classes;
		
		$this->setTranslator($translator);
		$this->addText('login', 'loginForm.login')->setRequired(true);
		$this->addPassword('password', 'loginForm.password')->setRequired(true);
		
		$this->addSubmit('submit', 'loginForm.submit');
		
		$this->onSuccess[] = [$this, 'submit'];
	}
	
	protected function submit(): void
	{
		try {
			$values = $this->getValues();
			$this->user->login($values->login, $values->password, $this->classes);
			$this->onLogin($this);
		} catch (Nette\Security\AuthenticationException $exception) {
			$this->onLoginFail($this, $exception->getCode());
		}
	}
}
