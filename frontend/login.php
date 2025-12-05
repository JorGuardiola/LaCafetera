<?php
// frontend/login.php
session_start();
require_once __DIR__ . '/../db/connection.php';

// Inicializar mensajes
$error_message = '';
$success_message = '';

// Ruta base dinámica (usada para las imágenes del carrusel)
// Asegúrate de que '/lacafetera' sea la subcarpeta del proyecto en htdocs.
$base_path = '/lacafetera'; 

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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - La Cafetera</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Iconos para funcionalidad de contraseña -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        /* Definición de la fuente general: Inter */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0d0d0d; 
        }
        /* Estilo para la columna de la imagen de fondo */
        .image-bg {
            background-image: url('/assets/img/hannah-tims-oasBqJPFyJA-unsplash.jpg'); 
            background-size: cover;
            background-position: center;
            border-radius: 0 10px 10px 0; /* Bordes redondeados en la derecha */
            overflow: hidden;
        }
        /* Contenedor del formulario  */
        .login-panel {
            background-color: rgba(255, 255, 255, 0.61);
            backdrop-filter: blur(10px); 
        }
        /* Estilos base para iconos (Header, Footer) */
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

    <!-- Contenedor principal de la página -->
    <div class="min-h-screen flex flex-col bg-[#0d0d0d] text-white">

       <?php include __DIR__ . '/templates/header.php'; ?>

        <!-- CONTENIDO PRINCIPAL (SPLIT LAYOUT) -->
       <main class="flex-grow grid grid-cols-1 lg:grid-cols-2 lg:max-w-7xl lg:mx-auto lg:rounded-xl lg:shadow-2xl lg:my-10 lg:overflow-hidden w-full">

            <!-- Columna Izquierda: Imagen de Fondo -->
            <div class="image-bg relative hidden lg:block">
                <!-- Overlay sutil -->
                <div class="absolute inset-0 bg-black bg-opacity-10"></div>
                <!-- Logo flotante -->
                <div class="absolute top-8 left-8 flex items-center space-x-2">
                    <img src="/assets/img/logo-white.png" alt="Logo La Cafetera" class="logo-icon w-8 h-8">
                    <span class="text-3xl font-bold text-white">La Cafetera</span>
                </div>
            </div>

            <!-- Columna Derecha: Formulario de Acceso (Fondo Semitransparente) -->
            <div class="flex items-center justify-center p-8 relative login-panel">
                
                <!-- Contenedor del formulario -->
                <div class="w-full max-w-sm z-10 p-4 text-black"> 
                    <h1 class="text-5xl font-light mb-8">Acceso</h1>

                    <!-- Mostrar mensajes de PHP (Errores/Éxito) -->
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
                    
                    <!-- Formulario de Acceso -->
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                        
                        <!-- Campo de Email -->
                        <div>
                            <label for="email" class="block text-sm mb-2">
                                Email
                            </label>
                            <input type="email" id="email" name="email" required
                                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black transition duration-150 text-gray-800"
                                placeholder="tu.correo@dominio.com">
                        </div>

                        <!-- Campo de Contraseña -->
                        <div>
                            <label for="password" class="block text-sm mb-2">
                                Contraseña
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black transition duration-150 text-gray-800"
                                    placeholder="********">
                                <!-- Icono de Ojo -->
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 focus:outline-none">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                            </div>
                            <div class="text-right mt-2">
                                <a href="#" class="text-xs text-black opacity-70 hover:opacity-100 transition duration-150 underline">
                                    ¿Has olvidado la contraseña?
                                </a>
                            </div>
                        </div>

                        <!-- Botón de Enviar -->
                        <div class="pt-4">
                            <button type="submit"
                                    class="w-full flex justify-center py-3 px-6 rounded-lg shadow-md text-base font-semibold text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition duration-150 ease-in-out transform hover:scale-[1.01]">
                                Acceder &rightarrow;
                            </button>
                        </div>
                        
                    </form>

                    <div class="mt-8 text-center">
                        <span class="text-sm opacity-70">
                            ¿Aún no te has registrado? 
                            <a href="#" class="font-semibold underline hover:opacity-100 transition duration-150">
                                Regístrate
                            </a>
                        </span>
                    </div>

                </div>
            </div>
        </main>

        <?php include __DIR__ . '/templates/footer.php'; ?>

    </div>

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
    </script>
</body>
</html>
