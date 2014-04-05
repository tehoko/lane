<?php
class UserController
{
	private function preWork()
	{
		ControllerHelper::attachUser();
	}

	/**
	* @route /a/{userName}/account-settings
	* @route /a/{userName}/account-settings/
	* @validate userName [a-zA-Z0-9_-]+
	*/
	public function accountSettingsAction($userName)
	{
		$this->preWork($userName);

		if (!ControllerHelper::canEditData($this->context->user))
			throw new SimpleException('Cannot edit settings of this user.');

		if (!$this->context->isSubmit)
			return;

		$this->context->user->settings->showGuestsLastUpdate = boolval(InputHelper::getPost('show-guests-last-update'));
		$this->context->user->settings->showCheatSheet = boolval(InputHelper::getPost('show-cheat-sheet'));

		$currentPassword = InputHelper::getPost('current-password');
		$currentPasswordHash = UserService::hashPassword($currentPassword);
		$currentPasswordOk = $currentPasswordHash == $this->context->user->passHash;

		$newPassword1 = InputHelper::getPost('new-password1');
		$newPassword2 = InputHelper::getPost('new-password2');
		$newPasswordHash = UserService::hashPassword($newPassword1);
		$newEmail = InputHelper::getPost('new-e-mail');

		if ($newPassword1)
		{
			if (!$currentPasswordOk)
				throw new ValidationException('Must supply valid current password.');

			if ($newPassword1 != $newPassword2)
				throw new ValidationException('Passwords must be the same.');

			$this->context->user->passHash = $newPasswordHash;
		}

		if ($newEmail and $newEmail != $this->context->user->email)
		{
			if (!$currentPasswordOk)
				throw new ValidationException('Must supply valid current password.');

			$validator = new Validator($newEmail, 'e-mail');
			$validator->checkEmail();

			$this->context->user->email = $newEmail;
		}

		UserService::saveOrUpdate($this->context->user);

		Messenger::success('Settings changed successfully.');

		$lastViewedList = ListService::getLastViewedList();
		Bootstrap::forward(\Chibi\UrlHelper::route('list', 'view', [
			'userName' => ListService::getOwner($lastViewedList)->name,
			'id' => $lastViewedList->urlName]));
	}

	/**
	* @route /a/{userName}/delete-account
	* @route /a/{userName}/delete-account/
	* @validate userName [a-zA-Z0-9_-]+
	*/
	public function deleteAccountAction($userName)
	{
		$this->context->viewName = 'messages';
		$this->preWork($userName);

		if (!ControllerHelper::canEditData($this->context->user))
			throw new SimpleException('Cannot delete account of this user.');

		if (!$this->context->isSubmit)
			return;

		UserService::delete($this->context->user);

		AuthHelper::logout();
		Messenger::success('Account deleted.');
		Bootstrap::forward('/');
	}

	/**
	* @route /a/{userName}/exec
	* @route /a/{userName}/exec/
	* @validate userName [a-zA-Z0-9_-]+
	*/
	public function execAction($userName)
	{
		$this->context->viewName = 'messages';
		$this->preWork($userName);

		if ($this->context->isSubmit)
			ControllerHelper::executeJobsSafely(ControllerHelper::getJobsFromInput(), $this->context->user);
	}
}
