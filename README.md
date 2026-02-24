# 🚀 Caravan Renting Platform (Autocaravana Project)

A full-stack web application for caravan rental management, built with **Laravel Sail**, **Livewire**, and **Tailwind CSS**. This project features an automated notification system, an administration panel, and optimized checkout processes.

---

## 🛠️ Tech Stack

* **Backend:** PHP 8.x, Laravel Framework
* **Frontend:** Blade, Livewire (Reactive UI), Alpine.js, Tailwind CSS
* **Database:** MySQL
* **Environment & DevOps:** Docker (Laravel Sail), Vite, Git
* **Automation:** Linux Cron Jobs for scheduled tasks (Email reminders, system cleanup)

---

## ⚠️ Prerequisites

* **Docker Desktop:** Ensure the Docker engine is running before starting the environment.
* **WSL 2:** Recommended for Windows users to ensure native Linux performance.

---

## 🚀 Local Development Setup

To start the development environment, you will need **two (2) Ubuntu (WSL) terminals** running simultaneously.

### 1️⃣ Backend & Database (Terminal 1)
This command handles the web server and database containers.

```bash
cd ~/projects/autocaravana-web
./vendor/bin/sail up -d
