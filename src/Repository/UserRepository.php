<?php

namespace App\Repository;

use App\Model\CheckEmailStatus;
use App\Model\NotificationType;
use App\Model\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Репозиторий пользователей
 * @extends EntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Найти пользователей с истекающими подписками и непроверенными имейлами
     * @return iterable<int, User>
     */
    public function getUncheckedExpiringSubscriptions(): iterable
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.checked = :checked')
            ->andWhere('u.confirmed = true') // незачем проверять неподтвержденных
            ->andWhere('u.validTs > :fromTs') // отсекаем неактуальные подписки
            ->andWhere('u.validTs <= :toTs')
            ->setParameter('checked', CheckEmailStatus::NotChecked)
            ->setParameter('fromTs', time())
            ->setParameter('toTs', time() + NotificationType::TIME_FOR_CHECK)
            ->getQuery()
            ->toIterable();
    }

    /**
     * Найти пользователей с истекающими подписками без отправленного уведомления
     * @param NotificationType $number
     * @return iterable<int, User>
     */
    public function getUntaskedCheckedExpiringSubscriptions(NotificationType $number): iterable
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.checked = :checked')
            ->andWhere('u.confirmed = true')
            ->andWhere('u.valid = true')
            ->andWhere('u.validTs > :fromTs') // отсекаем неактуальные подписки
            ->andWhere('u.validTs <= :toTs')
            ->andWhere('(u.notification IS NULL OR u.notification < :notificationType)')
            ->setParameter('checked', CheckEmailStatus::Checked)
            ->setParameter('fromTs', time())
            ->setParameter('toTs', time() + $number->getSecondsToExpiry())
            ->setParameter('notificationType', $number->value)
            ->getQuery()
            ->toIterable();
    }
}