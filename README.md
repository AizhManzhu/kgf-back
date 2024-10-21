# Kazakhstan Growth Forum backend


1) Установка зависимостей проекта
```
composer install
```
2) Копируем .env.example
```
cp .env.example .env
```
3) Генерируем ключ
   (перед запуском sail, прочитайте https://laravel.com/docs/10.x/sail#configuring-a-shell-alias)
```
sail artisan key:generate
```
4) Запускаем миграцию
```
sail artisan migrate
```
5) Запускаем проект
```
sail up
```
5.1) Для запуска проекта в фоновом режиме (optional)
```
sail up -d
```


## Юзеры и роли
