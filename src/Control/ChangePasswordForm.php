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
	use SecurityFormTrait;
	public const OLD_PASSWORD_VALIDATOR = '\Security\Control\ChangePasswordForm::validateOldPassword';

	/**
	 * @var callable[]&callable(\Security\Control\ChangePasswordForm): void; Occurs after change
	 */
	public $onChange;
	
	protected DIConnection $connection;
	
	protected Nette\Security\User $user;
	
	protected string $class;
	
	protected Repository $repository;
	
	public function __construct(string $class, DIConnection $connection, Nette\Security\User $user, Nette\Localization\ITranslator $translator)
	{
		parent::__construct();

		$this->connection = $connection;
		
		if (!$user->getIdentity()) {
			throw new \InvalidArgumentException('Damaged user identity!');
		}

		$this->user = $user;
		
		if (!\is_subclass_of($class, IUser::class) || !\is_subclass_of($class, Nette\Security\IIdentity::class)) {
			throw new \InvalidArgumentException("Wrong or empty class: $class");
		}

		$this->class = $class;
		
		$this->repository = $this->connection->findRepository($this->class);
		
		$this->user->getIdentity()->setParent($this->repository);
		
		$this->setTranslator($translator);
		$this->addPassword('oldPassword')
			->addRule(self::OLD_PASSWORD_VALIDATOR, 'changePasswordForm.oldPasswordCheck.notEqual', $user)
			->setRequired();
		$this->addPassword('password')
			->setRequired();
		$this->addPassword('passwordCheck')
			->addRule($this::EQUAL, 'changePasswordForm.passwordCheck.notEqual', $this['password'])
			->setRequired();
		
		
		$this->onSuccess[] = [$this, 'success'];
	}
	
	protected function beforeRender()
	{
		$this->addSubmit('submit');
	}
	
	public function success(): void
	{
		$values = $this->getValues();
		
		/** @var \Security\DB\Account $account */
		$account = $this->user->getIdentity()->getAccount();
		
		$account->changePassword($values->password);
		
		$this->onChange($this);
	}

	public static function validateOldPassword(\Nette\Forms\IControl $control, Nette\Security\User $user): bool
	{
		/** @var \Security\DB\Account $account */
		$account = $user->getIdentity()->getAccount();

		return $account->checkPassword($control->getValue());
	}
}
