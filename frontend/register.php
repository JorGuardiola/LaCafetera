<?php
// frontend/register.php

// 1. LÓGICA PHP (Siempre arriba del todo)
session_start();
require_once __DIR__ . '/../db/connection.php';

$error_message = '';
$nombre = '';
$apellidos = '';
$email = '';
// $password ya no se mantiene, pero sí los demás campos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización básica
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? ''; // <-- NUEVO CAMPO

    // Validaciones
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password) || empty($password_confirm)) { // <-- AÑADIDO: password_confirm
        $error_message = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del email no es válido.";
    } elseif (strlen($password) < 8) {
        $error_message = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $password_confirm) { // <-- NUEVA VALIDACIÓN: Las contraseñas no coinciden
        $error_message = "Las contraseñas no coinciden.";
    } else {
        try {
            // Verificar si el email ya existe
            $stmtCheck = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
            $stmtCheck->execute([$email]);
            
            if ($stmtCheck->fetch()) {
                $error_message = "Este correo electrónico ya está registrado.";
            } else {
                // Crear usuario
               $hash = password_hash($password, PASSWORD_DEFAULT);

                // 1. Preparar la consulta SQL para insertar el nuevo usuario
                $sql = "INSERT INTO usuarios (nombre, apellido, email, password_hash, fecha_registro) VALUES (?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);

                // 2. Ejecutar la inserción
                if ($stmt->execute([$nombre, $apellidos, $email, $hash])) {
                    
                    // === ÉXITO EN EL REGISTRO E INICIO DE SESIÓN AUTOMÁTICO ===
                    
                    // 3. Obtener el ID del último usuario insertado para la sesión
                    $id_usuario = $pdo->lastInsertId();
                    
                    // 4. Configurar las variables de sesión para marcar al usuario como logueado
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $id_usuario;
                    $_SESSION['user_nombre'] = $nombre; // Almacena el nombre para usarlo en el front-end
                    
                    // 5. Establecer un mensaje de éxito y redirigir a la página principal
                    $_SESSION['mensaje_exito'] = "¡Cuenta creada! Has iniciado sesión.";
                    header('Location: ' . BASE_URL . '/frontend/index.php');
                    exit;
                } else {
                    // Si la ejecución de la consulta falló (error de la base de datos)
                    $error_message = "Error al guardar en la base de datos.";
                }
            }
        } catch (PDOException $e) {
            // En un entorno de producción, usa un mensaje genérico.
            // $error_message = "Error del sistema. Inténtalo más tarde."; 
            $error_message = "Error del sistema. Inténtalo más tarde. Detalles: " . $e->getMessage();
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
        
        <div class="input-group password-field">
            <label for="password_confirm" class="input-label small-label">Confirmar Contraseña</label>
            <input 
                type="password" 
                id="password_confirm" 
                name="password_confirm" 
                required
                class="form-input"
            >
            <button type="button" id="togglePasswordConfirm" class="toggle-password">
                <i data-lucide="eye"></i>
            </button>
            </div>
        <button type="submit" class="btn btn-primary btn-acceder btn-register">
            Registrarse <span class="arrow-icon">&rarr;</span>
        </button>

    </form>
    
    <div class="login-prompt">
        <span>Ya tienes una cuenta? <a href="<?= BASE_URL ?>/frontend/login.php">Accede</a></span>
    </div>
</div>
<?php

$heroRightContent = ob_get_clean();


// Incluye el inicio del HTML (<!DOCTYPE html>, <head>, <body>, Nav)
include __DIR__ . '/templates/header.php';
?>

    <?php include __DIR__ . '/templates/hero.php'; ?>

        
    <?php include __DIR__ . '/templates/footer.php'; ?>

    </div>
    
    <script>
        // Redirigir al index.php si el registro fue exitoso
        <?php if (!empty($success_message)): ?>
            setTimeout(function() {
                window.location.href = '<?= BASE_URL ?>/frontend/index.php'; // Redirección a la página principal
            }, 2000); // 2 segundos
        <?php endif; ?>

        // Inicializar iconos de Lucide (si está disponible)
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

            // Lógica para alternar visibilidad de contraseñas
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');
        
        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                
                // 1. Encontramos el campo de input asociado.
                // El campo input es el elemento anterior (previousElementSibling) o lo buscamos en el contenedor padre.
                const passwordInput = button.parentElement.querySelector('.form-input');
                
                // 2. Encontramos el icono
                const icon = button.querySelector('[data-lucide]'); 

                if (!passwordInput || !icon) return; 

                // 3. Alternamos el tipo de campo y el icono
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.setAttribute('data-lucide', 'eye-off'); // Ojo tachado
                } else {
                    passwordInput.type = 'password';
                    icon.setAttribute('data-lucide', 'eye'); // Ojo normal
                }
                
                // 4. Vuelve a renderizar los iconos de Lucide
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            });
        });
    
</script>