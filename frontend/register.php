<?php
// frontend/register.php
session_start();
require_once __DIR__ . '/../db/connection.php';

/* =========================
   LÓGICA DE REGISTRO
========================= */

$error_message = '';
$nombre = '';
$apellidos = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre   = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!$nombre || !$apellidos || !$email || !$password || !$password_confirm) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del email no es válido.";
    } elseif (strlen($password) < 8) {
        $error_message = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Las contraseñas no coinciden.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error_message = "Este correo electrónico ya está registrado.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO usuarios (nombre, apellido, email, password_hash, fecha_registro)
                     VALUES (?, ?, ?, ?, NOW())"
                );

                if ($stmt->execute([$nombre, $apellidos, $email, $hash])) {

                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_nombre'] = $nombre;
                    $_SESSION['mensaje_exito'] = "¡Cuenta creada! Has iniciado sesión.";

                    header('Location: ' . BASE_URL . '/frontend/index.php');
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error del sistema. Inténtalo más tarde.";
        }
    }
}

/* =========================
   VARIABLES PARA HERO
========================= */

$bgClass = 'bg-registro';
$heroTitle = '';
$heroSubtitle = '';
$heroButtonText = '';
$heroButtonLink = '';

/* =========================
   CONTENIDO DERECHA (FORM)
========================= */
ob_start();
?>

<div class="login-box register-box">

    <h3 class="login-title">Crea tu cuenta</h3>

    <?php if ($error_message): ?>
        <div class="alert error">
            <i data-lucide="alert-circle"></i>
            <p><?= htmlspecialchars($error_message) ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" class="register-form">

        <div class="input-group-row">
            <div class="input-group half-width">
                <label class="input-label small-label">Nombre</label>
                <input type="text" name="nombre" class="form-input" required value="<?= htmlspecialchars($nombre) ?>">
            </div>

            <div class="input-group half-width">
                <label class="input-label small-label">Apellidos</label>
                <input type="text" name="apellidos" class="form-input" required value="<?= htmlspecialchars($apellidos) ?>">
            </div>
        </div>

        <div class="input-group">
            <label class="input-label small-label">Email</label>
            <input type="email" name="email" class="form-input" required value="<?= htmlspecialchars($email) ?>">
        </div>

        <div class="input-group password-field">
            <label class="input-label small-label">Contraseña</label>
            <input type="password" name="password" class="form-input" required>
            <button type="button" class="toggle-password">
                <i data-lucide="eye"></i>
            </button>
        </div>

        <div class="input-group password-field">
            <label class="input-label small-label">Confirmar contraseña</label>
            <input type="password" name="password_confirm" class="form-input" required>
            <button type="button" class="toggle-password">
                <i data-lucide="eye"></i>
            </button>
        </div>

        <button type="submit" class="btn btn-primary btn-acceder btn-register">
            Registrarse <span class="arrow-icon">&rarr;</span>
        </button>

    </form>

    <div class="login-prompt">
        <span>¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/frontend/login.php">Accede</a></span>
    </div>

</div>

<?php
$heroRightContent = ob_get_clean();
?>

<!-- =========================
     HTML FINAL
========================= -->

<?php include __DIR__ . '/templates/header.php'; ?>

<main>
    <?php include __DIR__ . '/templates/hero.php'; ?>
</main>

<?php include __DIR__ . '/templates/footer.php'; ?>

<script>
if (window.lucide) lucide.createIcons();

document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('.form-input');
        const icon = btn.querySelector('[data-lucide]');
        if (!input || !icon) return;

        input.type = input.type === 'password' ? 'text' : 'password';
        icon.setAttribute('data-lucide', input.type === 'password' ? 'eye' : 'eye-off');
        lucide.createIcons();
    });
});
</script>
