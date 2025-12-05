<?php
// Configuración de errores (opcional, útil durante el desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión 
session_start();

// Inicializar variables para mensajes
$error_message = '';
$success_message = '';
// Inicializar variables para mantener los datos en el formulario si hay error
$nombre = '';
$apellidos = '';
$email = '';

// db/db_connection.php
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
        // Opciones de configuración para PDO
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones en caso de error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Establecer el modo de obtención predeterminado a array asociativo
        PDO::ATTR_EMULATE_PREPARES => false, // Deshabilitar la emulación de prepared statements (más seguro)
    ]);
} catch (PDOException $e) {
    // Si la conexión falla, registrar el error y detener la ejecución
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    die('Error de conexión a la base de datos. Revisa los registros del servidor.');
}

// ====================================================================
// Lógica de Registro
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
            // Usando 'id_usuario' de la tabla 'usuarios'
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
                // Columnas ajustadas a la tabla 'usuarios': 'apellido' (singular) y 'password_hash'
                $sql_insert = "INSERT INTO usuarios (nombre, apellido, email, password_hash, fecha_registro) VALUES (:nombre, :apellido, :email, :password_hash, NOW())";
                
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->bindParam(':nombre', $nombre);
                $stmt_insert->bindParam(':apellido', $apellidos); // La variable PHP sigue siendo $apellidos
                $stmt_insert->bindParam(':email', $email);
                $stmt_insert->bindParam(':password_hash', $password_hash); // Nombre del parámetro ajustado
                
                // 5. Ejecutar la inserción
                if ($stmt_insert->execute()) {
                    $success_message = "¡Registro exitoso! Te estamos dirigiendo a la página principal.";
                    // Limpiar las variables para que no se queden en el formulario
                    $nombre = $apellidos = $email = ''; 
                    
                    // Redirigir al index.php después de un registro exitoso
                } else {
                    $error_message = "Error desconocido al registrar el usuario.";
                }
            }
        } catch (PDOException $e) {
            // Error en la consulta SQL
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
        /* Definición de la fuente general: Inter */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7; 
        }
        /* Estilo para la columna de la imagen de fondo */
        .image-bg {
            
            background-image: url('/assets/img/stephanie-morales-DGt9zA3Fr0g-unsplash.jpg'); 
            background-size: cover;
            background-position: center;
            /* Bordes redondeados y sombra para el contenedor de la tarjeta */
        }
        /* Estilos para el panel de registro (fondo blanco según la imagen) */
        .register-panel {
            background-color: #ffffff; 
        }
        /* Estilos base para iconos (Header, Footer) */
        .logo-icon {
            width: 32px; 
            height: 32px; 
            filter: invert(0); /* Icono oscuro para fondo claro en la columna de la imagen */
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

    <div class="min-h-screen flex flex-col bg-[#f7f7f7] text-gray-800">

        <?php include __DIR__ . '/templates/header.php'; ?>

        <main class="flex-grow grid grid-cols-1 lg:grid-cols-2 lg:max-w-7xl lg:mx-auto lg:rounded-xl lg:shadow-2xl lg:my-10 lg:overflow-hidden w-full">

            <div class="image-bg relative hidden lg:block p-10">
                <div class="absolute inset-0 bg-black bg-opacity-5"></div>
                <div class="flex items-center space-x-2 z-10 relative">
                    <img src="/assets/img/logo-dark.png" alt="Logo La Cafetera" class="logo-icon w-8 h-8">
                    <span class="text-3xl font-bold text-gray-800">La Cafetera</span>
                </div>
            </div>

            <div class="flex items-center justify-center p-8 relative register-panel">
                
                <div class="w-full max-w-sm z-10 p-4 text-black"> 
                    <h1 class="text-4xl font-light mb-8">Crea tu cuenta</h1>

                    <?php if ($error_message): ?>
                        <div class="bg-red-500 text-white px-4 py-3 rounded-lg mb-4 text-sm" role="alert">
                            <p><?php echo $error_message; ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="bg-green-600 text-white px-4 py-3 rounded-lg mb-4 text-sm" role="alert">
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
            
        </main>

        <?php include __DIR__ . '/templates/footer.php'; ?>

    </div>
        // Redirigir al index.php si el registro fue exitoso
        <?php if (!empty($success_message)): ?>
            setTimeout(function() {
                window.location.href = 'index.php'; // Redirección a la página principal
            }, 2000); // 2 segundos
        <?php endif; ?>
    </script>
</body>
</html>


















