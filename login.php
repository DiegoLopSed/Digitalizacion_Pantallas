<?php
session_start();
include 'includes/db.php';

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Buscar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verificar credenciales
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['id']; // Guardar el ID del usuario en la sesión
        header("Location: index.php");   // Redirigir al dashboard
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <span>FFT</span>
            </div>
            <form action="login.php" method="POST">
                <input type="text" name="username" placeholder="Usuario" class="input-field" required>
                <input type="password" name="password" placeholder="Contraseña" class="input-field" required>
                <button type="submit" class="btn">Iniciar Sesión</button>
            </form>
            <!-- Mostrar error si existe -->
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <div class="register">
                <p>¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>
            </div>
        </div>
        <div class="help-icon">
            <a href="#"><span>?</span></a>
        </div>
    </div>
</body>
</html>
