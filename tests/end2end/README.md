# Сквозное тестирование

Для работы нужен NodeJS LTS с npm 8.12.

## Установка:

1. Скопировать `.env.example` в `.env`,
2. Указать в `.env` URL тестового Битрикс24, логин и пароль администратора,
3. Выполнить `npm install`

## Запуск

- `npm run codeceptjs` или `npm run codeceptjs:ui`
- `npm run codeceptjs:headless` если окно браузера показывать не нужно
