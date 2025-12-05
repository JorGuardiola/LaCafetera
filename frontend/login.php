<?php
// frontend/login.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Inicializar mensajes
$error_message = '';
$success_message = '';

// Ruta base dinámica (usada para las imágenes, debe coincidir con la subcarpeta del proyecto en htdocs).
// ¡ATENCIÓN! Si tu proyecto se accede como http://localhost/LaCafetera/, esta ruta es correcta.
$base_path = '/LaCafetera'; 

// --- Lógica de Procesamiento del Formulario de Login ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger y sanear los datos del formulario
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? ''; // La contraseña sin sanear (será verificada con hash)

    if (!$email) {
        $error_message = "El formato del email es inválido.";
    } elseif (empty($password)) {
        $error_message = "La contraseña es obligatoria.";
    } else {
        try {
            // 2. Preparar y ejecutar la consulta a la base de datos
            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // 3. Verificar las credenciales
            if ($user && password_verify($password, $user['password_hash'])) {
                // 4. Login Exitoso: Iniciar sesión y Redirigir
                // *** Configuramos las variables de sesión ***
                $_SESSION['user_id'] = $user['id']; 
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                header('Location: index.php');
                exit;
                
            } else {
                // 5. Login Fallido
                $error_message = "Email o contraseña incorrectos.";
            }

        } catch (PDOException $e) {
            // Error de consulta
            $error_message = "Ocurrió un error en el servidor. Inténtelo de nuevo.";
            // Puedes loguear $e->getMessage() para debug
        }
    }
}
?>
ob_start(); 
?>
    <div class="login-container">
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)) : ?>
            <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="login-form">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Contraseña:</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" required>
                <button type="button" id="togglePassword" class="toggle-password">
                    <i data-lucide="eye"></i>
                </button>
            </div>

            <button type="submit" class="login-btn">Ingresar</button>
        </form>

        <p class="register-link">
            ¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a>
        </p>
    </div>
<?php
// Guardamos el HTML del formulario en la variable $heroContent
$heroContent = ob_get_clean(); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $heroTitle ?> | La Cafetera</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/templates/header.php'; ?>

    <main class="login-main">
        <?php include __DIR__ . '/templates/hero.php'; ?>
    </main>

    <?php include __DIR__ . '/templates/footer.php'; ?>
    
    <script>
        // Inicializar iconos de Lucide (si está disponible)
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Lógica para mostrar/ocultar contraseña
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function (e) {
                const passwordInput = document.getElementById('password');
                if (!passwordInput) return;
                const icon = e.currentTarget.querySelector('i');

                // Alternar el tipo de input y el icono
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    if (icon) icon.setAttribute('data-lucide', 'eye-off');
                } else {
                    passwordInput.type = 'password';
                    if (icon) icon.setAttribute('data-lucide', 'eye');
                }
                if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            });
        }
    </script>
</body>
</html>
