# Lite Scheduler

Plugin modular para WordPress que permite configurar perfiles de atención, recibir reservas, y gestionar citas fácilmente desde el backend. Pensado como una alternativa ligera a Calendly, con soporte para personalizaciones, integración futura con Google Calendar y notificaciones.

---

## 🚀 Características

- Configuración de días y horarios disponibles
- Generación automática de turnos
- Selector de fecha visual (Flatpickr)
- Reservas con nombre, correo, teléfono
- Administración de reservas en el panel
- Eliminación de reservas con AJAX
- Preparado para webhooks, Google Calendar, notificaciones

---

## 📦 Instalación

1. Cloná o descargá el repositorio en `/wp-content/plugins/`
2. Activá el plugin desde el panel de administración
3. Usá el shortcode `[lite_scheduler]` en cualquier página

---

## 🛠️ Estructura del Plugin

lite-scheduler/ ├── lite-scheduler.php # Bootstrap principal ├── includes/ │ ├── init.php # Activación, creación de tabla │ ├── admin.php # Configuración y reservas en admin │ ├── reservas.php # AJAX para guardar/eliminar reservas │ └── calendar-render.php # Shortcode + carga de scripts ├── assets/ │ ├── css/ │ │ ├── admin.css │ │ └── calendar.css │ └── js/ │ ├── admin.js │ └── calendar.js

yaml
Copiar
Editar

---

## ✨ En desarrollo

- Webhooks de creación y cancelación de reservas
- Envío de notificaciones (correo / WhatsApp / Slack)
- Integración con Google Calendar bidireccional
- Exportación a CSV/Excel

---

## 🧑‍💻 Contribuir

1. Fork del repo
2. Crea una rama: `feature/nueva-funcionalidad`
3. Pull Request

---

## 📄 Licencia

MIT - Usá y modificá libremente.