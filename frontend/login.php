<?php
// LÍNEA 1: INICIA EL BUFFER DE SALIDA
ob_start(); 

// frontend/login.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Inicializar mensajes
$error_message = '';
$success_message = '';

// INICIO DEL CAMBIO: RECUPERAR MENSAJE DE SESIÓN
if (isset($_SESSION['mensaje_exito'])) {
    $success_message = $_SESSION['mensaje_exito'];
    // Limpiar la variable de sesión para que no se muestre de nuevo al recargar
    unset($_SESSION['mensaje_exito']); 
}

// --- Lógica de Procesamiento del Formulario de Login ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger y sanear los datos del formulario
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email) {
        $error_message = "El formato del email es inválido.";
    } elseif (empty($password)) {
        $error_message = "La contraseña es obligatoria.";
    } else {
        try {
            // 2. Preparar y ejecutar la consulta a la base de datos
            $stmt = $pdo->prepare("SELECT id_usuario, email, password_hash, rol FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // 3. Verificar las credenciales y el hash de la contraseña

            // --- INICIO DEL CAMBIO DE LÓGICA DE VERIFICACIÓN ---
            if ($user) {
                // El email existe en la base de datos
                if (password_verify($password, $user['password_hash'])) {
                    
                    // 4. Login Exitoso: Iniciar sesión y Redirigir
                    $_SESSION['user_id']  = $user['id_usuario']; 
                    $_SESSION['email']    = $user['email'];
                    $_SESSION['rol']      = $user['rol'];   // 👈 ESTA ES LA CLAVE
                    $_SESSION['logged_in'] = true;
                    
                    // === REDIRECCIÓN FINAL  ===
                    ob_end_clean(); // Limpia el buffer antes de enviar la cabecera
                    header('Location:' . BASE_URL . '/frontend/index.php'); // RUTA nueva con BASE_URL
                    exit; 
                    // =================================
                    
                } else { 
                    // 5. Contraseña incorrecta para el email encontrado
                    $error_message = "Email o contraseña incorrectos."; // Mensaje genérico por seguridad
                    
                }
            } else {
                // 5. Email NO encontrado en la base de datos
                // Puedes usar un mensaje genérico o el que solicitaste para orientar:
                $error_message = "Email no registrado, por favor regístrate.";
                // O el más seguro y genérico: $error_message = "Email o contraseña incorrectos.";
            }
            // --- FIN DEL CAMBIO DE LÓGICA DE VERIFICACIÓN ---

        } catch (PDOException $e) {
            // Error de consulta o base de datos
            $error_message = "Ocurrió un error en el servidor. Inténtelo de nuevo.";
            // DESCOMENTA ESTO TEMPORALMENTE para ver el detalle: $error_message .= " Error: " . $e->getMessage(); 
        }
    }
}


// 1. VARIABLES PARA LA PLANTILLA HERO.PHP
$bgClass = 'bg-login'; 
$heroTitle = ''; 
$heroSubtitle = ''; 
$heroButtonText = '';
$heroButtonLink = ''; 

// 2. CONTENIDO DEL FORMULARIO (PARA INYECTAR EN hero-right)
ob_start();
?>
<div class="login-box">
    
    <h2 class="login-title">Acceso</h2>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert error">
            <i data-lucide="alert-circle"></i>
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert success">
            <i data-lucide="check-circle"></i> 
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>
    <form action="login.php" method="POST" class="login-form">
        
        <div class="input-group">
            <label for="email" class="input-label small-label">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                required
                class="form-input"
                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
            >
        </div>

        <div class="input-group password-field">
            <label for="password" class="input-label small-label">Contraseña</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                class="form-input"
            >
            <button type="button" id="togglePassword" class="toggle-password">
                <i data-lucide="eye"></i>
            </button>
        </div>
        
        <div class="forgot-link">
            <a href="<?= BASE_URL ?>/frontend/forgot_password.php">¿Has olvidado la contraseña?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-acceder">
            Acceder <span class="arrow-icon">&rarr;</span>
        </button>

    </form>
    
    <div class="register-prompt">
        <span>¿Aún no te has registrado? <a href="<?= BASE_URL ?>/frontend/register.php">Regístrate</a></span>
    </div>
</div>
<?php

$heroRightContent = ob_get_clean();


include __DIR__ . '/templates/header.php';
?>

<main>
    <?php include __DIR__ . '/templates/hero.php'; ?>
</main>

<script>
    // 1. INICIALIZACIÓN DE ICONOS DE LUCIDE (Se debe ejecutar tan pronto como sea posible)
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }

    // 2. Lógica DOM: Ejecutar la lógica de clic cuando la página esté lista.
    document.addEventListener('DOMContentLoaded', function() {

        // === Lógica para mostrar/ocultar contraseña (El Ojo) ===
        const togglePassword = document.getElementById('togglePassword');
        
        if (togglePassword) {
            togglePassword.addEventListener('click', function (e) {
                e.preventDefault(); 
                
                const passwordInput = document.getElementById('password');
                const icon = e.currentTarget.querySelector('[data-lucide]'); 

                if (!passwordInput || !icon) return; 

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.setAttribute('data-lucide', 'eye-off'); 
                } else {
                    passwordInput.type = 'password';
                    icon.setAttribute('data-lucide', 'eye'); 
                }
                
                // Vuelve a renderizar los iconos de Lucide (necesario después de cambiar el data-lucide)
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            });
        }
    });
</script>
<?php include __DIR__ . '/templates/footer.php'; ?>