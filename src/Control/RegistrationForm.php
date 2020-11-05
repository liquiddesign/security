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
	/**
	 * @var callable[]&callable(\Security\Control\\Security\Control\RegistrationForm , \Security\DB\Account): void; Occurs after registration
	 */
	public $onRegister;
	
	private Nette\Localization\ITranslator $translator;
	
	private \stdClass $registrationConfig;
	
	private AccountRepository $accountRepository;
	
	private TemplateRepository $templateRepository;
	
	private Nette\Mail\Mailer $mailer;
	
	private Nette\Application\LinkGenerator $linkGenerator;
	
	const UNIQUE_LOGIN = '\Security\Control\RegistrationForm::validateLogin';
	
	public function __construct(AccountRepository $accountRepository, Nette\Localization\ITranslator $translator, TemplateRepository $templateRepository, Nette\Mail\Mailer $mailSender, Nette\Application\LinkGenerator $linkGenerator)
	{
		parent::__construct();
		$this->translator = $translator;
		$this->templateRepository = $templateRepository;
		$this->mailer = $mailSender;
		$this->linkGenerator = $linkGenerator;
		$this->accountRepository = $accountRepository;
		
		// Default config
		$this->registrationConfig = new \stdClass();
		$this->registrationConfig->enabled = true;
		$this->registrationConfig->default = new \stdClass();
		$this->registrationConfig->default->type = 'retail';
		$this->registrationConfig->confirmation = false;
		$this->registrationConfig->confirmationEmail = '';
		$this->registrationConfig->emailAuthorization = true;
		
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
		
		$this->onSuccess[] = [$this, 'success'];
	}
	
	public function beforeRender()
	{
		parent::beforeRender();
		if ($this->registrationConfig->enabled) {
			$this->addSubmit('submit', 'registerForm.submit');
		}
	}
	
	public function setRegistrationConfig($registrationConfig)
	{
		$this->registrationConfig = $registrationConfig;
	}
	
	public static function validateLogin(\Nette\Forms\IControl $control, AccountRepository $accountRepository): bool
	{
		return !$accountRepository->one(['login' => $control->getValue()]);
	}
	
	public function success(RegistrationForm $form)
	{
		$values = $form->getValues();
		
		$params = [
			'email' => $values->email,
		];
		$token = $this->registrationConfig->emailAuthorization ? Nette\Utils\Random::generate(128) : '';
		
		/** @var \Security\DB\Account $account */
		$account = $this->accountRepository->createOne([
			'uuid' => Connection::generateUuid(),
			'login' => $values->login,
			'password' => Authenticator::setCredentialTreatment($values->password),
			'active' => !$this->registrationConfig->confirmation,
			'authorized' => !$this->registrationConfig->emailAuthorization,
			'emailAndPasswordConfirmationToken' => $token,
		]);
		
		$mail = $this->registrationConfig->emailAuthorization ? $this->templateRepository->createMessage('register.confirmation', $params + ['link' => $this->getPresenter()->link('//confirmUserEmail!', $token)], $values->email) : $this->templateRepository->createMessage('register.success', $params, $values->email);
		$this->mailer->send($mail);
		
		if ($this->registrationConfig->confirmation && isset($this->registrationConfig->confirmationEmail)) {
			$mail = $this->templateRepository->createMessage('register.adminInfo', $params, $this->registrationConfig->confirmationEmail);
			$this->mailer->send($mail);
		}
		
		$this->onRegister($this, $account);
	}
	
}
