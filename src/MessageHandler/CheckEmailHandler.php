<?php

namespace App\MessageHandler;

use App\Message\CheckEmail;
use App\Model\CheckEmailStatus;
use App\Repository\UserRepository;
use App\Service\CheckEmailService;
use App\Service\CheckUserEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обработчик очереди на проверку email
 */
#[AsMessageHandler(handles: CheckEmail::class)]
class CheckEmailHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CheckUserEmailService $checkUserEmailService,
    ) {
    }

    public function __invoke(CheckEmail $message): void
    {
        try {
            if (!$user = $this->userRepository->find($message->userId)) {
                // пользователя нет - считаем что удалён и всё ок
                return;
            }
            $this->checkUserEmailService->check($user);
            $this->entityManager->flush();
        } finally {
            $this->entityManager->clear();
        }
    }
}