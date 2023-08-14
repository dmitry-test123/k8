<?php

namespace App\Service;

use App\Model\User;
use BadMethodCallException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Сервис уведомлений пользователя об истекающей подписке
 */
class NotifyExpiringSubscriptionService
{
    public function __construct(
        #[Autowire('%env(NOTIFICATION_EMAIL_FROM)%')]
        private readonly string $emailFrom,
        private readonly SendEmailService $sendEmailService,
    ) {
    }

    /**
     * Уведомить пользователя об истекающей подписке
     * @throws BadMethodCallException Нет задач на отправку уведомления для пользователя
     * @throws RuntimeException       Ошибка отправки письма
     */
    public function notify(User $user): void
    {
        if (!$notification = $user->getNotification()) {
            throw new BadMethodCallException();
        }
        $this->sendEmailService->sendEmail(
            $this->emailFrom,
            $user->getEmail(),
            sprintf(
                '%s, your subscription is expiring soon',
                $user->getUserName(),
                // $notification->value,
            )
        );
    }
}