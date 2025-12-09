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

// 2. CONFIGURACIÓN VISUAL (Para hero.php)
$bgClass = 'bg-login'; // Reutilizamos el fondo del login para consistencia
$heroTitle = ''; 
$heroSubtitle = ''; 
$heroButtonText = '';
$heroButtonLink = ''; 

// 3. CONSTRUCCIÓN DEL FORMULARIO (Se inyecta en la derecha del Hero)
ob_start();
?>
<div class="login-box">
    <h2 class="login-title">Crear Cuenta</h2>
    
    <?php if ($error_message): ?>
        <div class="alert error" style="color: #e74c3c; text-align:center; margin-bottom:1rem; font-weight:bold;">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="login-form">
        
        <div class="input-group">
            <label for="nombre" class="input-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-input" value="<?= htmlspecialchars($nombre) ?>" required>
        </div>

        <div class="input-group">
            <label for="apellidos" class="input-label">Apellidos</label>
            <input type="text" name="apellidos" id="apellidos" class="form-input" value="<?= htmlspecialchars($apellidos) ?>" required>
        </div>

        <div class="input-group">
            <label for="email" class="input-label">Email</label>
            <input type="email" name="email" id="email" class="form-input" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="input-group password-field">
            <label for="password" class="input-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-input" placeholder="Mínimo 8 caracteres" required>
            <button type="button" id="togglePassword" class="toggle-password">
                <i data-lucide="eye"></i>
            </button>
        </div>

        <button type="submit" class="btn-acceder" style="margin-top: 2rem;">
            Registrarse <span class="arrow-icon">&rarr;</span>
        </button>
    </form>
    
    <div class="register-prompt">
        <span>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></span>
    </div>
</div>
<?php
// Guardamos el contenido del formulario en esta variable para el Hero
$heroRightContent = ob_get_clean();

// 4. VISTA FINAL (Header -> Main/Hero -> Footer)
include __DIR__ . '/templates/header.php'; 
?>

<main>
    <?php include __DIR__ . '/templates/hero.php'; ?>
</main>

<script>
    // Script para ver/ocultar contraseña (idéntico a login.php)
    const toggleBtn = document.getElementById('togglePassword');
    const passInput = document.getElementById('password');

    if(toggleBtn && passInput) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            
            // Cambiar icono
            const icon = toggleBtn.querySelector('i');
            if(icon) {
                icon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
            }
            // Refrescar iconos Lucide
            if(window.lucide && window.lucide.createIcons) {
                window.lucide.createIcons();
            }
        });
    }

    // Inicializar iconos al cargar
    if(window.lucide && window.lucide.createIcons) {
        window.lucide.createIcons();
    }
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>