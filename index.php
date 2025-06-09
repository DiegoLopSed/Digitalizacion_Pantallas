<?php
// Incluye el archivo de autenticación
include 'includes/auth.php';
include 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/js/all.js" defer></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Barra de Navegación -->
    <header class="bg-green-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-lg font-bold">Panel de Administración</h1>
        <nav>
            <ul class="flex space-x-4">
                <li><a href="index.php" class="hover:underline">Inicio</a></li>
                <li><a href="logout.php" class="hover:underline">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="p-8">
        <section class="mb-8">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
                    Pantalla eliminada correctamente.
                </div>
            <?php endif; ?>

            <h2 class="text-2xl font-bold mb-4">Pantallas Disponibles</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <?php
                $stmt = $pdo->query("
                    SELECT s.*, m.file_path 
                    FROM screens s
                    LEFT JOIN (
                        SELECT screen_id, file_path
                        FROM media
                        GROUP BY screen_id
                    ) m ON s.id = m.screen_id
                    ORDER BY s.created_at DESC
                ");

                while ($row = $stmt->fetch()) {
                    $screenUrl = "screens/" . urlencode($row['domain']);
                    $file_path = $row['file_path'];

                    echo "<div class='bg-white shadow rounded-lg p-4 flex flex-col items-center text-center'>";

                    // Mostrar multimedia
                    if (!empty($file_path)) {
                        if (filter_var($file_path, FILTER_VALIDATE_URL)) {
                            echo "<div class='w-full mb-4'>
                                    <iframe src='" . htmlspecialchars($file_path) . "' frameborder='0' allowfullscreen class='w-full h-40 rounded'></iframe>
                                  </div>";
                        } else {
                            echo "<img src='" . htmlspecialchars($file_path) . "' alt='Vista previa' class='w-32 h-24 object-cover mb-4 rounded'>";
                        }
                    } else {
                        echo "<div class='bg-gray-200 w-32 h-24 mb-4 rounded flex items-center justify-center text-gray-500'>Sin contenido</div>";
                    }

                    // Nombre y dominio
                    echo "<p class='font-medium'>" . htmlspecialchars($row['name']) . "</p>";
                    echo "<p class='text-gray-500 text-sm mb-2'>" . htmlspecialchars($row['domain']) . "</p>";

                    // Botones
                    echo "<div class='flex flex-col space-y-2 w-full'>";
                    echo "<a href='$screenUrl.php' target='_blank' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'><i class='fas fa-eye'></i> Ver</a>";
                    echo "<a href='functions/screen_config.php?id=" . $row['id'] . "' class='bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'><i class='fas fa-edit'></i> Editar</a>";
                    echo "<button onclick=\"copyToClipboard('$screenUrl')\" class='bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600'><i class='fas fa-link'></i> Copiar enlace</button>";
                    echo "<form action='functions/screen_delete.php' method='GET' onsubmit='return confirm(\"¿Estás seguro de eliminar esta pantalla?\");' class='w-full'>";
                    echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' class='bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 w-full'><i class='fas fa-trash'></i> Eliminar</button>";
                    echo "</form>";
                    echo "</div>";

                    echo "</div>";
                }
                ?>

                <!-- Tarjeta de agregar nueva pantalla -->
                <div class="bg-white shadow rounded-lg p-4 flex flex-col items-center justify-center">
                    <a href="functions/add.php" class="text-green-600 text-4xl mb-2"><i class="fas fa-plus"></i></a>
                    <p class="text-center font-medium">Agregar Pantalla</p>
                </div>
            </div>
        </section>
    </main>

    <script>
        function copyToClipboard(text) {
            const url = `${location.origin}/Digitalización_Pantallas_AT/${text}.php`;
            navigator.clipboard.writeText(url).then(() => {
                alert("Enlace copiado: " + url);
            }).catch(err => {
                console.error('Error al copiar', err);
                alert("Error al copiar el enlace.");
            });
        }
    </script>

    <!-- Pie de Página -->
    <footer class="bg-gray-200 text-center p-4">
        <p>&copy; <?php echo date('Y'); ?> - Gestión de Pantallas</p>
    </footer>
</body>

</html>
