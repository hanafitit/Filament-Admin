# Freelance CRM 🚀

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-FFA116?style=for-the-badge&logo=laravel&logoColor=white)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

**Freelance CRM** — это мощная и гибкая система для управления заказами, разработанная специально для фрилансеров и небольших команд. Система позволяет автоматизировать учет доходов, контролировать дедлайны и анализировать финансовую эффективность работы на различных площадках.

![Dashboard Preview](https://placehold.co/1200x600/2a2a2a/white?text=Freelance+CRM+Dashboard)

---

## 🌟 Ключевые возможности

### 📦 Управление заказами
![Kanban Board](https://placehold.co/800x400/2a2a2a/white?text=Kanban+Board+View)
- **Kanban-доска**: Наглядное управление этапами работы над заказами.
- **Интеграция с биржами**: Поддержка популярных площадок (Kwork, FL.ru и др.).
- **Автоматический расчет комиссий**: Система сама вычисляет чистую прибыль, учитывая прогрессивные комиссии бирж.
- **Вложения и комментарии**: Храните все файлы и переписку по заказу в одном месте.

### 📊 Аналитика и отчетность
![Financial Reports](https://placehold.co/800x400/2a2a2a/white?text=Financial+Analytics+Report)
- **Финансовый отчет**: Детальная статистика по выручке, комиссиям и чистой прибыли.
- **Фильтрация**: Анализируйте данные за любые периоды, в разрезе менеджеров или источников трафика.
- **Экспорт данных**: Выгрузка отчетов в форматы Excel (.xlsx) и CSV для дальнейшей обработки.

### 🤖 Автоматизация и уведомления
- **Telegram Bot**: Мгновенные уведомления о новых событиях и изменениях статусов.
- **Контроль дедлайнов**: Система напомнит о приближающихся сроках сдачи и оплаты.

### 🔐 Безопасность
- **Ролевая модель (RBAC)**: Гибкая настройка прав доступа для администраторов и менеджеров через Filament Shield.
- **Резервное копирование**: Автоматическое создание бэкапов базы данных и файлов.

---

## ⚙️ Уникальная архитектура БД

Проект реализует продвинутую систему синхронизации данных, позволяющую работать в трех режимах:

1.  **Local**: Работа только с локальной базой (SQLite). Идеально для оффлайн-разработки.
2.  **Remote**: Прямая работа с серверной базой (PostgreSQL).
3.  **Hybrid**: Комбинированный режим. Данные пишутся в локальную БД для максимальной скорости отклика, а фоновая синхронизация обновляет серверную БД каждые 5 минут.

*Управление режимами осуществляется через переменную `APP_DB_MODE` в файле `.env`.*

---

## 🚀 Быстрый старт

### Требования
- PHP 8.3+
- Composer
- Node.js & NPM
- SQLite / PostgreSQL

### Установка

1. **Клонируйте репозиторий:**
   ```bash
   git clone https://github.com/your-repo/freelance-crm.git
   cd freelance-crm
   ```

2. **Установите зависимости:**
   ```bash
   composer install
   npm install
   ```

3. **Настройте окружение:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Запустите миграции и сидеры:**
   ```bash
   php artisan migrate --seed
   ```

5. **Запустите проект:**
   ```bash
   npm run dev
   # в другом терминале
   php artisan serve
   ```

---

## ☁️ Деплой (Render)

Проект полностью оптимизирован для развертывания на платформе **Render**.

- Конфигурация описана в `render.yaml`.
- Автоматически настраивается persistent disk для SQLite.
- Поддерживается автоматическое выполнение расписания (Schedule) для гибридного режима.

---

## 🛠 Стек технологий

- **Backend**: Laravel 11+, Filament V3 (TALL Stack)
- **Frontend**: Blade, Tailwind CSS 4, Vite 8
- **Database**: SQLite (Local), PostgreSQL (Production)
- **Integrations**: Telegram Bot API, Maatwebsite Excel

---

## 📄 Лицензия

Этот проект является открытым программным обеспечением, распространяемым по лицензии [MIT](LICENSE).
