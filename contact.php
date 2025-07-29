<?php
require_once 'php/includes/header.php';
?>

<main class="container my-5 contact-page">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title text-center">Contacto</h1>
                </div>
                <div class="card-body">
                    <p class="text-center">¿Tienes alguna pregunta o comentario? Rellena el formulario y te responderemos lo antes posible.</p>
                    
                    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                        <div class="alert alert-success" role="alert">
                            ¡Gracias! Tu mensaje ha sido enviado con éxito.
                        </div>
                    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
                        <div class="alert alert-danger" role="alert">
                            Hubo un problema al enviar tu mensaje. Por favor, inténtalo de nuevo. <br>
                            <?php if (isset($_GET['message'])) echo htmlspecialchars($_GET['message']); ?>
                        </div>
                    <?php endif; ?>

                    <form id="contact-form" action="php/contact/send_contactanos.php" method="POST">
                        <div class="mb-3">
                            <label for="contact_name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="contact_name" name="contact_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_subject" class="form-label">Asunto</label>
                            <input type="text" class="form-control" id="contact_subject" name="contact_subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_message" class="form-label">Mensaje</label>
                            <textarea class="form-control" id="contact_message" name="contact_message" rows="5" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once 'php/includes/footer.php';
?>
