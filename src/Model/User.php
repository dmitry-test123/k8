<?php

namespace App\Model;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Пользователь
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table('users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue("SEQUENCE")]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'bigint', name: 'validts')]
    private int $validTs = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $confirmed = false;

    #[ORM\Column(type: 'smallint', enumType: CheckEmailStatus::class)]
    private CheckEmailStatus $checked = CheckEmailStatus::NotChecked;

    #[ORM\Column(type: 'boolean')]
    private bool $valid = false;

    /**
     * Тип обработанного уведомления
     */
    #[ORM\Column(type: 'smallint', enumType: NotificationType::class)]
    private ?NotificationType $notification = null;

    public function __construct(
        #[ORM\Column(type: 'string', name: 'username')]
        private string $userName,

        #[ORM\Column(type: 'string')]
        private string $email,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getValidTs(): int
    {
        return $this->validTs;
    }

    public function setValidTs(int $validTs): void
    {
        $this->validTs = $validTs;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): void
    {
        $this->confirmed = $confirmed;
    }

    public function getChecked(): CheckEmailStatus
    {
        return $this->checked;
    }

    public function setChecked(CheckEmailStatus $checked): void
    {
        $this->checked = $checked;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Установить обработанное уведомление об истекающей подписке
     */
    public function setNotification(NotificationType $number): void
    {
        $this->notification = $number;
    }

    /**
     * Получить тип обработанного уведомления об истекающей подписке
     */
    public function getNotification(): ?NotificationType
    {
        return $this->notification;
    }
}