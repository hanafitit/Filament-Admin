# Freelance CRM

## Режимы БД

Проект поддерживает три режима, которые переключаются через `.env` до запуска приложения:

```env
APP_DB_MODE=local
DB_LOCAL_CONNECTION=sqlite
DB_REMOTE_CONNECTION=pgsql_remote
```

- `local`:
  приложение полностью работает на локальной БД.
- `remote`:
  приложение полностью работает на серверной БД.
- `hybrid`:
  приложение работает на локальной БД, а синхронизация в серверную уходит каждые 5 минут.

Во всех режимах выбор активной БД делается автоматически через `App\Support\Database\DatabaseModeManager`.

## Первичная копия локальной БД в серверную

Перед началом работы в `hybrid` режиме сначала скопируйте локальную БД в серверную:

```bash
php artisan app:sync-remote-database --force
```

Команда:

- делает `upsert` по основным таблицам приложения;
- удаляет на серверной БД записи, которых больше нет в локальной;
- выравнивает PostgreSQL sequence после копирования.

Если нужно только долить данные без удаления лишних строк на сервере:

```bash
php artisan app:sync-remote-database --force --keep-extra
```

## Render

Для Render добавлен [render.yaml](/C:/Users/Байсангур/Новая%20папка/render.yaml), рассчитанный на гибридный режим:

- локальная SQLite хранится на persistent disk в `/var/data/database.sqlite`;
- web-сервис поднимает Laravel, `queue:work` и `schedule:work` в одном контейнере;
- расписание Laravel запускает синхронизацию серверной БД каждые 5 минут.

Минимальные переменные, которые нужно заполнить на Render:

- `APP_KEY`
- `APP_URL`
- `REMOTE_DB_HOST`
- `REMOTE_DB_PORT`
- `REMOTE_DB_DATABASE`
- `REMOTE_DB_USERNAME`
- `REMOTE_DB_PASSWORD`
- `REMOTE_DB_SSLMODE`

Если нужен строгий интервал синхронизации без пауз, лучше не использовать sleeping/free-окружение, потому что при сне web-сервиса фоновые процессы тоже останавливаются.
