# Proyecto Autocaravana 🚐

Guía rápida para arrancar el entorno de desarrollo local en Windows + WSL.

## ⚠️ Prerrequisito Indispensable

* **Docker Desktop** debe estar **iniciado** y funcionando (el icono de la ballena  estable) antes de ejecutar cualquier comando.

---

## 🚀 Arranque del Entorno (2 Terminales)

Necesitarás **dos (2) terminales de Ubuntu (WSL)** abiertas simultáneamente.

### 1. Terminal 1: Backend (PHP + Base de Datos)

Esta terminal arranca el servidor web y la base de datos.

```bash
# 1. Abre una terminal de Ubuntu.
# 2. Navega a la carpeta del proyecto:
cd ~/projects/autocaravana-web

# 3. Arranca el servidor Sail (PHP, Nginx, MySQL):
./vendor/bin/sail up -d
```

*(Esta terminal quedará libre para usar otros comandos de `sail artisan...` si lo necesitas)*.

### 2. Terminal 2: Frontend (CSS/JS)

Esta terminal compila el CSS (Tailwind) y JavaScript en tiempo real.

```bash
# 1. Abre una *segunda* terminal de Ubuntu.
# 2. Navega a la carpeta del proyecto:
cd ~/projects/autocaravana-web

# 3. Arranca el servidor Vite (se quedará "escuchando"):
./vendor/bin/sail npm run dev
```

*(Esta terminal **se quedará ocupada** mostrando la salida de Vite. Déjala así)*.

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

1.  En la **Terminal 2** (la de `npm run dev`), pulsa **Ctrl + C**.

2.  En la **Terminal 1**, ejecuta:

    ```bash
    ./vendor/bin/sail down
    ```
