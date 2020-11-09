<?php

declare(strict_types=1);

namespace Security\Control;

use Messages\DB\TemplateRepository;
use Nette;
use Security\DB\IUser;
use StORM\DIConnection;
use StORM\Repository;

/**
 * @method onRecover(\Security\Control\LostPasswordForm $form)
 */
class LostPasswordForm extends \Nette\Application\UI\Form
{
	public const EMAIL_EXISTS = '\Security\Control\LostPasswordForm::validateEmail';

	/**
	 * @var callable[]&callable(\Security\Control\LostPasswordForm): void; Occurs after recover
	 */
	public $onRecover;
	
	protected TemplateRepository $templateRepository;
	
	protected Nette\Mail\Mailer $mailer;
	
	protected Repository $repository;
	
	public function __construct(string $class, DIConnection $connection, Nette\Localization\ITranslator $translator, Nette\Mail\Mailer $mailer, TemplateRepository $templateRepository)
	{
		parent::__construct();

		$this->templateRepository = $templateRepository;
		$this->mailer = $mailer;
		
		if (!\is_subclass_of($class, IUser::class) || !\is_subclass_of($class, Nette\Security\IIdentity::class)) {
			throw new \InvalidArgumentException("Wrong or empty class: $class");
		}
		
		$this->repository = $connection->findRepository($class);
		
		$this->setTranslator($translator);
		$this->addText('email', 'lostPasswordForm.email')
			->addRule($this::EMAIL)
			->addRule($this::EMAIL_EXISTS, 'lostPasswordForm.emailNotFound', $this->repository)
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
		
		$customer = $this->repository->one(['email' => $values->email]);
		$customer->account->update(['confirmationToken' => $token]);
		
		$mail = $this->templateRepository->createMessage('lostPassword', $params + ['link' => $this->getPresenter()->link('//generateNewPassword!', [$token, $values->email])], $values->email);
		$this->mailer->send($mail);
		$this->onRecover($this);
	}
	
	public static function validateEmail(\Nette\Forms\IControl $control, Repository $repository): bool
	{
		return (bool)$repository->getByEmail($control->getValue());
	}
}
