/**
 * PetDay - JavaScript Principal
 * Funcionalidades interactivas para la aplicación
 */

console.log('app.js cargado y ejecutándose');

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {

    // Lógica para el menú desplegable del usuario
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        // No se necesita JS adicional si se usa :hover en CSS
        // Pero si se prefiere click, se añadiría aquí
    }

    // Lógica para la campana de notificaciones
    const notificationBell = document.getElementById('notificationBell');
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    const notificationCount = document.getElementById('notificationCount');

    if (notificationBell && notificationsDropdown && notificationCount) {
        notificationBell.addEventListener('click', (e) => {
            e.stopPropagation(); // Evita que el clic se propague al documento
            notificationsDropdown.classList.toggle('open');
            // Marcar notificaciones como leídas al abrir el dropdown (opcional)
            markAllNotificationsAsRead();
        });

        // Cerrar el dropdown si se hace clic fuera de él
        document.addEventListener('click', (e) => {
            if (notificationsDropdown.classList.contains('open') && !notificationBell.contains(e.target) && !notificationsDropdown.contains(e.target)) {
                notificationsDropdown.classList.remove('open');
            }
        });

        // Función para obtener y mostrar notificaciones
        function fetchNotifications() {
            fetch('/petday/php/notifications/get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Notificaciones obtenidas:', data.notifications);
                        notificationCount.textContent = data.unread_count;
                        notificationsDropdown.innerHTML = ''; // Limpiar notificaciones existentes

                        if (data.notifications.length === 0) {
                            notificationsDropdown.innerHTML = '<div class="notification-item">No hay notificaciones nuevas.</div>';
                        } else {
                            data.notifications.forEach(notification => {
                                const notificationItem = document.createElement('div');
                                notificationItem.classList.add('notification-item');
                                if (notification.leida === 0) {
                                    notificationItem.classList.add('unread');
                                } else {
                                    notificationItem.classList.add('read');
                                }
                                notificationItem.innerHTML = `
                                    <div class="notification-content">
                                        <strong>${notification.titulo}</strong><br>
                                        <small>${notification.mensaje}</small><br>
                                        <small class="text-muted">${new Date(notification.fecha_envio).toLocaleString()}</small>
                                    </div>
                                    <div class="notification-actions">
                                        ${notification.tipo === 'rutina' && notification.id_entidad_relacionada ? `<button class="btn btn-xs btn-outline-success mark-routine-complete-btn ${notification.leida === 1 ? 'btn-notification-completed' : ''}" data-notification-id="${notification.id_notificacion}" data-routine-id="${notification.id_entidad_relacionada}">Completada</button>` : ''}
                                        <button class="btn btn-xs btn-danger delete-notification-btn" data-id="${notification.id_notificacion}">Eliminar</button>
                                    </div>
                                `;
                                notificationsDropdown.appendChild(notificationItem);
                            });

                            // Añadir event listeners a los nuevos botones
                            document.querySelectorAll('.mark-routine-complete-btn').forEach(button => {
                                button.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    const notificationId = this.dataset.notificationId;
                                    const routineId = this.dataset.routineId;
                                    markRoutineAsCompleteFromNotification(notificationId, routineId);
                                });
                            });

                            document.querySelectorAll('.delete-notification-btn').forEach(button => {
                                button.addEventListener('click', function(e) {
                                    e.stopPropagation();
                                    const notificationId = this.dataset.id;
                                    deleteNotification(notificationId);
                                });
                            });
                        }
                    } else {
                        console.error('Error al obtener notificaciones:', data.message);
                    }
                })
                .catch(error => console.error('Error de red al obtener notificaciones:', error));
        }

        // Función para marcar una notificación como completada
        function markRoutineAsCompleteFromNotification(notificationId, routineId) {
            fetch('/petday/php/notifications/mark_routine_complete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    notification_id: notificationId,
                    routine_id: routineId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    fetchNotifications(); // Recargar notificaciones para actualizar el estado
                    updateRoutineStatusIcons(); // Actualizar el estado visual de las rutinas
                } else {
                    alert('Error: ' + data.message); // Mostrar error al usuario
                }
            })
            .catch(error => console.error('Error de red al marcar la rutina como completada:', error));
        }

        // Función para eliminar una notificación
        function deleteNotification(notificationId) {
            if (confirm('¿Estás seguro de que quieres eliminar esta notificación?')) {
                fetch('/petday/php/notifications/delete_notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `notification_id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log(data.message);
                        fetchNotifications(); // Recargar notificaciones para actualizar la lista
                    } else {
                        console.error('Error al eliminar notificación:', data.message);
                    }
                })
                .catch(error => console.error('Error de red al eliminar notificación:', error));
            }
        }

        // Función para verificar y crear notificaciones de rutinas próximas
        function checkUpcomingRoutines() {
            fetch('/petday/php/notifications/check_upcoming_routines.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.notifications_created > 0) {
                            console.log(`Se crearon ${data.notifications_created} notificaciones de rutina.`);
                            fetchNotifications(); // Recargar notificaciones si se crearon nuevas
                        }
                    } else {
                        console.error('Error al verificar rutinas próximas:', data.message);
                    }
                })
                .catch(error => console.error('Error de red al verificar rutinas próximas:', error));
        }

        // Obtener notificaciones y verificar rutinas próximas al cargar la página y cada cierto tiempo
        fetchNotifications();
        checkUpcomingRoutines(); // Ejecutar al inicio
        setInterval(() => {
            fetchNotifications();
            checkUpcomingRoutines();
        }, 60000); // Actualizar cada 1 minuto
    }

});

