<?php
// frontend/login.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Inicializar mensajes
$error_message = '';
$success_message = '';

// Ruta base dinámica 
$base_path = '/LaCafetera'; 

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
            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // 3. Verificar las credenciales
            if ($user && password_verify($password, $user['password_hash'])) {
                // 4. Login Exitoso: Iniciar sesión y Redirigir
                $_SESSION['user_id'] = $user['id']; 
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // === REDIRECCIÓN ===
                // Verifica que no haya output antes de este header()
                header('Location: index.php'); 
                exit; // Detiene la ejecución para asegurar la redirección
                // ====================
                
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


// 1. VARIABLES PARA LA PLANTILLA HERO.PHP
$bgClass = 'bg-login'; // Clase para aplicar el fondo de las dos mitades
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
            <a href="forgot_password.php">¿Has olvidado la contraseña?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-acceder">
            Acceder <span class="arrow-icon">&rarr;</span>
        </button>

    </form>
    
    <div class="register-prompt">
        <span>¿Aún no te has registrado? <a href="register.php">Regístrate</a></span>
    </div>
</div>
<?php

$heroRightContent = ob_get_clean();


// Incluye el inicio del HTML (<!DOCTYPE html>, <head>, <body>, Nav)
include __DIR__ . '/templates/header.php';
?>

<main>
    <?php include __DIR__ . '/templates/hero.php'; ?>
</main>

<script>
    // Se ejecuta tan pronto como el navegador llega a este punto
    
    // === 1. LÓGICA DE REDIRECCIÓN PARA INICIO DE SESIÓN 
    
    <?php 
    // Usamos la variable de éxito de sesión para manejar la redirección si el header PHP falló
    if (isset($_SESSION['login_success']) && $_SESSION['login_success']): 
        unset($_SESSION['login_success']); 
    ?>
        // Redirección inmediata (si el PHP no pudo hacer el header())
        window.location.href = '<?php echo $base_path; ?>/index.php'; 
    <?php endif; ?>
    
    // Ejecutar el resto del código cuando el DOM esté completamente cargado.
    document.addEventListener('DOMContentLoaded', function() {
        
        // INICIALIZACIÓN DE ICONOS 
        // Usamos setTimeout(0) para darle prioridad alta después de la carga del DOM.
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            setTimeout(function() {
                 window.lucide.createIcons();
            }, 0);
        }

       
        // Lógica para mostrar/ocultar contraseña
        
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function (e) {
                e.preventDefault(); 
                
                const passwordInput = document.getElementById('password');
                // Buscamos el elemento que tiene el atributo data-lucide (el icono)
                const icon = e.currentTarget.querySelector('[data-lucide]'); 

                if (!passwordInput || !icon) return; 

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.setAttribute('data-lucide', 'eye-off'); // Ojo tachado
                } else {
                    passwordInput.type = 'password';
                    icon.setAttribute('data-lucide', 'eye'); // Ojo normal
                }
                
                // Vuelve a renderizar los iconos de Lucide
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
                if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
            });
        }
    });
</script>

<?php
// Incluye el cierre del HTML (Footer, Scripts, </body>, </html>)
require_once __DIR__ . '/templates/footer.php';
?>