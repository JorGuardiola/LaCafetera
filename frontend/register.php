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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - La Cafetera</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            /* Cambiado a fondo oscuro para igualar login.php */
            background-color: #0d0d0d; 
        }
        /* Uso de la variable PHP para la ruta de la imagen */
        .image-bg {
            /* Se establece la imagen de fondo solicitada */
            background-image: url('<?php echo $base_path; ?>/assets/img/stephanie-morales-DGt9zA3Fr0g-unsplash.jpg');
            background-color: #e5e7eb; 
            background-size: cover;
            background-position: center;
        }
        /* Estilo del panel de registro (similar al de login.php) */
        .register-panel {
            background-color: rgba(255, 255, 255, 0.61);
            backdrop-filter: blur(10px); 
        }
        /* Estilos base para iconos (copiados de login.php) */
        .logo-icon {
            width: 32px; 
            height: 32px; 
            filter: invert(1); 
        }
        .header-icon {
            width: 20px; 
            height: 20px; 
            opacity: 0.75;
            cursor: pointer;
            transition: opacity 200ms;
        }
        .header-icon:hover {
            opacity: 1;
        }
    </style>
</head>
<body>

    <!-- Contenedor principal cambiado a tema oscuro para igualar login.php -->
    <div class="min-h-screen flex flex-col bg-[#0d0d0d] text-white">

        <?php include __DIR__ . '/templates/header.php'; ?>

        <main class="flex-grow grid grid-cols-1 lg:grid-cols-2 lg:max-w-7xl lg:mx-auto lg:rounded-xl lg:shadow-2xl lg:my-10 lg:overflow-hidden w-full">

            <div class="image-bg relative hidden lg:block p-10">
                <!-- Oscurecimiento del fondo para mejor contraste -->
                <div class="absolute inset-0 bg-black bg-opacity-10"></div> 
            </div>

            <!-- El panel usa 'register-panel' con blur y el texto interno es negro sobre el panel claro -->
            <div class="flex items-center justify-center p-8 relative register-panel">
                
                <div class="w-full max-w-sm z-10 p-4 text-black"> 
                    <h1 class="text-4xl font-light mb-8">Crea tu cuenta</h1>

                    <!-- Mensajes de error y éxito con colores adaptados para el fondo claro del panel -->
                    <?php if ($error_message): ?>
                        <div class="bg-red-800 bg-opacity-80 text-white px-4 py-3 rounded-lg mb-4 text-sm" role="alert">
                            <p><?php echo $error_message; ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="bg-green-800 bg-opacity-80 text-white px-4 py-3 rounded-lg mb-4 text-sm" role="alert">
                            <p><?php echo $success_message; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
                        
                        <div class="flex space-x-4">
                            <div class="w-1/2">
                                <label for="nombre" class="block text-sm mb-2">Nombre</label>
                                <input type="text" id="nombre" name="nombre" required
                                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black transition duration-150 text-gray-800"
                                    value="<?php echo htmlspecialchars($nombre); ?>"
                                    placeholder="Nombre">
                            </div>
                            <div class="w-1/2">
                                <label for="apellidos" class="block text-sm mb-2">Apellidos</label>
                                <input type="text" id="apellidos" name="apellidos" required
                                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black transition duration-150 text-gray-800"
                                    value="<?php echo htmlspecialchars($apellidos); ?>"
                                    placeholder="Apellidos">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm mb-2">Email</label>
                            <input type="email" id="email" name="email" required
                                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black transition duration-150 text-gray-800"
                                value="<?php echo htmlspecialchars($email); ?>"
                                placeholder="tu.correo@dominio.com">
                        </div>

                        <div>
                            <label for="password" class="block text-sm mb-2">Contraseña</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black transition duration-150 text-gray-800"
                                    placeholder="Mínimo 8 caracteres">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 focus:outline-none">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit"
                                        class="w-full flex justify-center py-3 px-6 rounded-lg shadow-md text-base font-semibold text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition duration-150 ease-in-out transform hover:scale-[1.01]">
                                Registrarse &rightarrow;
                            </button>
                        </div>
                        
                        <div class="flex items-center justify-center pt-2">
                            <button type="button" class="w-full flex items-center justify-center py-3 px-6 border border-gray-300 rounded-lg shadow-sm text-base font-semibold text-gray-700 bg-white hover:bg-gray-50 transition duration-150">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/4/4a/Logo_2013_Google.png" alt="Google logo" class="w-5 h-5 mr-2">
                                Sign up with Google
                            </button>
                        </div>

                    </form>

                    <div class="mt-8 text-center">
                        <span class="text-sm opacity-70">
                            ¿Ya tienes una cuenta? 
                            <a href="login.php" class="font-semibold underline hover:opacity-100 transition duration-150">
                                Accede
                            </a>
                        </span>
                    </div>

                </div>
            
            </div>
        </main>

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
</body>
</html>