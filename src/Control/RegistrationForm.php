<?php

declare(strict_types=1);

namespace Security\Control;

use Messages\DB\TemplateRepository;
use Nette;
use Security\Authenticator;
use Security\DB\AccountRepository;
use StORM\Connection;

/**
 * @method onRegister(\Security\Control\RegistrationForm $form, \Security\DB\Account $account)
 */
class RegistrationForm extends \Nette\Application\UI\Form
{
	public const UNIQUE_LOGIN = '\Security\Control\RegistrationForm::validateLogin';
	
	/**
	 * @var callable[]&callable(\Security\Control\\Security\Control\RegistrationForm , \Security\DB\Account): void; Occurs after registration
	 */
	public $onRegister;
	
	protected Nette\Localization\ITranslator $translator;
	
	protected bool $confirmation = false;
	
	protected string $confirmationEmail = '';
	
	protected bool $emailAuthorization = true;
	
	protected AccountRepository $accountRepository;
	
	protected TemplateRepository $templateRepository;
	
	protected Nette\Mail\Mailer $mailer;
	
	public function __construct(AccountRepository $accountRepository, Nette\Localization\ITranslator $translator, TemplateRepository $templateRepository, Nette\Mail\Mailer $mailSender)
	{
		parent::__construct();
		
		$this->translator = $translator;
		$this->templateRepository = $templateRepository;
		$this->mailer = $mailSender;
		$this->accountRepository = $accountRepository;
		
		$this->setTranslator($translator);
		
		$this->addText('login', 'registerForm.login')
			->addRule($this::UNIQUE_LOGIN, 'registerForm.account.alreadyExists', $accountRepository)
			->setRequired();
		
		$this->addText('email', 'registerForm.email')
			->addRule($this::EMAIL)
			->setRequired();
		
		$this->addPassword('password', 'registerForm.password')
			->setRequired();
		
		$this->addPassword('passwordCheck', 'registerForm.passwordCheck')
			->addRule($this::EQUAL, 'registerForm.passwordCheck.notEqual', $this['password'])
			->setRequired();
		
		$this->addSubmit('submit', 'registerForm.submit');
		
		$this->onSuccess[] = [$this, 'success'];
	}
	
	public function setConfirmation(bool $confirmation = true): void
	{
		$this->confirmation = $confirmation;
	}
	
	public function setConfirmationEmail(string $confirmationEmail): void
	{
		$this->confirmationEmail = $confirmationEmail;
	}
	
	public function setEmailAuthorization(bool $emailAuthorization): void
	{
		$this->emailAuthorization = $emailAuthorization;
	}
	
	public function getConfirmationEmail(): string
	{
		return $this->confirmationEmail;
	}
	
	public function isConfirmation(): bool
	{
		return $this->confirmation;
	}
	
	public function isEmailAuthorization(): bool
	{
		return $this->emailAuthorization;
	}
	
	public function success(RegistrationForm $form): void
	{
		$values = $form->getValues();
		
		$params = [
			'email' => $values->email,
		];
		$token = $this->emailAuthorization ? Nette\Utils\Random::generate(128) : '';
		
		/** @var \Security\DB\Account $account */
		$account = $this->accountRepository->createOne([
			'uuid' => Connection::generateUuid(),
			'login' => $values->login,
			'password' => Authenticator::setCredentialTreatment($values->password),
			'active' => !$this->confirmation,
			'authorized' => !$this->emailAuthorization,
			'confirmationToken' => $token,
		]);
		
		$mail = $this->emailAuthorization ? $this->templateRepository->createMessage('register.confirmation', $params + ['link' => $this->getPresenter()->link('//confirmUserEmail!', $token)], $values->email) : $this->templateRepository->createMessage('register.success', $params, $values->email);
		$this->mailer->send($mail);
		
		if ($this->confirmation && isset($this->confirmationEmail)) {
			$mail = $this->templateRepository->createMessage('register.adminInfo', $params, $this->confirmationEmail);
			$this->mailer->send($mail);
		}
		
		$this->onRegister($this, $account);
	}
	
	public static function validateLogin(\Nette\Forms\IControl $control, AccountRepository $accountRepository): bool
	{
		return !$accountRepository->one(['login' => $control->getValue()]);
	}
}
