# Результат выполнения `PHP Developer.Test Case.pdf`

## Анализ задачи

- Предположим, что пользователи размазаны по всем часовым поясам равномерно. 
  Предположим, что подписка оформляется на месяц.
  Таким образом, будет высылаться **в среднем 7 оповещений в минуту** (5000000 пользователей * 0.2 с подпиской * 0,15 с подтверденным email / 30 дней / 24 часов / 60 минут * 2 оповещения)
- Предположим, что отправка email стоит больше 1 рубля. Значит, нужно лениво проверить валидность email до необходимости отправки оповещения.


## Что сделано

1. Модифицирована таблица `users`: 
   1. Добавил колонку `users.notification`, в которой хранится тип (номер) обработанного уведомления. 
   2. Добавил новое значение `2` в `users.checked`. Оно означает ожидание проверки имейла.

2. Быстрый поиск неподтвержденных истекающих подписок с помощью маленького индекса unconfirmed_valid_users_validts.
   План запроса
   ```
   Index Scan using unconfirmed_valid_users_validts on users  (cost=0.42..8.44 rows=1 width=44) (actual time=0.073..0.081 rows=4 loops=1)
   Index Cond: ((validts > 1692290832) AND (validts < 1692290892))
   Planning Time: 0.204 ms
   Execution Time: 0.139 ms
   ```
3. Быстрый поиск подтвержденных истекающих подписок с помощью маленького индекса confirmed_valid_users_validts.
   План запроса
   ```
   Index Scan using confirmed_valid_users_validts on users  (cost=0.42..61.18 rows=23 width=44) (actual time=0.045..0.085 rows=23 loops=1)
    Index Cond: ((validts > 1692290832) AND (validts <= 1692290892))
    Filter: ((notification IS NULL) OR (notification < 1))
    Planning Time: 0.943 ms
    Execution Time: 0.124 ms
   ```
4. Функцию send_email() расположил в `\App\Service\SendEmailService::sendEmail()`
5. Функцию check_email() расположил в `\App\Service\CheckEmailService::checkEmail()`
6. Консольная команда для поиска истекающих подписок и отправки команд в очередь
   `\App\Console\NotifyExpiringSubscriptionsCommand`
7. Обработчик очереди на отправку `\App\MessageHandler\NotifyExpiringSubscriptionHandler`
8. Обработчик очереди на проверку email `\App\MessageHandler\CheckEmailHandler`

## Что можно улучшить
1. Добавить оптимистичные блокировки
2. Настроить Docker, включая Cron и Supervisor
2. Интерфейс UserRepository для декаплинга от доктрины, но меня просили поменьше ООП.

## Выбранный стек
| Технология                                | Обоснование                                                             |
|-------------------------------------------|-------------------------------------------------------------------------|
| Symfony                                   | небольшой фреймворк,<br/>используется в компании,<br/>личная экспертиза |
| Doctrine ORM 2                            | для упрощения работы с БД,<br/>используется в компании                  |
| База данных PostgreSQL                    | поддерживает partial indexes,<br/>личная экспертиза                     |
| Брокер сообщений Symfony Messenger / AMQP | простое и надёжное решение, транспорт легко меняется конфигурацией      |

## Запуск и настройка

1. Зависимости
   ```shell
   composer i
   ```

2. Миграция БД
   ```shell
   php bin/console doctrine:migrations:migrate
   ```

3. Настройка cron. Запускать следующую команду в заданное время. Исходя из анализа задачи - предлагаю 1 раз в минуту.
   ```shell
   php bin/console app:notify_expiring_subscriptions
   ```

4. Запуск воркеров (неограниченное количество)
   ```shell
   php bin/console messenger:consume NotifyExpiringSubscription
   php bin/console messenger:consume CheckEmail
   ```
