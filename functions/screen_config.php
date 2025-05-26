<?php
// Incluye autenticación y conexión PDO
include '../includes/auth.php';
include '../includes/db.php';

$screen_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = $error = "";

// Obtener información de pantalla
$stmt = $pdo->prepare("SELECT name, domain FROM screens WHERE id = ?");
$stmt->execute([$screen_id]);
$screen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$screen) {
    die("Pantalla no encontrada.");
}

// Ruta base de los archivos multimedia
$upload_dir = realpath(__DIR__ . '/../assets/uploads') . DIRECTORY_SEPARATOR;

// Obtener último archivo multimedia relacionado
$stmt = $pdo->prepare("SELECT id, file_path FROM media WHERE screen_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$stmt->execute([$screen_id]);
$last_media = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar nueva subida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_media'])) {
    $file_name = time() . "_" . basename($_FILES["new_media"]["name"]);
    $relative_path = "assets/uploads/" . $file_name;
    $absolute_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES["new_media"]["tmp_name"], $absolute_path)) {
        // Eliminar archivo anterior si existe
        if ($last_media && file_exists("../" . $last_media['file_path'])) {

            @chmod("../" . $last_media['file_path'], 0666); // intentar permitir escritura
            unlink("../" . $last_media['file_path']); // eliminar archivo
            // Eliminar registro anterior
            $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
            $stmt->execute([$last_media['id']]);
        }

        // Guardar nuevo registro en la base de datos
        $stmt = $pdo->prepare("INSERT INTO media (screen_id, file_path) VALUES (?, ?)");
        if ($stmt->execute([$screen_id, $relative_path])) {
            $success = "Contenido actualizado correctamente.";
        } else {
            $error = "Error al guardar en la base de datos.";
        }
    } else {
        $error = "Error al subir el archivo.";
    }
}

// Obtener media actual para mostrarla
$stmt = $pdo->prepare("SELECT file_path FROM media WHERE screen_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$stmt->execute([$screen_id]);
$media = $stmt->fetch(PDO::FETCH_ASSOC);
$media_path = $media ? "../" . $media['file_path'] : null;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Editar Multimedia - <?= htmlspecialchars($screen['name']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Editar contenido de la pantalla: <?= htmlspecialchars($screen['name']) ?></h1>
        <p><strong>Dominio:</strong> <?= htmlspecialchars($screen['domain']) ?></p>

        <?php if ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($media_path): ?>
            <h3>Contenido actual:</h3>
            <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $media_path)): ?>
                <img src="<?= htmlspecialchars($media_path) ?>" alt="Media" width="300">
            <?php else: ?>
                <p><a href="<?= htmlspecialchars($media_path) ?>" target="_blank">Ver archivo</a></p>
            <?php endif; ?>
        <?php endif; ?>

        <h3>Subir nuevo contenido multimedia:</h3>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="new_media" required><br><br>
            <button type="submit">Actualizar</button>
        </form>

        <p><a href="../index.php">← Volver al inicio</a></p>
    </div>
</body>

</html>