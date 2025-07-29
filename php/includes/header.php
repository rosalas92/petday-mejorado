<?php
// Asegúrate de que config.php y functions.php estén incluidos antes de este archivo
// en la página principal que lo llama.

// Estas variables deben estar definidas en la página que incluye este header
// $isLoggedIn = isset($_SESSION['user_id']);
// $user = getUserById($_SESSION['user_id']);

?>

<header class="main-header">
    <div class="container">
        <div class="header-content">
            <a href="<?php echo URL_ADMIN; ?>/index.php" class="logo">
                <img src="<?php echo URL_ADMIN; ?>/images/logotipo-lateral.png" alt="PetDay Logo" style="height: 90px;">
            </a>
            
            <nav class="main-nav">
                <?php if ($isLoggedIn): ?>
                    <div class="user-menu">
                        <span class="welcome-text">Hola, <?php echo htmlspecialchars($user['nombre_completo']); ?></span>
                        <div class="notification-bell">
                            <img src="<?php echo URL_ADMIN; ?>/images/campana.png" alt="Notificaciones" class="bell-icon" id="notificationBell">
                            <span class="notification-count" id="notificationCount">0</span>
                            <div class="notifications-dropdown" id="notificationsDropdown">
                                <!-- Las notificaciones se cargarán aquí dinámicamente -->
                                <div class="notification-item">No hay notificaciones nuevas.</div>
                            </div>
                        </div>
                        <div class="user-dropdown">
                            <button class="user-btn">👤</button>
                            <div class="dropdown-content">
                                <a href="<?php echo URL_ADMIN; ?>/php/pets/manage_pets.php">Mis Mascotas</a>
                                <a href="<?php echo URL_ADMIN; ?>/php/reports/reports.php">Reportes</a>
                                <a href="<?php echo URL_ADMIN; ?>/php/calendar/calendar.php">Calendario</a>
                                <a href="<?php echo URL_ADMIN; ?>/php/vet_contacts/manage_vet_contacts.php">Veterinarios</a>
                                <a href="<?php echo URL_ADMIN; ?>/php/auth/logout.php">Cerrar Sesión</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="<?php echo URL_ADMIN; ?>/php/auth/login.php" class="btn btn-outline">Iniciar Sesión</a>
                        <a href="<?php echo URL_ADMIN; ?>/php/auth/register.php" class="btn btn-primary">Registrarse</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
