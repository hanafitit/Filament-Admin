# Freelance CRM 🚀

[![Laravel](https://img.shields.io/badge/Laravel-13.8-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-v3-FFA116?style=for-the-badge&logo=laravel&logoColor=white)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

**Freelance CRM** — это профессиональное решение для фрилансеров и команд, объединяющее управление заказами, финансовую аналитику и автоматизацию в одном компактном интерфейсе. Забудьте о таблицах — контролируйте всё от первого клика до финальной выплаты.

---

## 🛠 Ключевые возможности

### 📦 Управление и контроль
- **Интеллектуальный Kanban**: Ведите заказы по этапам с автоматическим расчетом чистой прибыли (с учетом комиссий Kwork, FL и др.).
- **Центр коммуникаций**: Все вложения, комментарии и дедлайны привязаны к конкретным задачам.
- **Умные уведомления**: Интеграция с **Telegram** оповестит о смене статусов и приближающихся сроках.
- **Безопасность данных**: Гибкое управление доступом (**RBAC/Shield**) и автоматические бэкапы.

### 📊 Аналитика и финансы
![Financial Reports](https://raw.githubusercontent.com/your-repo/freelance-crm/main/docs/images/fin-report.png)
*(Замените ссылку на актуальный путь к Снимку экрана 2026-07-04 174737.png)*

- **Прозрачная прибыль**: Детальные отчеты по источникам (биржи, прямые клиенты) и исполнителям.
- **Мгновенный экспорт**: Выгрузка данных в Excel/CSV для глубокого анализа или отчетности.

---

## ⚙️ Синхронизация (Hybrid DB)

Уникальная архитектура позволяет системе работать быстро и надежно в любых условиях:
- **Local (SQLite)**: Максимальная скорость и оффлайн-доступ.
- **Remote (PostgreSQL)**: Централизованное хранение данных.
- **Hybrid**: Работа на локальной базе с фоновой синхронизацией на сервер каждые 5 минут.

---

## 🚀 Быстрый старт

```bash
# Установка зависимостей
composer install && npm install

# Настройка окружения
cp .env.example .env && php artisan key:generate

# Миграции и запуск
php artisan migrate --seed
php artisan serve
```

---

## 💻 Стек технологий

- **Core**: Laravel 13.8 (PHP 8.3)
- **UI/UX**: Filament v3, Tailwind CSS v4, Vite 8
- **DB**: SQLite / PostgreSQL
- **Deploy**: Полная поддержка Render (конфиг `render.yaml` включен)

---

## 📄 Лицензия
MIT. Сделано для эффективного фриланса.
