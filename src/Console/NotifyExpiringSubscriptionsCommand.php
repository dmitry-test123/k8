<?php

namespace App\Console;

use App\Message\CheckEmail;
use App\Message\NotifyExpiringSubscription;
use App\Model\CheckEmailStatus;
use App\Model\NotificationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Консольная команда на отправку уведомлений об истекающих подписках
 */
#[AsCommand('app:notify_expiring_subscriptions')]
class NotifyExpiringSubscriptionsCommand extends Command
{
    /**
     * @param UserRepository $userRepository Просили поменьше ООП, поэтому здесь реализация вместо интерфейса
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ищем непроверенных, близких к уведомлению
        $users = $this->userRepository->getUncheckedExpiringSubscriptions();
        foreach ($users as $user) {
            // ставим задачу, at least once
            $this->bus->dispatch(new CheckEmail($user->getId()));
            $user->setChecked(CheckEmailStatus::Wait);
            $this->entityManager->flush();
        }
        $this->entityManager->clear();


        // собираем номера уведомлений в обратном порядке (чтобы поставить 1 задачу в случае накопления)
        $notificationTypes = array_reverse(NotificationType::cases());
        foreach ($notificationTypes as $notificationType) {
            $this->sendNotifyNumber($notificationType);
        }
        return Command::SUCCESS;
    }

    /**
     * Поиск и отправка уведомлений об истекающих подписках нужного типа
     * @param NotificationType $number Номер уведомления
     * @return void
     */
    private function sendNotifyNumber(NotificationType $number): void
    {
        $users = $this->userRepository->getUntaskedCheckedExpiringSubscriptions($number);
        foreach ($users as $user) {
            // ставим задачу на отправку, at least once
            $this->bus->dispatch(new NotifyExpiringSubscription($user->getId()));
            $user->setNotification($number);
            $this->entityManager->flush();
        }
    }
}