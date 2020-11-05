<?php

declare(strict_types=1);

namespace Security\Control;

use Nette;
use Security\DB\IUser;
use StORM\DIConnection;
use StORM\Repository;

/**
 * @method onChange(\Security\Control\ChangePasswordForm $form)
 */
class ChangePasswordForm extends \Nette\Application\UI\Form
{
	/**
	 * @var callable[]&callable(\Security\Control\ChangePasswordForm): void; Occurs after change
	 */
	public $onChange;
	
	public const OLD_PASSWORD_VALIDATOR = '\Security\Control\ChangePasswordForm::validateOldPassword';
	
	private DIConnection $connection;
	
	private Nette\Security\User $user;
	
	private string $class;
	
	private Repository $repository;
	
	public function __construct(DIConnection $connection, Nette\Security\User $user, Nette\Localization\ITranslator $translator, string $class)
	{
		parent::__construct();
		$this->connection = $connection;
		$this->user = $user;
		
		if (!isset($class) || !is_subclass_of($class, IUser::class) || !is_subclass_of($class, Nette\Security\IIdentity::class)) {
			throw new \DomainException("Wrong or empty class: $class");
		}
		$this->class = $class;
		
		$this->repository = $this->connection->findRepository($this->class);
		
		if (!$this->repository) {
			throw new \DomainException("Repository for class \"$class\" not found!");
		}
		
		if (!$user->getIdentity()) {
			throw new \DomainException('Damaged user identity!');
		}
		
		$this->setTranslator($translator);
		$this->addPassword('oldPassword')
			->addRule(self::OLD_PASSWORD_VALIDATOR, 'changePasswordForm.oldPasswordCheck.notEqual', [$this->repository, $user])
			->setRequired();
		$this->addPassword('password')
			->setRequired();
		$this->addPassword('passwordCheck')
			->addRule($this::EQUAL, 'changePasswordForm.passwordCheck.notEqual', $this['password'])
			->setRequired();
		$this->addSubmit('submit');
		
		$this->onSuccess[] = [$this, 'success'];
		
	}
	
	/**
	 * @param \Nette\Forms\IControl $control
	 * @param array $args
	 * [0] CustomerRepository
	 * [1] Nette\Security\User
	 * @return mixed
	 */
	public static function validateOldPassword(\Nette\Forms\IControl $control, array $args)
	{
		return $args[0]->checkPassword($args[1]->getIdentity()->uuid, $control->getValue());
	}
	
	public function success(): void
	{
		$values = $this->getValues();
		
		$this->repository->changePassword($this->user->getIdentity()->uuid, $values->password);
		
		$this->onChange($this);
	}
	
}