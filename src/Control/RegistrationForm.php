<?php

declare(strict_types=1);

namespace Security\Control;

use Messages\DB\TemplateRepository;
use Nette;
use Security\Authenticator;
use Security\DB\AccountRepository;
use Security\DB\IUser;
use StORM\Connection;
use StORM\DIConnection;
use StORM\Repository;

/**
 * @method onRegister(\Security\Control\RegistrationForm $form, \Security\DB\IUser $customer)
 */
class RegistrationForm extends \Nette\Application\UI\Form
{
	/**
	 * @var callable[]&callable(\Security\Control\\Security\Control\RegistrationForm , \App\User\DB\Customer): void; Occurs after registration
	 */
	public $onRegister;
	
	private Nette\Localization\ITranslator $translator;
	
	private $shopper;
	
	private AccountRepository $accountRepository;
	
	private Repository $entityRepository;
	
	private Repository $addressRepository;
	
	private TemplateRepository $templateRepository;
	
	private Nette\Mail\Mailer $mailer;
	
	private Nette\Application\LinkGenerator $linkGenerator;
	
	const UNIQUE_LOGIN = '\Security\Control\RegistrationForm::validateLogin';
	
	public function __construct(string $class, string $shopperClass, string $addressClass, Nette\DI\Container $container, DIConnection $connection, Nette\Localization\ITranslator $translator, TemplateRepository $templateRepository, Nette\Mail\Mailer $mailSender, Nette\Application\LinkGenerator $linkGenerator, AccountRepository $accountRepository)
	{
		parent::__construct();
		$this->translator = $translator;
		$this->templateRepository = $templateRepository;
		$this->mailer = $mailSender;
		$this->linkGenerator = $linkGenerator;
		$this->accountRepository = $accountRepository;
		
		if (!isset($class) || !is_subclass_of($class, IUser::class) || !is_subclass_of($class, Nette\Security\IIdentity::class)) {
			throw new \InvalidArgumentException("Wrong or empty class: $class");
		}
		
		$this->entityRepository = $connection->findRepository($class);
		
		if (!$this->entityRepository) {
			throw new \InvalidArgumentException("Repository for class \"$class\" not found!");
		}
		
		$this->addressRepository = $connection->findRepository($addressClass);
		
		if (!$this->addressRepository) {
			throw new \InvalidArgumentException("Repository for class \"$class\" not found!");
		}
		
		$this->shopper = $container->getByType($shopperClass);
		
		if (!$this->shopper) {
			throw new \InvalidArgumentException('Instance of Shopper class not found!');
		}
		
		if ($this->shopper->registration->enabled == false) {
			return;
		}
		
		$this->setTranslator($translator);
		
		$this->addText('login', 'registerForm.login')
			->addRule($this::UNIQUE_LOGIN, 'registerForm.account.alreadyExists', $this->entityRepository)
			->setRequired();
		
		$this->addText('email', 'registerForm.email')
			->addRule($this::EMAIL)
			->setRequired();
		
		$this->addPassword('password', 'registerForm.password')
			->setRequired();
		
		$this->addPassword('passwordCheck', 'registerForm.passwordCheck')
			->addRule($this::EQUAL, 'registerForm.passwordCheck.notEqual', $this['password'])
			->setRequired();
		
		$billAddress = $this->addContainer('billAddress');
		
		$billAddress->addText('street', 'billAddress.street')
			->setRequired();
		
		$billAddress->addText('city', 'billAddress.city')
			->setRequired();
		
		$billAddress->addText('zipcode', 'billAddress.zipcode')
			->setRequired();
		
		$billAddress->addText('state', 'billAddress.state');
		
		$deliveryAddress = $this->addContainer('deliveryAddress');
		
		$deliveryAddress->addText('street', 'deliveryAddress.street')
			->setRequired();
		
		$deliveryAddress->addText('city', 'deliveryAddress.city')
			->setRequired();
		
		$deliveryAddress->addText('zipcode', 'deliveryAddress.zipcode')
			->setRequired();
		
		$deliveryAddress->addText('state', 'deliveryAddress.state');
		
		$this->addSubmit('submit', 'registerForm.submit');
		
		$this->onSuccess[] = [$this, 'success'];
	}
	
	public static function validateLogin(\Nette\Forms\IControl $control, Repository $entityRepository): bool
	{
		return !$entityRepository->getByAccountLogin($control->getValue());
	}
	
	public function success(RegistrationForm $form)
	{
		$values = $form->getValues();
		
		$billAddressValues = (array)$form['billAddress']->getValues();
		$billAddressValues += [
			'name' => $form['billAddress']->getValues()->street . ',' . $form['billAddress']->getValues()->city,
			'uuid' => Connection::generateUuid(),
		];
		$billAddress = $this->addressRepository->createOne($billAddressValues);
		
		$deliveryAddressValues = (array)$form['deliveryAddress']->getValues();
		$deliveryAddressValues += [
			'name' => $form['deliveryAddress']->getValues()->street . ',' . $form['deliveryAddress']->getValues()->city,
			'uuid' => Connection::generateUuid(),
		];
		$deliveryAddress = $this->addressRepository->createOne($deliveryAddressValues);
		
		// Account
		$account = $this->accountRepository->createOne([
			'uuid' => Connection::generateUuid(),
			'login' => $values->login,
			'password' => Authenticator::setCredentialTreatment($values->password),
			'active' => !$this->shopper->registration->confirmation,
			'authorized' => !$this->shopper->registration->emailAuthorization,
		]);
		
		// Customer
		$params = [
			'email' => $values->email,
		];
		$token = $this->shopper->registration->emailAuthorization ? Nette\Utils\Random::generate(128) : '';
		
		$customer = $this->entityRepository->createNew([
			'uuid' => Connection::generateUuid(),
			'account' => $account->getPK(),
			'email' => $values->email,
			'emailAndPasswordConfirmationToken' => $token,
			'billAddress' => $billAddress->getPK(),
			'deliveryAddress' => $deliveryAddress->getPK(),
			'type' => $this->shopper->registration->default->type ?? 'retail',
		]);
		
		$mail = $this->shopper->registration->emailAuthorization ? $this->templateRepository->createMessage('register.confirmation', $params + ['link' => $this->getPresenter()->link('//confirmUserEmail!', $token)], $values->email) : $this->templateRepository->createMessage('register.success', $params, $values->email);
		$this->mailer->send($mail);
		
		if ($this->shopper->registration->confirmation && isset($this->shopper->registration->confirmationEmail)) {
			$mail = $this->templateRepository->createMessage('register.adminInfo', $params, $this->shopper->registration->confirmationEmail);
			$this->mailer->send($mail);
		}
		
		$this->onRegister($this, $customer);
	}
	
}
