Meister Company - ToDoList Task Management System
===============================================

This project is a PHP & MySQL based To-Do List and Task Management System built for small and medium teams.

Features implemented in this scaffold:
- User management with roles (Admin, Team Leader, Member)
- Tasks with title, description, priority, deadline, status, progress
- Subtasks, tags, comments, attachments
- Daily To-Do lists per user
- Dashboard with charts and calendar view (frontend uses FullCalendar and Chart.js)
- Email notification placeholders and integration hooks
- Dark/Light mode and workspace theme settings

Requirements
- PHP 8.x
- MySQL 5.7+ / MariaDB
- XAMPP (Windows) or similar Apache+PHP stack

Quick setup (Windows + XAMPP)
1. Copy this project into your XAMPP `htdocs` folder: `C:\xampp\htdocs\Meister Company\ToDoList`
2. Start Apache and MySQL via XAMPP Control Panel.
3. Create a database `meister_todo` and import `migrations/schema.sql`.
4. Edit `api/config.php` and set DB credentials.
5. Create an admin user (see `scripts/create_admin.php`).
6. Open `http://localhost/Meister%20Company/ToDoList/public/` in your browser.

Email notifications
- This scaffold includes a basic notifications helper at `api/notifications.php` which will use PHPMailer if you install dependencies via Composer, otherwise it falls back to PHP `mail()`.
- To enable reliable email delivery, install PHPMailer via Composer in the project root:

```powershell
cd "C:\xampp\htdocs\Meister Company\ToDoList"
composer require phpmailer/phpmailer
```

- Configure SMTP credentials in `api/notifications.php` (replace `smtp.example.com`, `smtp_user`, `smtp_pass`).
- A scheduled reminder script `scripts/send_deadline_reminders.php` is provided — schedule it daily using Windows Task Scheduler. Example command:

```powershell
php "C:\xampp\htdocs\Meister Company\ToDoList\scripts\send_deadline_reminders.php"
```

Notes & Next steps
- Integrations (Slack, WhatsApp, Google Calendar) are not implemented yet; there are UI placeholders and hooks where they can be added.
- Report exports (Excel/PDF) are placeholders. Use `phpoffice/phpspreadsheet` and `tecnickcom/tcpdf` for exports when needed.

WhatsApp (Twilio) integration
- This project supports sending WhatsApp messages via Twilio. To enable:
	1. Create a Twilio account and get your Account SID and Auth Token.
	2. Use a Twilio WhatsApp-enabled number or sandbox number.
	3. Open `api/config.php` and set `twilio.account_sid`, `twilio.auth_token`, and `twilio.from_whatsapp_number` (e.g. `+1415xxxxxxx`).
	4. Add phone numbers for users in the `users.phone` column (E.164 format, e.g. `+15551234567`). A migration file `migrations/alter_add_phone.sql` is provided to add the column.
	5. The system will attempt to send WhatsApp messages for task assignments and deadline reminders when a user's `phone` is set.

Example Twilio config snippet in `api/config.php`:

```php
'twilio' => [
		'account_sid' => 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
		'auth_token' => 'your_auth_token',
		'from_whatsapp_number' => 'whatsapp:+1415xxxxxxx'
]
```

Note: Twilio sandbox requires the recipient to opt-in (join code) during testing. See Twilio docs for sandbox setup.
