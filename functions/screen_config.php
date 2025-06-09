<?php
include '../includes/auth.php';
include '../includes/db.php';

// Evitar cacheo para forzar siempre nueva carga
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies

$screen_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si no se proporciona un ID válido, redirigir a index
if ($screen_id <= 0) {
    header('Location: ../index.php');
    exit;
}

$success = $error = "";

// Obtener información de pantalla
$stmt = $pdo->prepare("SELECT name, domain FROM screens WHERE id = ?");
$stmt->execute([$screen_id]);
$screen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$screen) {
    // Redirigir si pantalla no existe
    header('Location: ../index.php');
    exit;
}

// Ruta de uploads
$upload_dir = realpath(__DIR__ . '/../assets/uploads') . DIRECTORY_SEPARATOR;

// Último medio existente
$stmt = $pdo->prepare("SELECT id, file_path FROM media WHERE screen_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$stmt->execute([$screen_id]);
$last_media = $stmt->fetch(PDO::FETCH_ASSOC);

// Proceso al enviar formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';

    if ($last_media) {
        // Si el último contenido es un archivo local, borrarlo
        if (!filter_var($last_media['file_path'], FILTER_VALIDATE_URL) && file_exists("../" . $last_media['file_path'])) {
            @chmod("../" . $last_media['file_path'], 0666);
            unlink("../" . $last_media['file_path']);
        }

        // Eliminar registro anterior
        $pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$last_media['id']]);
    }

    if ($mode === 'file' && isset($_FILES['new_media'])) {
        $file_name = time() . "_" . basename($_FILES["new_media"]["name"]);
        $relative_path = "assets/uploads/" . $file_name;
        $absolute_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES["new_media"]["tmp_name"], $absolute_path)) {
            $stmt = $pdo->prepare("INSERT INTO media (screen_id, file_path) VALUES (?, ?)");
            if ($stmt->execute([$screen_id, $relative_path])) {
                $success = "Contenido actualizado correctamente (archivo).";
            } else {
                $error = "Error al guardar el nuevo archivo.";
            }
        } else {
            $error = "Error al subir el archivo.";
        }
    } elseif ($mode === 'url' && !empty($_POST['external_url'])) {
        $url = trim($_POST['external_url']);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $error = "La URL ingresada no es válida.";
        } else {
            // Convertir enlaces de YouTube a formato embed
            if (preg_match('#https?://(www\.)?youtube\.com/watch\?v=([^\s&]+)#', $url, $matches)) {
                $url = 'https://www.youtube.com/embed/' . $matches[2];
            } elseif (preg_match('#https?://youtu\.be/([^\s&]+)#', $url, $matches)) {
                $url = 'https://www.youtube.com/embed/' . $matches[1];
            }

            $stmt = $pdo->prepare("INSERT INTO media (screen_id, file_path) VALUES (?, ?)");
            if ($stmt->execute([$screen_id, $url])) {
                $success = "Contenido actualizado correctamente (enlace externo).";
            } else {
                $error = "Error al guardar la URL.";
            }
        }
    }

    // Implementamos Post/Redirect/Get para evitar reenvío al refrescar o volver atrás
    if ($success || $error) {
        // Para pasar mensajes con GET (si quieres mostrar luego en GET), codificamos en la URL
        $params = [];
        if ($success) $params['success'] = urlencode($success);
        if ($error) $params['error'] = urlencode($error);

        $location = $_SERVER['PHP_SELF'] . '?id=' . $screen_id;
        if (!empty($params)) {
            $location .= '&' . http_build_query($params);
        }

        header('Location: ' . $location);
        exit;
    }
}

// Obtener mensajes enviados por GET luego del redirect PRG
if (isset($_GET['success'])) {
    $success = urldecode($_GET['success']);
}
if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}

// Obtener contenido actual
$stmt = $pdo->prepare("SELECT file_path FROM media WHERE screen_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$stmt->execute([$screen_id]);
$media = $stmt->fetch(PDO::FETCH_ASSOC);
$media_path = $media['file_path'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar contenido - <?= htmlspecialchars($screen['name']) ?></title>
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

        .form-section {
            display: none;
            margin-top: 10px;
        }

        iframe {
            width: 100%;
            height: 300px;
            border: none;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/screen_config.css">
    <script>
        function toggleMode() {
            const mode = document.getElementById("mode").value;
            document.getElementById("file-section").style.display = (mode === 'file') ? 'block' : 'none';
            document.getElementById("url-section").style.display = (mode === 'url') ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Editar contenido de: <?= htmlspecialchars($screen['name']) ?></h1>
        <p><strong>Dominio:</strong> <?= htmlspecialchars($screen['domain']) ?></p>

        <?php if ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($media_path): ?>
            <h3>Contenido actual:</h3>
            <?php if (filter_var($media_path, FILTER_VALIDATE_URL)): ?>
                <iframe 
                    src="<?= htmlspecialchars($media_path) ?>" 
                    allowfullscreen 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share">
                </iframe>
                <p class="text-sm text-gray-500">Este contenido proviene de un enlace externo.</p>
            <?php elseif (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $media_path)): ?>
                <img src="../<?= htmlspecialchars($media_path) ?>" alt="Media" width="300">
            <?php else: ?>
                <p><a href="../<?= htmlspecialchars($media_path) ?>" target="_blank">Ver archivo</a></p>
            <?php endif; ?>
        <?php endif; ?>

        <h3>Actualizar contenido:</h3>
        <form method="post" enctype="multipart/form-data">
            <label for="mode"><strong>Tipo de contenido:</strong></label>
            <select id="mode" name="mode" onchange="toggleMode()" required>
                <option value="">Selecciona una opción</option>
                <option value="file">Subir archivo</option>
                <option value="url">Agregar enlace externo</option>
            </select>

            <div id="file-section" class="form-section">
                <p><input type="file" name="new_media" accept="image/*,video/*"></p>
            </div>

            <div id="url-section" class="form-section">
                <p><input type="url" name="external_url" placeholder="https://sitio.com/recurso" style="width: 100%;"></p>
            </div>

            <button type="submit" style="margin-top: 15px;">Actualizar</button>
        </form>

        <p><a href="../index.php">← Volver al inicio</a></p>
    </div>

    <script>
        // Mostrar sección activa si se recarga el formulario con valores
        document.addEventListener("DOMContentLoaded", toggleMode);
    </script>
</body>
</html>
