<?php
include '../includes/auth.php';
include '../includes/db.php';

$screen_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($screen_id <= 0) {
    die("ID de pantalla no válido.");
}

// Obtener datos de la pantalla
$stmt = $pdo->prepare("SELECT domain FROM screens WHERE id = ?");
$stmt->execute([$screen_id]);
$screen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$screen) {
    die("Pantalla no encontrada.");
}

// Eliminar archivo PHP de la pantalla
$screen_file = "../screens/" . $screen['domain'] . ".php";
if (file_exists($screen_file)) {
    @chmod($screen_file, 0666);
    unlink($screen_file);
}

// Eliminar archivos multimedia locales y registros
$stmt = $pdo->prepare("SELECT file_path FROM media WHERE screen_id = ?");
$stmt->execute([$screen_id]);

while ($media = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $path = $media['file_path'];
    if (!filter_var($path, FILTER_VALIDATE_URL)) {
        $full_path = "../" . $path;
        if (file_exists($full_path)) {
            @chmod($full_path, 0666);
            unlink($full_path);
        }
    }
}

// Eliminar registros de media
$pdo->prepare("DELETE FROM media WHERE screen_id = ?")->execute([$screen_id]);

// Eliminar pantalla
$pdo->prepare("DELETE FROM screens WHERE id = ?")->execute([$screen_id]);

header("Location: ../index.php?deleted=1");
exit;
