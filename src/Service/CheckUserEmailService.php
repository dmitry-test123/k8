<?php

namespace App\Service;

use App\Model\CheckEmailStatus;
use App\Model\User;

/**
 * Сервис проверки email пользователя
 */
class CheckUserEmailService
{
    public function __construct(
        private readonly CheckEmailService $checkEmailService,
    ) {
    }

    public function check(User $user): void
    {
        if ($user->getChecked() == CheckEmailStatus::Checked) {
            // уже проверен, не тратим рубль
            return;
        }

        $result = $this->checkEmailService->checkEmail($user->getEmail());
        $user->setValid($result);
        $user->setChecked(CheckEmailStatus::Checked);
    }
}