<?php
// frontend/register.php

// Configuración de errores (opcional, útil durante el desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión 
session_start();

// Ruta base dinámica (usada para las imágenes y enlaces)
// **¡IMPORTANTE!** Cambia '/LaCafetera' si tu subcarpeta en htdocs es diferente.
$base_path = '/LaCafetera'; 

// Inicializar variables para mensajes
$error_message = '';
$success_message = '';
// Inicializar variables para mantener los datos en el formulario si hay error
$nombre = '';
$apellidos = '';
$email = '';

// ====================================================================
// CONEXIÓN A LA BASE DE DATOS (db/db_connection.php)
// ====================================================================
// Parámetros de conexión a la base de datos
$host = 'localhost';
$db = 'cafeteria_db'; // Asegúrate de que esta base de datos exista
$user = 'root';
$pass = ''; // Contraseña de tu usuario root de MySQL
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    // Crear una instancia de PDO
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        PDO::ATTR_EMULATE_PREPARES => false, 
    ]);
} catch (PDOException $e) {
    // Si la conexión falla, registrar el error y detener la ejecución
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    // Mensaje de error amigable para el usuario
    die('Error de conexión a la base de datos. Revisa los registros del servidor.');
}

// ====================================================================
// Lógica de Procesamiento del Formulario de Registro
// ====================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger y sanitizar datos:
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
    $apellidos = filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password_input = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW) ?? '';

    // Validaciones básicas
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password_input)) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del correo electrónico no es válido.";
    } elseif (strlen($password_input) < 8) {
        $error_message = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        try {
            // 2. Verificar si el email ya existe
            $sql_check = "SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':email', $email);
            $stmt_check->execute();

            if ($stmt_check->fetch()) {
                $error_message = "Este correo electrónico ya está registrado.";
            } else {
                // 3. Hashear la contraseña de forma segura
                $password_hash = password_hash($password_input, PASSWORD_DEFAULT);

                // 4. Preparar la consulta SQL para insertar el nuevo usuario
                $sql_insert = "INSERT INTO usuarios (nombre, apellido, email, password_hash, fecha_registro) VALUES (:nombre, :apellido, :email, :password_hash, NOW())";
                
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->bindParam(':nombre', $nombre);
                $stmt_insert->bindParam(':apellido', $apellidos);
                $stmt_insert->bindParam(':email', $email);
                $stmt_insert->bindParam(':password_hash', $password_hash);
                
                // 5. Ejecutar la inserción
                if ($stmt_insert->execute()) {
                    $success_message = "¡Registro exitoso! Te estamos dirigiendo a la página principal.";
                    $nombre = $apellidos = $email = ''; 
                } else {
                    $error_message = "Error desconocido al registrar el usuario.";
                }
            }
        } catch (PDOException $e) {
            error_log("Error de registro: " . $e->getMessage());
            $error_message = "Ocurrió un error en la base de datos. Intenta de nuevo más tarde.";
        }
    }
}
// 1. VARIABLES PARA LA PLANTILLA HERO.PHP
$bgClass = 'bg-registro'; // Clase para aplicar el fondo de las dos mitades
$heroTitle = ''; 
$heroSubtitle = ''; 
$heroButtonText = '';
$heroButtonLink = ''; 

// 2. CONTENIDO DEL FORMULARIO DE REGISTRO (PARA INYECTAR EN hero-right)
ob_start();
?>
<div class="login-box register-box">
    
    <h2 class="login-title">Crea tu cuenta</h2>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert error">
            <i data-lucide="alert-circle"></i>
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="register-form">
        
        <div class="input-group-row">
            <div class="input-group half-width">
                <label for="nombre" class="input-label small-label">Nombre</label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    required
                    class="form-input"
                    value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>"
                >
            </div>

            <div class="input-group half-width">
                <label for="apellidos" class="input-label small-label">Apellidos</label>
                <input 
                    type="text" 
                    id="apellidos" 
                    name="apellidos" 
                    required
                    class="form-input"
                    value="<?php echo isset($apellidos) ? htmlspecialchars($apellidos) : ''; ?>"
                >
            </div>
        </div>

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
        
        <button type="submit" class="btn btn-primary btn-acceder btn-register">
            Registrarse <span class="arrow-icon">&rarr;</span>
        </button>

    </form>
      
    <div class="login-prompt">
        <span>Ya tienes una cuenta? <a href="login.php">Accede</a></span>
    </div>
</div>
<?php

$heroRightContent = ob_get_clean();


// Incluye el inicio del HTML (<!DOCTYPE html>, <head>, <body>, Nav)
include __DIR__ . '/templates/header.php';
?>

    <?php include __DIR__ . '/templates/hero.php'; ?>

        <!-- FOOTER: Se ha quitado la etiqueta <footer> envolvente para que el footer.php incluído determine el estilo, como en login.php -->
        <?php include __DIR__ . '/templates/footer.php'; ?>

    </div>
    
    <script>
        // Redirigir al index.php si el registro fue exitoso
        <?php if (!empty($success_message)): ?>
            setTimeout(function() {
                window.location.href = '<?php echo $base_path; ?>/index.php'; // Redirección a la página principal
            }, 2000); // 2 segundos
        <?php endif; ?>

        // Inicializar iconos de Lucide (si está disponible)
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Lógica para mostrar/ocultar contraseña
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function (e) {
                e.preventDefault(); // Evitar el envío accidental del formulario
                const passwordInput = document.getElementById('password');
                if (!passwordInput) return;
                const icon = e.currentTarget.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    if (icon) icon.setAttribute('data-lucide', 'eye-off');
                } else {
                    passwordInput.type = 'password';
                    if (icon) icon.setAttribute('data-lucide', 'eye');
                }
                // Vuelve a renderizar los iconos de Lucide después del cambio de atributo
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            });
        }
    </script>
