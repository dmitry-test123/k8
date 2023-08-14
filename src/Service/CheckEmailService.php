<?php

namespace App\Service;

use RuntimeException;

/**
 * Сервис проверки любых email
 */
class CheckEmailService
{
    /**
     * Проверяет email на валидность
     * @throws RuntimeException
     */
    public function checkEmail(string $email): int
    {
        if (rand(0, 99) === 0) {
            throw new RuntimeException(); // эмуляция недоступности внешнего сервиса
        }
        sleep(rand(1, 10));
        return rand(0, 1);
    }
}