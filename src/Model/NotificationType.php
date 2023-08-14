<?php

namespace App\Model;

/**
 * Номера уведомлений (по хорошему - вынести в yaml)
 * Строго в порядке отправления (за месяц - первее чем за неделю)
 * При изменении конфигурации - сначала закончить обработку старой очереди!
 */
enum NotificationType: int
{
    case First = 1;
    case Second = 2;

    const TIME_FOR_CHECK = 259140; // почти 3 дня

    public function getSecondsToExpiry(): int
    {
        return match ($this) {
            NotificationType::First => 259200, // 3 дня
            NotificationType::Second => 86400, // 1 день
        };
    }
}