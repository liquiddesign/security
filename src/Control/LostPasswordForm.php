<?php

declare(strict_types=1);

namespace App\User\Controls;

use App\User\DB\Customer;
use App\User\DB\CustomerRepository;
use Messages\DB\TemplateRepository;
use Nette;
use Translator\Translator;

/**
 * @method onRecover(\App\User\Controls\LostPasswordForm $form)
 */
class LostPasswordForm extends \Nette\Application\UI\Form
{
	/**
	 * @var callable[]&callable(\App\User\Controls\LostPasswordForm): void; Occurs after recover
	 */
	public $onRecover;
	
	const EMAIL_EXISTS = '\App\User\Controls\LostPasswordForm::validateEmail';
	
	private TemplateRepository $templateRepository;
	
	private Nette\Mail\Mailer $mailer;
	
	private CustomerRepository $customerRepository;
	
	public function __construct(CustomerRepository $customerRepository, Translator $translator, Nette\Mail\Mailer $mailer, TemplateRepository $templateRepository)
	{
		parent::__construct();
		$this->templateRepository = $templateRepository;
		$this->customerRepository = $customerRepository;
		$this->mailer = $mailer;
		
		$this->setTranslator($translator);
		$this->addText('email', 'lostPasswordForm.email')
			->addRule($this::EMAIL)
			->addRule($this::EMAIL_EXISTS, 'lostPasswordForm.emailNotFound', $customerRepository)
			->setRequired();
		$this->addSubmit('submit');
		
		$this->onSuccess[] = [$this, 'success'];
		
	}
	
	public function success(LostPasswordForm $form): void
	{
		$values = $form->getValues();
		$params = [
			'email' => $values->email,
		];
		
		$token = Nette\Utils\Random::generate(128);
		
		$customer = $this->customerRepository->one(['email' => $values->email]);
		$customer->update(['token' => $token]);
		
		$mail = $this->templateRepository->createMessage('lostPassword', $params + ['link' => $this->getPresenter()->link('//generateNewPassword!', [$token, $values->email])],$values->email);
		$this->mailer->send($mail);
		$this->onRecover($this);
	}
	
	public static function validateEmail(\Nette\Forms\IControl $control, CustomerRepository $customerRepository): bool
	{
		return (bool)$customerRepository->getByEmail($control->getValue());
	}
	
}