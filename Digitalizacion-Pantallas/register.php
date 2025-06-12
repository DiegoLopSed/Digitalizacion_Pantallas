<?php
session_start();
include 'includes/db.php'; // Incluye la conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtiene y sanitiza los datos del formulario
    $username = trim(htmlspecialchars($_POST['username']));
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validación de errores
    $error = '';
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Por favor, completa todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verifica si el usuario o correo ya existen en la base de datos
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = "El nombre de usuario o correo ya están en uso.";
        } else {
            // Inserta al nuevo usuario
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $_SESSION['user'] = $pdo->lastInsertId();
                header("Location: index.php");
                exit;
            } else {
                $error = "Error al registrar el usuario. Inténtalo nuevamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="icon" type="image/png" href="../assets/images/common/FFF_LogoNormal.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <span>F</span>
                <span>F</span>
                <span>T</span>
            </div>
            <form action="register.php" method="POST">
                <!-- Mensaje de error -->
                <?php if (!empty($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <!-- Campos del formulario -->
                <input type="text" name="username" placeholder="Usuario" class="input-field" required>
                <input type="email" name="email" placeholder="Correo electrónico" class="input-field" required>
                <input type="password" name="password" placeholder="Contraseña" class="input-field" required>
                <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" class="input-field" required>
                <button type="submit" class="btn">Registrar</button>
            </form>
            <div class="register">
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
            </div>
        </div>
        <div class="help-icon">
            <a href="#"><span>?</span></a>
        </div>
    </div>
</body>
</html>