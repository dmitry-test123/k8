<?php

namespace App\Message;

/**
 * Команда на отправку уведомления об истечении подписки
 */
class NotifyExpiringSubscription
{
    public function __construct(
        public readonly int $userId,
    ) {
    }
}