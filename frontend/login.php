<?php
// Configuración de errores (opcional)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inicializar variables para mensajes
$error_message = '';
$success_message = '';

// ====================================================================
// *** COMENTARIO: AQUÍ VA LA CONEXIÓN DE BASE DE DATOS Y LÓGICA DE PHP ***
// Este es el bloque que se ejecuta cuando el usuario envía el formulario.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger y sanitizar datos:
    // $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    // $password = $_POST['password']; 

    // 2. Lógica de autenticación:
    /*
    // require 'db_config.php'; // Incluye tu archivo de configuración de BD

    // Lógica para verificar credenciales contra la base de datos (MySQLi, PDO, Firestore, etc.)
    // if (credenciales_validas($email, $password)) {
    //     $success_message = "¡Acceso exitoso!";
    //     // header("Location: dashboard.php");
    //     // exit();
    // } else {
    //     $error_message = "Email o contraseña incorrectos.";
    // }
    */
}
// ====================================================================
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

        <!-- HEADER / NAVBAR -->
        <header class="w-full bg-black p-4 border-b border-gray-800">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <!-- ICONO DEL LOGO -->
                    <img src="/assets/img/logo-white.png" alt="Logo La Cafetera" class="logo-icon">
                    <span class="text-xl font-bold">La Cafetera</span>
                </div>
                
                <!-- Navegación -->
                <nav class="hidden md:flex space-x-6 text-sm">
                    <a href="#" class="text-white opacity-75 hover:opacity-100 transition duration-200">Productos</a>
                    <a href="#" class="text-white opacity-75 hover:opacity-100 transition duration-200">Nosotros</a>
                    <a href="#" class="text-white opacity-75 hover:opacity-100 transition duration-200">Recetas</a>
                    <a href="#" class="text-white opacity-100 font-semibold border-b-2 border-white pb-1">Contacto</a>
                </nav>

                <!-- Iconos de Lupa, Carrito y Usuario -->
                <div class="flex items-center space-x-4">
                    <img src="/assets/img/buscar.png" alt="Buscar" class="header-icon">
                    <img src="/assets/img/carrito.png" alt="Carrito" class="header-icon">
                    <img src="/assets/img/login.png" alt="Perfil" class="header-icon">
                </div>
            </div>
        </header>

        <!-- CONTENIDO PRINCIPAL (SPLIT LAYOUT) -->
       <main class="flex-grow grid grid-cols-1 lg:grid-cols-2 lg:max-w-7xl lg:mx-auto lg:rounded-xl lg:shadow-2xl lg:my-10 lg:overflow-hidden w-full">

            <!-- Columna Izquierda: Imagen de Fondo -->
            <div class="image-bg relative hidden lg:block">
                <!-- Overlay sutil -->
                <div class="absolute inset-0 bg-black bg-opacity-10"></div>
                <!-- Logo flotante -->
                <div class="absolute top-8 left-8 flex items-center space-x-2">
                    <img src="img/logo-white.png" alt="Logo La Cafetera" class="logo-icon w-8 h-8">
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

        <!-- FOOTER (Negro) -->
        <footer class="bg-black text-white py-10 border-t border-gray-800">
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
                        <!-- Redes Sociales usando IMG -->
                        <div class="flex space-x-3 mb-6">
                            <a href="URL_TU_INSTAGRAM" class="p-2 border border-gray-700 rounded-full hover:bg-gray-800 transition duration-200">
                                <img src="/assets/img/instagram.svj" alt="Instagram" class="w-5 h-5">
                            </a>
                            <a href="URL_TU_FACEBOOK" class="p-2 border border-gray-700 rounded-full hover:bg-gray-800 transition duration-200">
                                <img src="/assets/img/facebook.svj" alt="Facebook" class="w-5 h-5">
                            </a>
                            <a href="URL_TU_WHATSAPP" class="p-2 border border-gray-700 rounded-full hover:bg-gray-800 transition duration-200">
                                <img src="/assets/img/whatsapp.svj" alt="Whatsapp" class="w-5 h-5">
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

    </div>

    <script>
        // Inicializar iconos de Lucide
        lucide.createIcons();

        // Lógica para mostrar/ocultar contraseña
        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const passwordInput = document.getElementById('password');
            const icon = e.currentTarget.querySelector('i');
            
            // Alternar el tipo de input y el icono
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
                lucide.createIcons();
            } else {
                passwordInput.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>


