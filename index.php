<?php
// Incluye el archivo de autenticación
include 'includes/auth.php';
include 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"> <!-- Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/js/all.js" defer></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Barra de Navegación -->
    <header class="bg-green-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-lg font-bold">Panel de Administración</h1>
        <nav>
            <ul class="flex space-x-4">
                <li><a href="index.php" class="hover:underline">Inicio</a></li>
                <li><a href="screens/list.php" class="hover:underline">Gestión de Pantallas</a></li>
                <li><a href="logout.php" class="hover:underline">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="p-8">
        <section class="mb-8">
            <h2 class="text-2xl font-bold mb-4">Pantallas Disponibles</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <?php
                $stmt = $pdo->query("SELECT * FROM screens ORDER BY created_at DESC");
                while ($row = $stmt->fetch()) {
                    echo "<div class='bg-white shadow rounded-lg p-4 flex flex-col items-center'>";
                    echo "<div class='bg-gray-200 w-32 h-24 mb-4 rounded'></div>";
                    echo "<p class='font-medium text-center'>" . htmlspecialchars($row['name']) . "</p>";
                    echo "<p class='text-gray-500 text-sm'>" . htmlspecialchars($row['domain']) . "</p>";
                    echo "<a href='functions/screen_config.php?id=" . $row['id'] . "' class='mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'><i class='fas fa-edit'></i> Editar</a>";
                    echo "</div>";
                }
                ?>
                <div class="bg-white shadow rounded-lg p-4 flex flex-col items-center justify-center">
                    <a href="add.php" class="text-green-600 text-4xl mb-2"><i class="fas fa-plus"></i></a>
                    <p class="text-center font-medium">Agregar Pantalla</p>
                </div>

            </div>
        </section>
    </main>

    <!-- Pie de Página -->
    <footer class="bg-gray-200 text-center p-4">
        <p>&copy; <?php echo date('Y'); ?> - Gestión de Pantallas</p>
    </footer>
</body>

</html>