initializeApp();

    // Lógica para el botón flotante (FAB)
    const fab = document.querySelector('.fab');
    const fabMenu = document.querySelector('.fab-menu');
    if (fab) {
        fab.addEventListener('click', () => {
            fab.classList.toggle('open');
            fabMenu.classList.toggle('open');
        });
    }

    // Lógica para cerrar modales (general)
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const closeButton = modal.querySelector('.modal-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                modal.classList.remove('open');
            });
        }
        // Cerrar al hacer clic fuera del contenido del modal
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('open');
            }
        });
    });

    // Lógica para abrir el modal de detalles de eventos/rutinas
    const dayDetailsModal = document.getElementById('dayDetailsModal');
    const modalDayTitle = document.getElementById('modalDayTitle');
    const modalEventsList = document.getElementById('modalEventsList');
    
    // Seleccionar todos los elementos que pueden abrir el modal
    const clickableEvents = document.querySelectorAll('.calendar-day, .clickable-event');

    clickableEvents.forEach(element => {
        element.addEventListener('click', function() {
            // Solo abrir modal en vista responsive (ej. menos de 768px de ancho)
            // O si es un elemento .clickable-event (para el perfil de mascota)
            if (window.innerWidth <= 768 || this.classList.contains('clickable-event')) {
                let events = [];
                let title = '';

                if (this.classList.contains('calendar-day')) {
                    // Lógica para el calendario (ya existente)
                    const date = this.dataset.date;

                    events = JSON.parse(this.dataset.events);
                    const dateObj = new Date(date + 'T00:00:00');
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    title = dateObj.toLocaleDateString('es-ES', options);
                } else if (this.classList.contains('clickable-event')) {
                    // Lógica para el perfil de mascota
                    const eventData = JSON.parse(this.dataset.event);
                    events = [eventData]; // El perfil de mascota pasa un solo evento/rutina

                    if (eventData.tipo_actividad) {
                        // Es una rutina
                        title = `Detalles de Rutina: ${eventData.nombre_actividad}`;
                    } else if (eventData.titulo) {
                        // Es un evento
                        title = `Detalles de Evento: ${eventData.titulo}`;
                    }
                }

                modalDayTitle.textContent = title;
                modalEventsList.innerHTML = ''; // Limpiar lista anterior

                if (events.length === 0) {
                    modalEventsList.innerHTML = '<p class="no-events-msg">No hay actividades programadas para este día.</p>';
                } else {
                    events.forEach(event => {
                        const eventItem = document.createElement('div');
                        eventItem.classList.add('event-item');
                        eventItem.classList.add(event.tipo_actividad ? 'routine-event' : 'calendar-event');

                        const icon = event.tipo_actividad ? getRoutineIcon(event.tipo_actividad) : '🏥';
                        const time = event.hora_programada ? event.hora_programada.substring(0, 5) : (event.fecha_evento ? event.fecha_evento.substring(11, 16) : '');
                        const name = event.nombre_actividad ? event.nombre_actividad : event.titulo;
                        const petName = event.pet_name ? ` (${event.pet_name})` : '';
                        const description = event.descripcion ? `<p class="event-description">${event.descripcion}</p>` : '';
                        const dateInfo = event.fecha_evento ? `<small class="text-muted">Fecha: ${new Date(event.fecha_evento).toLocaleDateString('es-ES')}</small>` : '';
                        const daysOfWeek = event.dias_semana ? `<small class="text-muted">Días: ${event.dias_semana.replace(/,/g, ', ')}</small>` : '';

                        eventItem.innerHTML = `
                            <span class="event-icon">${icon}</span>
                            <div class="event-details">
                                <span class="event-time">${time}</span>
                                <span class="event-title">${name}${petName}</span>
                                ${dateInfo}
                                ${daysOfWeek}
                                ${description}
                            </div>
                        `;
                        modalEventsList.appendChild(eventItem);
                    });
                }

                dayDetailsModal.classList.add('open');
            }
        });
    });

    // Helper function to get routine icon (needs to match PHP's getActivityIcon)
    function getRoutineIcon(activityType) {
        switch (activityType) {
            case 'paseo': return '🐾';
            case 'comida': return '🍖';
            case 'medicacion': return '💊';
            case 'juego': return '🎾';
            case 'higiene': return '🛁';
            case 'entrenamiento': return '🧠';
            default: return '📝';
        }
    }

    // Función para actualizar los iconos de estado de las rutinas
    function updateRoutineStatusIcons() {
        const routineStatusIcons = document.querySelectorAll('.routine-status-icon');
        routineStatusIcons.forEach(iconElement => {
            const routineId = iconElement.dataset.routineId;
            if (routineId) {
                fetch(`/petday/php/routines/get_routine_status.php?id=${routineId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.status) {
                            let statusIcon = '';
                            switch (data.status) {
                                case 'completed':
                                    statusIcon = '✅';
                                    break;
                                case 'pending':
                                    statusIcon = '⏳';
                                    break;
                                case 'overdue':
                                    statusIcon = '⚠️';
                                    break;
                                default:
                                    statusIcon = '';
                            }
                            iconElement.innerHTML = statusIcon;
                        }
                    })
                    .catch(error => console.error('Error fetching routine status:', error));
            }
        });
    }

    // Llamar a la función para actualizar los iconos al cargar la página
    updateRoutineStatusIcons();

    // Lógica para el selector de vista del calendario en pet_profile.php
    const calendarViewSelector = document.getElementById('calendarViewSelector');
    if (calendarViewSelector) {
        calendarViewSelector.value = new URLSearchParams(window.location.search).get('view') || 'month';
        calendarViewSelector.addEventListener('change', function() {
            const petId = new URLSearchParams(window.location.search).get('id');
            const selectedView = this.value;
            window.location.href = `pet_profile.php?id=${petId}&view=${selectedView}`;
        });
    }

    // Función para marcar todas las notificaciones como leídas
    function markAllNotificationsAsRead() {
        fetch('/petday/php/notifications/mark_all_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(data.message);
                fetchNotifications(); // Volver a cargar las notificaciones para actualizar el contador
            } else {
                console.error('Error al marcar notificaciones como leídas:', data.message);
            }
        })
        .catch(error => console.error('Error de red al marcar notificaciones como leídas:', error));
    }

/**
 * Inicializar la aplicación principal
 */
function initializeApp() {
    // Actualizar la fecha y hora actual
    updateCurrentDateTime();

    // Configurar la funcionalidad de marcar rutina como completada
    setupMarkRoutineComplete();

    // Configurar el menú desplegable del usuario
    initializeUserDropdown();

    console.log('✅ PetDay iniciado correctamente');
}

/**
 * Configurar botón flotante (FAB)
 */
function initializeFAB() {
    const fabMain = document.getElementById('fabMain');
    const fabMenu = document.getElementById('fabMenu');
    
    if (fabMain && fabMenu) {
        fabMain.addEventListener('click', (e) => {
            e.stopPropagation();
            fabMenu.classList.toggle('open');
            fabMain.classList.toggle('open');
        });

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (fabMenu.classList.contains('open') && !fabMain.contains(e.target) && !fabMenu.contains(e.target)) {
                fabMenu.classList.remove('open');
                fabMain.classList.remove('open');
            }
        });
    }
}

/**
 * Actualizar fecha y hora actual
 */
function updateCurrentDateTime() {
    const dateElement = document.getElementById('current-date');
    if (dateElement) {
        const now = new Date();
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        dateElement.textContent = now.toLocaleDateString('es-ES', options);
    }
}

/**
 * Configura los event listeners para marcar rutinas como completadas.
 */
function setupMarkRoutineComplete() {
    const buttons = document.querySelectorAll('.mark-complete-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const routineId = this.dataset.routineId;
            const routineItem = this.closest('.routine-item');

            fetch('/petday/php/routines/mark_complete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', // Cambiado a form-urlencoded
                },
                body: `routine_id=${routineId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (routineItem) {
                        routineItem.classList.remove('pending', 'overdue');
                        routineItem.classList.add('completed');
                        // Reemplazar el botón con el checkmark
                        const statusDiv = routineItem.querySelector('.routine-status');
                        if (statusDiv) {
                            statusDiv.innerHTML = '<span class="status-badge completed">✅</span>';
                        }
                    }
                } else {
                    alert('Error al marcar la rutina como completada: ' + (data.message || ''));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al intentar marcar la rutina.');
            });
        });
    });
}

/**
 * Configura el menú desplegable del usuario.
 */
function initializeUserDropdown() {
    const userDropdown = document.querySelector('.user-dropdown');
    const userBtn = userDropdown ? userDropdown.querySelector('.user-btn') : null;
    const dropdownContent = userDropdown ? userDropdown.querySelector('.dropdown-content') : null;

    if (userBtn && dropdownContent) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Evita que el clic se propague al documento
            userDropdown.classList.toggle('open');
        });

        // Cerrar el dropdown si se hace clic fuera de él
        document.addEventListener('click', (e) => {
            if (userDropdown.classList.contains('open') && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('open');
            }
        });
    }
}