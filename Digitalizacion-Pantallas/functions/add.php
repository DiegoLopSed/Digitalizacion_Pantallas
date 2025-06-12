<?php
include '../includes/auth.php'; // Verificar autenticación
include '../includes/db.php';   // Conexión a la base de datos

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $domain = trim($_POST['domain'] ?? '');

    if (empty($name) || empty($domain)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $domain)) {
        $error = 'El dominio solo puede contener letras, números, guiones y guiones bajos.';
    } else {
        try {
            // Insertar pantalla
            $stmt = $pdo->prepare("INSERT INTO screens (name, domain, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$name, $domain]);

            // Crear carpeta screens si no existe
            $dir = __DIR__ . '../../screens';
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $filePath = "$dir/{$domain}.php";

            // Contenido del archivo generado
            $fileContent = <<<PHP
<?php
include '../includes/db.php';

// Obtener screen_id según domain
\$stmt = \$pdo->prepare("SELECT id, name FROM screens WHERE domain = ?");
\$stmt->execute(['$domain']);
\$screen = \$stmt->fetch(PDO::FETCH_ASSOC);

if (!\$screen) {
    die('Pantalla no encontrada.');
}

// Obtener media asociados
\$stmt = \$pdo->prepare("SELECT * FROM media WHERE screen_id = ?");
\$stmt->execute([\$screen['id']]);
\$mediaItems = \$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(\$screen['name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background-color: #000;
            color: #fff;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }
        .screen-wrapper {
            position: relative;
            width: 100vw;
            height: 100vh;
            max-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            aspect-ratio: 9 / 16;
            background-color: #111;
        }
        .media-content {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .fullscreen-img {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .fullscreen-img img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="screen-wrapper">
        <?php if (empty(\$mediaItems)): ?>
            <p class="text-gray-400 text-center text-lg">No hay contenido disponible.</p>
        <?php else: ?>
            <?php foreach (\$mediaItems as \$item): ?>
                <?php
                \$path = \$item['file_path'];
                if (filter_var(\$path, FILTER_VALIDATE_URL)) {
                    echo '<iframe src="' . htmlspecialchars(\$path) . '" class="media-content border-0" allowfullscreen></iframe>';
                } else {
                    \$ext = pathinfo(\$path, PATHINFO_EXTENSION);
                    \$localPath = "../" . \$path;

                    if (in_array(strtolower(\$ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        echo '<img src="' . htmlspecialchars(\$localPath) . '" class="media-content cursor-pointer" alt="Imagen" onclick="openFullscreen(\'' . htmlspecialchars(\$localPath) . '\')">';
                    } elseif (in_array(strtolower(\$ext), ['mp4', 'webm', 'ogg'])) {
                        echo '<video controls autoplay loop muted class="media-content">
                                <source src="' . htmlspecialchars(\$localPath) . '" type="video/' . htmlspecialchars(\$ext) . '">
                                Tu navegador no soporta videos HTML5.
                              </video>';
                    } else {
                        echo '<p class="text-center text-red-500">Tipo de archivo no soportado.</p>';
                    }
                }
                ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="fullscreen-container" class="hidden"></div>

    <script>
        function openFullscreen(src) {
            const container = document.getElementById('fullscreen-container');
            container.className = 'fullscreen-img';
            container.innerHTML = '<img src="' + src + '" alt="Imagen ampliada" onclick="closeFullscreen()">';
        }

        function closeFullscreen() {
            const container = document.getElementById('fullscreen-container');
            container.className = 'hidden';
            container.innerHTML = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === "Escape") closeFullscreen();
        });
    </script>
</body>
</html>
PHP;
            // Crear archivo
            file_put_contents($filePath, $fileContent);
            $success = "Pantalla creada exitosamente: <a class='underline' href='../screens/{$domain}.php' target='_blank'>Ver pantalla</a>";
        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000) ? 'El dominio ya existe. Intenta con otro.' : 'Error de base de datos: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = 'Error al crear el archivo: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Pantalla</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-green-600 text-white p-4 flex justify-between items-center">
        <nav>
            <ul class="flex space-x-4">
                <li><a href="../index.php" class="hover:underline">Inicio</a></li>
                <li><a href="../logout.php" class="hover:underline">Cerrar Sesión</a></li>
            </ul>
        </nav>
        <h1 class="text-2xl font-bold text-center">Agregar Nueva Pantalla</h1>
    </header>
    <main class="p-6 max-w-xl mx-auto">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Formulario de Registro</h2>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="add.php" class="space-y-4">
                <div>
                    <label for="name" class="block font-medium">Nombre de la Pantalla</label>
                    <input type="text" name="name" id="name" class="w-full border border-gray-300 rounded px-3 py-2" required>
                </div>
                <div>
                    <label for="domain" class="block font-medium">Dominio</label>
                    <input type="text" name="domain" id="domain" class="w-full border border-gray-300 rounded px-3 py-2" required>
                    <p class="text-sm text-gray-500 mt-1">Ejemplo: pantalla01, menu-principal, etc.</p>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">
                    Crear Pantalla
                </button>
            </form>
        </div>
    </main>
</body>
</html>
