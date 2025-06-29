<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === null) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al Sistema</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #121212;
            --card-dark: #1e1e1e;
            --text-light: #e0e0e0;
            --accent-color: #00e676;
            --shadow-glow: 0 0 25px rgba(0, 230, 118, 0.4);
            --danger-color: #e74c3c;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            text-align: center;
            color: var(--text-light);
        }
        .welcome-container {
            background-color: var(--card-dark);
            padding: 60px 80px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.1);
            animation: scaleIn 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            max-width: 550px;
            width: 90%;
        }
        .welcome-container h1 {
            font-size: 4rem;
            font-weight: 700;
            color: var(--accent-color);
            text-shadow: 0 0 10px rgba(0, 230, 118, 0.3);
            margin: 0;
        }
        .welcome-container p {
            font-size: 1.6rem;
            font-weight: 500;
            color: #b0b0b0;
            margin: 10px 0 20px;
        }
        .welcome-container strong {
            display: block;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-light);
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 40px;
            padding: 18px 50px;
            background-color: var(--danger-color);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 1px;
            box-shadow: 0 10px 30px rgba(231, 76, 60, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .logout-btn:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(231, 76, 60, 0.5);
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <h1>¡Hola!</h1>
        <p>Has iniciado sesión como:</p>
        <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
        <a href="cerrarSesion.php" class="logout-btn">Cerrar Sesión</a>
    </div>
</body>
</html>