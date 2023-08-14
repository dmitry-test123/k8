<?php

namespace App\MessageHandler;

use App\Message\NotifyExpiringSubscription;
use App\Repository\UserRepository;
use App\Service\NotifyExpiringSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обработчик очереди на отправку уведомления об истекающей подписке
 */
#[AsMessageHandler(handles: NotifyExpiringSubscription::class)]
class NotifyExpiringSubscriptionHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly NotifyExpiringSubscriptionService $notifyService,
    ) {
    }

    public function __invoke(NotifyExpiringSubscription $message): void
    {
        try {
            if (!$user = $this->userRepository->find($message->userId)) {
                // пользователя нет - считаем что удалён и всё ок
                return;
            }
            $this->notifyService->notify($user);
            $this->entityManager->flush();
        } finally {
            $this->entityManager->clear();
        }
    }
}