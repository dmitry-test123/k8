<?php

namespace App\Service;

use RuntimeException;

/**
 * Сервис отправки любых email
 */
class SendEmailService
{
    /**
     * @throws RuntimeException
     */
    public function sendEmail(string $from, string $to, string $text): void
    {
        if (rand(0, 99) === 0) {
            throw new RuntimeException(); // эмуляция ошибки отправки
        }
        sleep(rand(1, 10));
    }
}