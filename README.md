# 🚀 Caravan Renting Platform (Autocaravana Project)

A full-stack enterprise-grade web application for caravan rental management. Built with **Laravel Sail**, **Livewire**, and **Tailwind CSS**, this platform focuses on high availability, secure data management, and automated user notifications.

---

## 🌟 Key Features

* **Custom Admin Dashboard:** Specialized panel for managing fleet, bookings, and customer data.
* **Asynchronous UX:** Implementation of AJAX and Livewire for a seamless, "Single Page Application" feel during the booking and checkout processes.
* **Automated Notification Engine:** System-wide automated emails for booking confirmations and reminders.
* **Secure Infrastructure:** Designed with security-by-default principles, including protection against SQL injection and XSS via Laravel’s Eloquent and Blade engines.
* **Reporting Tools:** Generation of PDF invoices and rental contracts on-the-fly.

---

## 🛠️ Tech Stack

* **Backend:** PHP 8.x | Laravel Framework
* **Frontend:** Blade | Livewire (Reactive UI) | Alpine.js | Tailwind CSS
* **Database:** MySQL (Relational modeling & normalization)
* **DevOps & Environment:** Docker (Laravel Sail) | Vite | Git
* **Automation:** Linux Cron Jobs for scheduled tasks (Daily backups & Email reminders)

---

## ⚠️ Prerequisites

* **Docker Desktop:** Ensure the Docker engine is running before starting the environment.
* **WSL 2:** Highly recommended for Windows users to ensure native Linux performance and compatibility.

---

## 🚀 Local Development Setup

To start the development environment, you will need **two (2) Ubuntu (WSL) terminals** running simultaneously.

### 1️⃣ Backend & Database (Terminal 1)

This command initializes the web server, PHP, and MySQL containers.

```bash
cd ~/projects/autocaravana-web
./vendor/bin/sail up -d
```

> Note: This terminal is ready for sail artisan commands (migrations, seeding, etc.).

---

### 2️⃣ Frontend Assets (Terminal 2)

This terminal compiles CSS and JS in real-time using Vite's Hot Module Replacement (HMR).

```bash
cd ~/projects/autocaravana-web
./vendor/bin/sail npm run dev
```

> Note: Keep this process running to see UI changes instantly during development.

---

## 💻 Code Access

Launch VS Code directly from your WSL environment to ensure proper integration:

```bash
code .
```

---

## 🔗 Local Access Points

* **Public Site:** http://localhost  
* **Admin Dashboard:** http://localhost/admin  

---

## 🌍 Production Deployment Workflow

Standardized workflow for deploying updates to a production Linux environment.

---

### 🧭 Phase 1: Local Preparation

Compile assets for production:

```bash
npm run build
```

Commit and push changes to the main branch:

```bash
git add .
git commit -m "feat: implement automated email notifications and security hardening"
git push origin main
```

---

### 🖥️ Phase 2: Server Execution (via SSH)

Execute the following commands in the project's root directory on the production server:

Enable Maintenance Mode:

```bash
php artisan down
```

Pull Latest Updates:

```bash
git pull origin main
```

Install Production Dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

Execute Database Migrations:

```bash
php artisan migrate --force
```

Cache Optimization (Crucial for Speed):

```bash
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

Disable Maintenance Mode:

```bash
php artisan up
```

---

### ⏰ Phase 3: Task Scheduling (Cron Job)

To ensure the `schedule:run` command (responsible for email [RF7.1]) works automatically:

Open crontab:

```bash
crontab -e
```

Add the following line (adjusting the path to your production directory):

```bash
* * * * * cd /var/www/autocaravana-web && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧾 Project Information

* **Lead Engineer:** Juan José Martínez Martínez  
* **Organization:** TrookeM S.L.  
* **Core Architecture:** Laravel Sail (Nginx, PHP 8.x, MySQL, Vite)  
* **Last Maintenance Update:** February 2026  

---

## 🛑 How to Stop the Environment

In Terminal 2, press `Ctrl + C` to stop Vite.

In Terminal 1, run:

```bash
./vendor/bin/sail down
```

---

## 🔐 Recommended `.gitignore` Configuration

To make your GitHub repository look **Top Tier**, go to the main repository page, click **"Add File" → "Create new file"**, name it `.gitignore` (if you don’t already have one), and ensure it includes:

```text
.env
/vendor
/node_modules
/public/storage
/storage/*.key
.phpunit.result.cache
```
