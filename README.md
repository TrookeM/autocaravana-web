## 📘 Proyecto Autocaravana

Guía rápida para arrancar el entorno de desarrollo local en Windows + WSL y desplegar el proyecto en producción.

---

## ⚠️ Prerrequisito Indispensable

* **Docker Desktop** debe estar **iniciado** y funcionando (el icono de la ballena estable) antes de ejecutar cualquier comando.

---

## 🚀 Arranque del Entorno (2 Terminales)

Necesitarás **dos (2) terminales de Ubuntu (WSL)** abiertas simultáneamente.

---

### 1️⃣ Terminal 1: Backend (PHP + Base de Datos)

Esta terminal arranca el servidor web y la base de datos.

```bash
# 1. Abre una terminal de Ubuntu.
# 2. Navega a la carpeta del proyecto:
cd ~/projects/autocaravana-web

# 3. Arranca el servidor Sail (PHP, Nginx, MySQL):
./vendor/bin/sail up -d
```

*(Esta terminal quedará libre para usar otros comandos de `sail artisan...` si lo necesitas).*

---

### 2️⃣ Terminal 2: Frontend (CSS/JS)

Esta terminal compila el CSS (Tailwind) y JavaScript en tiempo real.

```bash
# 1. Abre una *segunda* terminal de Ubuntu.
# 2. Navega a la carpeta del proyecto:
cd ~/projects/autocaravana-web

# 3. Arranca el servidor Vite (se quedará "escuchando"):
./vendor/bin/sail npm run dev
```

*(Esta terminal **se quedará ocupada** mostrando la salida de Vite. Déjala así).*

---

## 💻 Abrir el Código (VS Code)

Puedes hacer esto en **cualquiera** de las dos terminales, **después** de haber entrado a la carpeta del proyecto (`cd ...`).

```bash
code .
```

---

## 🔗 Enlaces Útiles

Una vez que ambos servidores estén corriendo:

* **Web Pública:** [http://localhost](http://localhost)
* **Panel de Admin:** [http://localhost/admin](http://localhost/admin)

---

## 🛑 Cómo Parar Todo

Cuando termines de trabajar:

1. En la **Terminal 2** (la de `npm run dev`), pulsa **Ctrl + C**.
2. En la **Terminal 1**, ejecuta:

```bash
./vendor/bin/sail down
```

---

## 🌍 Despliegue en Producción

Guía para subir los cambios del entorno local (desarrollo) al servidor de producción.

---

### 🧭 Fase 1: En tu máquina local (Antes de subir)

1. **Compilar los assets:** Tus cambios en CSS y JS deben ser compilados para producción.

```bash
# Ejecuta esto en tu terminal local (no en sail)
npm run build
```

2. **Confirmar todos los cambios en Git:** Sube todos tus archivos nuevos y modificados al repositorio.

```bash
git add .
git commit -m "Descripción de los cambios (ej: Implementa reseñas y emails)"
git push origin main  # O el nombre de tu rama principal
```

---

### 🖥️ Fase 2: En tu Servidor de Producción

Conéctate a tu servidor por SSH y ejecuta los siguientes comandos en la raíz de tu proyecto.

1. **Activar Modo Mantenimiento:** Pone la web "en pausa" para los visitantes.

```bash
php artisan down
```

2. **Actualizar el Código:** Descarga los cambios que subiste a Git.

```bash
git pull origin main
```

3. **Instalar Dependencias de Composer:** Actualiza el backend.

```bash
composer install --no-dev --optimize-autoloader
```

4. **Ejecutar las Migraciones:** ¡El paso más importante! Actualiza la base de datos de producción con las nuevas tablas y columnas.

```bash
php artisan migrate --force
```

5. **Optimizar la Caché:** Acelera la aplicación en producción.

```bash
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

6. **Desactivar Modo Mantenimiento:** Vuelve a poner la web online.

```bash
php artisan up
```

---

### ⏰ Fase 3: Configurar el CRON JOB (Solo 1 vez)

Esto es necesario para que los recordatorios por email ([RF7.1]) funcionen automáticamente.

1. En tu servidor de producción, abre el editor de tareas cron:

```bash
crontab -e
```

2. Añade esta línea **al final** del archivo. Asegúrate de cambiar la ruta a la de tu proyecto.

```bash
# Ejecuta el programador de Laravel cada minuto
* * * * * cd /ruta/completa/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

> *(Ejemplo de ruta: `/var/www/html/autocaravana-web`)*

Esto "despertará" a Laravel cada minuto, y Laravel decidirá si es hora de ejecutar alguna tarea programada (como la de `dailyAt('09:00')` definida en `app/Console/Kernel.php`).

---

## 🧾 Créditos

**Autor:** Juan
**Empresa:** TrookeM S.L.
**Framework:** Laravel Sail (con Nginx, PHP, MySQL y Vite)
**Fecha de última actualización:** Octubre 2025