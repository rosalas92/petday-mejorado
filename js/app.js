/**
 * PetDay - JavaScript Principal
 * Funcionalidades interactivas para la aplicación
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('app.js cargado y ejecutándose');

    //--------------------------------------------------
    // INICIALIZACIÓN PRINCIPAL
    //--------------------------------------------------
    initializeApp();

    function initializeApp() {
        initializeNotificationSystem();
        initializeUserDropdown();
        initializeFAB();
        initializeModals();
        initializeClickableEvents();
        updateCurrentDateTime();
        setupMarkRoutineComplete();
        updateRoutineStatusIcons();
        initializeCalendarViewSelector();
        console.log('✅ PetDay iniciado correctamente');
    }

    //--------------------------------------------------
    // SISTEMA DE NOTIFICACIONES
    //--------------------------------------------------
    function initializeNotificationSystem() {
        const notificationBell = document.getElementById('notificationBell');
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        const notificationCount = document.getElementById('notificationCount');

        if (!notificationBell || !notificationsDropdown || !notificationCount) {
            return;
        }

        // Función para obtener y mostrar notificaciones
        function fetchNotifications() {
            fetch('/petday/php/notifications/get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Error al obtener notificaciones:', data.message);
                        return;
                    }

                    console.log('Notificaciones obtenidas:', data.notifications);
                    notificationCount.textContent = data.unread_count;
                    notificationsDropdown.innerHTML = ''; // Limpiar

                    if (data.notifications.length === 0) {
                        notificationsDropdown.innerHTML = '<div class="notification-item">No tienes notificaciones.</div>';
                    } else {
                        data.notifications.forEach(notification => {
                            const item = document.createElement('div');
                            item.className = `notification-item ${notification.leida === 0 ? 'unread' : 'read'}`;
                            item.innerHTML = `
                                <div class="notification-content">
                                    <strong>${notification.titulo}</strong><br>
                                    <small>${notification.mensaje}</small><br>
                                    <small class="text-muted">${new Date(notification.fecha_envio).toLocaleString()}</small>
                                </div>
                                <div class="notification-actions">
                                    ${notification.tipo === 'rutina' && notification.id_entidad_relacionada ? `<button class="btn btn-xs btn-outline-success mark-routine-complete-btn ${notification.leida === 1 ? 'btn-notification-completed' : ''}" data-notification-id="${notification.id_notificacion}" data-routine-id="${notification.id_entidad_relacionada}">Completada</button>` : ''}
                                    <button class="btn btn-xs btn-danger delete-notification-btn" data-id="${notification.id_notificacion}">Eliminar</button>
                                </div>`;
                            notificationsDropdown.appendChild(item);
                        });

                        const deleteAllButton = document.createElement('button');
                        deleteAllButton.className = 'btn btn-xs btn-danger delete-all-notifications-btn';
                        deleteAllButton.textContent = 'Borrar Notificaciones';
                        notificationsDropdown.appendChild(deleteAllButton);
                    }
                })
                .catch(error => console.error('Error de red al obtener notificaciones:', error));
        }

        // Función para marcar una rutina como completada desde una notificación
        function markRoutineAsCompleteFromNotification(notificationId, routineId) {
            fetch('/petday/php/notifications/mark_routine_complete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: notificationId, routine_id: routineId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    fetchNotifications();
                    updateRoutineStatusIcons();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error de red al marcar la rutina como completada:', error));
        }

        // Función para eliminar una notificación
        function deleteNotification(notificationId) {
            if (!confirm('¿Estás seguro de que quieres eliminar esta notificación?')) return;

            fetch('/petday/php/notifications/delete_notification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `notification_id=${notificationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    fetchNotifications();
                } else {
                    console.error('Error al eliminar notificación:', data.message);
                }
            })
            .catch(error => console.error('Error de red al eliminar notificación:', error));
        }

        // Función para eliminar TODAS las notificaciones
        function deleteAllNotifications() {
            if (!confirm('¿Estás seguro de que quieres eliminar todas las notificaciones?')) return;

            fetch('/petday/php/notifications/delete_all_notifications.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    notificationsDropdown.innerHTML = '<div class="notification-item">No tienes notificaciones.</div>';
                    notificationCount.textContent = '0';
                } else {
                    console.error('Error al eliminar todas las notificaciones:', data.message);
                }
            })
            .catch(error => console.error('Error de red al eliminar todas las notificaciones:', error));
        }

        // Función para marcar todas las notificaciones como leídas
        function markAllNotificationsAsRead() {
            fetch('/petday/php/notifications/mark_all_notifications_read.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(data.message);
                    // No es necesario recargar, solo actualizar el contador visualmente
                    notificationCount.textContent = '0';
                    // Y quitar el estilo 'unread'
                    notificationsDropdown.querySelectorAll('.unread').forEach(n => n.classList.remove('unread'));
                } else {
                    console.error('Error al marcar notificaciones como leídas:', data.message);
                }
            })
            .catch(error => console.error('Error de red al marcar notificaciones como leídas:', error));
        }

        // Función para verificar y crear notificaciones de rutinas próximas
        function checkUpcomingRoutines() {
            fetch('/petday/php/notifications/check_upcoming_routines.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.notifications_created > 0) {
                        console.log(`Se crearon ${data.notifications_created} notificaciones de rutina.`);
                        fetchNotifications();
                    }
                })
                .catch(error => console.error('Error de red al verificar rutinas próximas:', error));
        }

        // Event Listeners
        notificationBell.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationsDropdown.classList.toggle('open');
            if (notificationsDropdown.classList.contains('open')) {
                markAllNotificationsAsRead();
            }
        });

        document.addEventListener('click', (e) => {
            if (notificationsDropdown.classList.contains('open') && !notificationBell.contains(e.target) && !notificationsDropdown.contains(e.target)) {
                notificationsDropdown.classList.remove('open');
            }
        });

        notificationsDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            const target = e.target;
            if (target.matches('.mark-routine-complete-btn')) {
                markRoutineAsCompleteFromNotification(target.dataset.notificationId, target.dataset.routineId);
            } else if (target.matches('.delete-notification-btn')) {
                deleteNotification(target.dataset.id);
            } else if (target.matches('.delete-all-notifications-btn')) {
                deleteAllNotifications();
            }
        });

        // Carga inicial y ciclo de actualización
        fetchNotifications();
        checkUpcomingRoutines();
        setInterval(() => {
            fetchNotifications();
            checkUpcomingRoutines();
        }, 60000);
    }

    //--------------------------------------------------
    // OTROS COMPONENTES UI
    //--------------------------------------------------

    function initializeUserDropdown() {
        const userDropdown = document.querySelector('.user-dropdown');
        if (!userDropdown) return;
        userDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('open');
        });
        document.addEventListener('click', (e) => {
            if (userDropdown.classList.contains('open') && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('open');
            }
        });
    }

    function initializeFAB() {
        const fabMain = document.getElementById('fabMain');
        const fabMenu = document.getElementById('fabMenu');
        if (!fabMain || !fabMenu) return;
        fabMain.addEventListener('click', (e) => {
            e.stopPropagation();
            fabMenu.classList.toggle('open');
            fabMain.classList.toggle('open');
        });
        document.addEventListener('click', (e) => {
            if (fabMenu.classList.contains('open') && !fabMain.contains(e.target)) {
                fabMenu.classList.remove('open');
                fabMain.classList.remove('open');
            }
        });
    }

    function initializeModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            const closeButton = modal.querySelector('.modal-close');
            if (closeButton) {
                closeButton.addEventListener('click', () => modal.classList.remove('open'));
            }
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.remove('open');
            });
        });
    }

    function initializeClickableEvents() {
        const dayDetailsModal = document.getElementById('dayDetailsModal');
        if (!dayDetailsModal) return;

        const modalDayTitle = document.getElementById('modalDayTitle');
        const modalEventsList = document.getElementById('modalEventsList');
        const editDayButton = document.getElementById('editDayButton');

        let currentEvents = []; // Variable para mantener los eventos del día seleccionado

        document.querySelectorAll('.calendar-day').forEach(element => {
            element.addEventListener('click', function() {
                try {
                    currentEvents = JSON.parse(this.dataset.events || '[]');
                } catch (e) {
                    console.error("Error al parsear los datos de eventos:", e);
                    modalEventsList.innerHTML = '<p class="no-events-msg">Error al cargar los datos de este día.</p>';
                    dayDetailsModal.classList.add('open');
                    return;
                }

                const date = this.dataset.date;
                const title = new Date(date + 'T00:00:00').toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

                modalDayTitle.textContent = title;
                modalEventsList.innerHTML = '';

                if (currentEvents.length === 0) {
                    modalEventsList.innerHTML = '<p class="no-events-msg">No hay actividades programadas.</p>';
                } else {
                    currentEvents.forEach(event => {
                        const item = document.createElement('div');
                        item.className = 'event-item ' + (event.tipo_actividad ? 'routine-event' : 'calendar-event');
                        
                        const icon = event.tipo_actividad ? getRoutineIcon(event.tipo_actividad) : '🏥';
                        const time = event.hora_programada ? event.hora_programada.substring(0, 5) : (event.fecha_evento ? new Date(event.fecha_evento).toTimeString().substring(0, 5) : '');
                        const name = event.nombre_actividad || event.titulo;
                        const petName = event.pet_name ? ` (${event.pet_name})` : '';
                        const description = event.descripcion ? `<p class="event-description">${event.descripcion}</p>` : '';

                        item.innerHTML = `
                            <span class="event-icon">${icon}</span>
                            <div class="event-details">
                                <span class="event-time">${time}</span>
                                <span class="event-title">${name}${petName}</span>
                                ${description}
                            </div>
                        `;
                        modalEventsList.appendChild(item);
                    });
                }
                
                if(editDayButton) {
                    editDayButton.style.display = 'inline-block';
                }

                dayDetailsModal.classList.add('open');
            });
        });

        if (editDayButton) {
            editDayButton.addEventListener('click', () => {
                const petsWithEvents = [...new Map(currentEvents.map(e => [e.pet_id, e])).values()];

                if (petsWithEvents.length === 1) {
                    window.location.href = `/petday/php/pets/pet_profile.php?id=${petsWithEvents[0].pet_id}`;
                } else {
                    modalDayTitle.textContent = 'Editar Rutinas';
                    modalEventsList.innerHTML = '<p>Selecciona una mascota para editar sus rutinas:</p>';
                    const petList = document.createElement('div');
                    petList.className = 'pet-selection-list';
                    petsWithEvents.forEach(pet => {
                        const petLink = document.createElement('a');
                        petLink.href = `/petday/php/pets/pet_profile.php?id=${pet.pet_id}`;
                        petLink.className = 'btn btn-outline';
                        petLink.textContent = pet.pet_name;
                        petList.appendChild(petLink);
                    });
                    modalEventsList.appendChild(petList);
                    editDayButton.style.display = 'none';
                }
            });
        }
    }

    function getRoutineIcon(activityType) {
        const icons = {
            'paseo': '🐾',
            'comida': '🍖',
            'medicacion': '💊',
            'juego': '🎾',
            'higiene': '🛁',
            'entrenamiento': '🧠',
            'otro': '📝'
        };
        return icons[activityType] || '📝';
    }

    function updateCurrentDateTime() {
        const dateElement = document.getElementById('current-date');
        if (dateElement) {
            dateElement.textContent = new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
    }

    function setupMarkRoutineComplete() {
        document.body.addEventListener('click', function(e) {
            if (e.target.matches('.mark-complete-btn')) {
                const routineId = e.target.dataset.routineId;
                const routineItem = e.target.closest('.routine-item');
                fetch('/petday/php/routines/mark_complete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `routine_id=${routineId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && routineItem) {
                        routineItem.classList.remove('pending', 'overdue');
                        routineItem.classList.add('completed');
                        const statusDiv = routineItem.querySelector('.routine-status');
                        if (statusDiv) statusDiv.innerHTML = '<span class="status-badge completed">✅</span>';
                    } else if (!data.success) {
                        alert('Error: ' + (data.message || ''));
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    }

    function updateRoutineStatusIcons() {
        document.querySelectorAll('.routine-status-icon').forEach(iconElement => {
            const routineId = iconElement.dataset.routineId;
            if (!routineId) return;
            fetch(`/petday/php/routines/get_routine_status.php?id=${routineId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.status) {
                        const icons = { completed: '✅', pending: '⏳', overdue: '⚠️' };
                        iconElement.innerHTML = icons[data.status] || '';
                    }
                })
                .catch(error => console.error('Error fetching routine status:', error));
        });
    }

    function initializeCalendarViewSelector() {
        const selector = document.getElementById('calendarViewSelector');
        if (selector) {
            const params = new URLSearchParams(window.location.search);
            selector.value = params.get('view') || 'month';
            selector.addEventListener('change', function() {
                const petId = params.get('id');
                // Comprobar en qué página estamos para construir la URL correcta
                if (window.location.pathname.includes('pet_profile.php')) {
                    window.location.href = `pet_profile.php?id=${petId}&view=${this.value}`;
                } else {
                    window.location.href = `calendar.php?view=${this.value}`;
                }
            });
        }
    }
});
