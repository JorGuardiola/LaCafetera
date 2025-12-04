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



<!-- 3. FOOTER -->
    <footer class="bg-black text-white py-10 border-t border-gray-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
                
                <!-- Columna 1: Branding -->
                <div class="col-span-2 md:col-span-1 space-y-3">
                     <div class="flex items-center space-x-2">
                        <img src="/assets/img/logo-white.png" alt="Logo La Cafetera" class="logo-icon">
                        <span class="text-2xl font-bold">La Cafetera</span>
                    </div>
                    <p class="text-sm opacity-70">Desde 1994 seleccionando los mejores cafés del mundo para llevarlos a tu taza</p>
                </div>

                <!-- Columna 2: Cafetera Links -->
                <div>
                    <h4 class="font-semibold mb-3">Cafetera</h4>
                    <ul class="space-y-2 text-sm opacity-70">
                        <li><a href="#" class="hover:text-white transition duration-200">Nosotros</a></li>
                        <li><a href="#" class="hover:text-white transition duration-200">Elaboración</a></li>
                        <li><a href="#" class="hover:text-white transition duration-200">Sostenibilidad</a></li>
                        <li><a href="#" class="hover:text-white transition duration-200">Contacto</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Productos Links -->
                <div>
                    <h4 class="font-semibold mb-3">Productos</h4>
                    <ul class="space-y-2 text-sm opacity-70">
                        <li><a href="#" class="hover:text-white transition duration-200">Productos</a></li>
                        <li><a href="#" class="hover:text-white transition duration-200">Packs</a></li>
                        <li><a href="#" class="hover:text-white transition duration-200">Recetas</a></li>
                        <li><a href="#" class="hover:text-white transition duration-200">Información</a></li>
                    </ul>
                </div>

                <!-- Columna 4 & 5: Contacto y Social Media -->
                <div class="col-span-2 md:col-span-2 flex flex-col items-start md:items-end">
                    <button class="border border-white text-white px-6 py-2 rounded-lg hover:bg-white hover:text-black transition duration-200 mb-4">
                        Contacto
                    </button>
                    <div class="flex space-x-3 mb-6">
                        <a href="URL_TU_INSTAGRAM" class="p-2 border border-gray-700 rounded-full hover:bg-gray-800 transition duration-200">
                            <img src="/assets/img/instagram.svg" alt="Instagram" class="w-5 h-5">
                        </a>
                        <a href="URL_TU_FACEBOOK" class="p-2 border border-gray-700 rounded-full hover:bg-gray-800 transition duration-200">
                            <img src="/assets/img/facebook.svg" alt="Facebook" class="w-5 h-5">
                        </a>
                        <a href="URL_TU_WHATSAPP" class="p-2 border border-gray-700 rounded-full hover:bg-gray-800 transition duration-200">
                            <img src="/assets/img/whatsapp.svg" alt="Whatsapp" class="w-5 h-5">
                        </a>
                    </div>
                </div>

            </div>

            <!-- Legal/Copyright -->
            <div class="border-t border-gray-800 pt-6 mt-6 flex flex-col md:flex-row justify-between items-center text-xs opacity-50">
                <p>© 2025 La Cafetera. Todos los derechos reservados.</p>
                <p class="mt-2 md:mt-0 space-x-4">
                    <a href="#" class="hover:text-white">Política de privacidad</a> | 
                    <a href="#" class="hover:text-white">Términos y condiciones</a> | 
                    <a href="#" class="hover:text-white">Cookies</a>
                </p>
            </div>
        </div>
    </footer>
    
    <script>
        // Inicializar iconos de Lucide (si está disponible)
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }

        // Lógica para mostrar/ocultar contraseña (proteger contra elementos faltantes)
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
                    if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
                } else {
                    passwordInput.type = 'password';
                    if (icon) icon.setAttribute('data-lucide', 'eye');
                    if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
                }
            });
        }

        // Redirigir al index.php si el registro fue exitoso
        <?php if (!empty($success_message)): ?>
            setTimeout(function() {
                window.location.href = 'index.php'; // Redirección a la página principal
            }, 2000); // 2 segundos
        <?php endif; ?>
    </script>
</body>
</html>


















