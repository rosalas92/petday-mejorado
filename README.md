# PetDay Mejorado

**PetDay Mejorado** es una aplicación web para la gestión integral de rutinas, eventos y salud de mascotas. Permite a los usuarios organizar horarios de comida, paseos, medicaciones, citas veterinarias y mucho más, todo desde una misma plataforma fácil de usar.

---

## Características principales

- **Gestión de Mascotas**: Añade, edita y elimina mascotas. Cada mascota tiene su perfil con datos, historial médico, mediciones y rutinas.
- **Cartilla Sanitaria**: Registra vacunas, desparasitaciones y adjunta documentos médicos.
- **Gestión de Rutinas**: Crea rutinas diarias/semanales/mensuales (comida, paseo, medicación, etc.) y márcalas como completadas.
- **Notificaciones Inteligentes**: Recibe recordatorios automáticos de rutinas y eventos importantes.
- **Calendario de Eventos**: Administra y visualiza citas, cumpleaños y otros eventos relevantes.
- **Informes y Gráficos**: Visualiza gráficas de peso, altura y salud de tus mascotas.
- **Gestión de Contactos Veterinarios**: Guarda y accede rápidamente a tus veterinarios y clínicas de confianza.
- **Panel Principal (Dashboard)**: Resumen de próximas rutinas, eventos y notificaciones recientes.

---

## Tecnologías utilizadas

- **Backend**: PHP 7.4+
- **Frontend**: JavaScript, CSS, HTML5
- **Base de datos**: MySQL/MariaDB
- **Librerías**: Charts.js (gráficas), Bootstrap (opcional, para UI)
- **Otros**: AJAX para actualización en tiempo real (notificaciones/calendario)

---

## Estructura de Carpetas

```
petday-mejorado/
├── config/               # Configuración de base de datos y constantes
├── css/                  # Estilos principales
├── images/               # Recursos gráficos
├── js/                   # Scripts de frontend (app.js, charts.js)
├── php/                  # Lógica de aplicación (mascotas, rutinas, auth, eventos, reportes)
├── docs/                 # Documentación y manuales
├── esquema_base_de_datos.txt # Esquema de la base de datos
├── MANUAL_USUARIO.md     # Manual de Usuario
└── README.md             # Este archivo
```

---

## Instalación y Configuración

1. **Clona el repositorio:**
   ```bash
   git clone https://github.com/rosalas92/petday-mejorado.git
   ```
2. **Configura la base de datos:**
   - Crea una base de datos MySQL/MariaDB.
   - Importa el archivo `esquema_base_de_datos.txt`.
   - Actualiza las credenciales en `/config/config.php` según tu entorno.

3. **Permisos:**
   - Da permisos de escritura a las carpetas necesarias (`uploads/`, `images/`).

4. **Configura el servidor web:**  
   - Apunta el DocumentRoot a la raíz del proyecto.
   - Asegúrate de que PHP y las extensiones necesarias estén instaladas.

5. **Accede a la URL de tu servidor y realiza el registro del primer usuario.**

---

## Esquema de la Base de Datos

Las tablas principales incluyen: `usuarios`, `mascotas`, `rutinas`, `seguimiento_actividades`, `eventos`, `notificaciones`, `medidas_mascota`, `cartilla_sanitaria`, `historial_medico`, `contactos_veterinarios`.

Consulta el archivo `esquema_base_de_datos.txt` para detalles completos.

---

## Capturas de pantalla

- Panel principal con resumen de mascotas y próximas rutinas.
- Calendario visual de eventos y rutinas.
- Reportes y gráficas de salud.
- Gestión de mascotas y contactos veterinarios.

---

## Posibles mejoras futuras

- API RESTful para integración con apps móviles.
- Notificaciones push vía web/móvil.
- Soporte multiusuario avanzado.
- Integración con servicios veterinarios externos.
- Más opciones de personalización y reportes.

---

## Contribuciones

¡Las contribuciones son bienvenidas! Puedes abrir issues o pull requests para sugerir mejoras o reportar errores.

---

## Licencia

MIT

---

## Autor

- [rosalas92](https://github.com/rosalas92)

---

## Enlaces útiles

- [Manual de Usuario](./MANUAL_USUARIO.md)
- [Esquema Base de Datos](./esquema_base_de_datos.txt)
- [Repositorio en GitHub](https://github.com/rosalas92/petday-mejorado)
