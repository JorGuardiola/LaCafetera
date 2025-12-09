<?php
// frontend/register.php

// 1. LÓGICA PHP (Siempre arriba del todo)
session_start();
require_once __DIR__ . '/../db/connection.php';

$error_message = '';
$nombre = '';
$apellidos = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización básica
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validaciones
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del email no es válido.";
    } elseif (strlen($password) < 8) {
        $error_message = "La contraseña debe tener al menos 8 caracteres.";
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
                $sql = "INSERT INTO usuarios (nombre, apellido, email, password_hash, fecha_registro) VALUES (?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$nombre, $apellidos, $email, $hash])) {
                    // Éxito: Redirigir al login con mensaje (opcional, aquí redirigimos directo)
                    $_SESSION['mensaje_exito'] = "¡Cuenta creada! Inicia sesión.";
                    header('Location: login.php');
                    exit;
                } else {
                    $error_message = "Error al guardar en la base de datos.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error del sistema. Inténtalo más tarde.";
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

    // Inicializar iconos al cargar
    if(window.lucide && window.lucide.createIcons) {
        window.lucide.createIcons();
    }
</script>

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
