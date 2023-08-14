<?php

namespace App\Model;

/**
 * Статус проверки валидности email
 */
enum CheckEmailStatus: int
{
    // Проверки не было
    case NotChecked = 0;

    // Проверка была
    case Checked = 1;

    // Проверка в процессе
    case Wait = 2;
}