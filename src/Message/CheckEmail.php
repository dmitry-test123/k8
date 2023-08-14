<?php

namespace App\Message;

/**
 * Команда на проверку email
 */
class CheckEmail
{
    public function __construct(
        public readonly int $userId,
    ) {
    }
}