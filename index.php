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
    <link rel="stylesheet" href="assets/css/main.css"> <!-- Vincula tu archivo CSS -->
</head>
<body>
    <!-- Barra de Navegación -->
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="screens/list.php">Gestión de Pantallas</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main>
        <h1>Bienvenido al Dashboard</h1>
        <p>Bienvenido, aquí puedes gestionar tus pantallas y contenido multimedia.</p>

        <!-- Ejemplo: Mostrar pantallas recientes -->
        <section>
            <h2>Pantallas Recientes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Dominio</th>
                        <th>Fecha de Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM screens ORDER BY created_at DESC LIMIT 5");
                    while ($row = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['domain']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Pie de Página -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> - Gestión de Pantallas</p>
    </footer>
</body>
</html>
