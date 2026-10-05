<?php
// =============================================
// Página de inicio de sesión
// Archivo: index.php
// =============================================
require_once 'config/session.php';
require_once 'config/db.php';

if (estaLogueado()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($usuario && $contrasena) {
        $conn = conectar();
        $stmt = $conn->prepare("SELECT id_usuario, nombre, contrasena, rol FROM usuarios WHERE usuario = ? AND activo = 1");
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($contrasena, $row['contrasena'])) {
                $_SESSION['id_usuario'] = $row['id_usuario'];
                $_SESSION['nombre']     = $row['nombre'];
                $_SESSION['rol']        = $row['rol'];
                header('Location: dashboard.php');
                exit();
            }
        }
        $error = 'Usuario o contraseña incorrectos.';
        $conn->close();
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Iniciar Sesión</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --verde: #4CAF50;
    --verde-oscuro: #2E7D32;
    --azul: #1565C0;
    --gris-oscuro: #1a1a2e;
    --gris-medio: #2d2d44;
    --blanco: #f0f4f8;
    --acento: #76FF03;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--gris-oscuro);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-image: repeating-linear-gradient(
      45deg,
      rgba(255,255,255,.015) 0,
      rgba(255,255,255,.015) 1px,
      transparent 0,
      transparent 50%
    );
    background-size: 20px 20px;
  }
  .login-card {
    background: var(--gris-medio);
    border-radius: 16px;
    padding: 48px 40px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 24px 60px rgba(0,0,0,.5);
    border: 1px solid rgba(255,255,255,.07);
  }
  .logo {
    text-align: center;
    margin-bottom: 32px;
  }
  .logo-icon {
    font-size: 48px;
    display: block;
    margin-bottom: 8px;
  }
  .logo h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.6rem;
    color: var(--acento);
    letter-spacing: 4px;
    line-height: 1;
  }
  .logo p { color: rgba(255,255,255,.4); font-size: .85rem; margin-top: 4px; letter-spacing: 2px; text-transform: uppercase; }
  .form-group { margin-bottom: 20px; }
  label { display: block; color: rgba(255,255,255,.6); font-size: .8rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; }
  input[type="text"], input[type="password"] {
    width: 100%;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px;
    padding: 12px 16px;
    color: var(--blanco);
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem;
    transition: border-color .2s;
  }
  input:focus { outline: none; border-color: var(--acento); }
  input::placeholder { color: rgba(255,255,255,.2); }

  .btn-login {
    width: 100%;
    background: var(--verde);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 14px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.2rem;
    letter-spacing: 3px;
    cursor: pointer;
    transition: background .2s, transform .1s;
    margin-top: 8px;
  }
  .btn-login:hover { background: var(--verde-oscuro); transform: translateY(-1px); }

  /* ── Botón Registrarse ── */
  .btn-register {
    display: block;
    width: 100%;
    background: transparent;
    color: rgba(255,255,255,.65);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 8px;
    padding: 13px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.1rem;
    letter-spacing: 3px;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    transition: background .2s, border-color .2s, color .2s, transform .1s;
    margin-top: 10px;
  }
  .btn-register:hover {
    background: rgba(118,255,3,.08);
    border-color: var(--acento);
    color: var(--acento);
    transform: translateY(-1px);
  }

  .divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 20px 0 4px;
  }
  .divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,.08);
  }
  .divider span { font-size: .7rem; color: rgba(255,255,255,.25); letter-spacing: 1.5px; text-transform: uppercase; }

  .error {
    background: rgba(244,67,54,.15);
    border: 1px solid rgba(244,67,54,.4);
    color: #ef9a9a;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: .875rem;
    margin-bottom: 20px;
  }
  .hint { text-align:center; color: rgba(255,255,255,.25); font-size: .75rem; margin-top: 22px; }
</style>
</head>
<body>
<div class="login-card">
  <div class="logo">
    <span class="logo-icon">🅿️</span>
    <h1>PARKING SGP</h1>
    <p>Sistema de Gestión de Parqueadero</p>
  </div>

  <?php if ($error): ?>
    <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Usuario</label>
      <input type="text" name="usuario" placeholder="Ingresa tu usuario" autocomplete="username" required>
    </div>
    <div class="form-group">
      <label>Contraseña</label>
      <input type="password" name="contrasena" placeholder="••••••••" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn-login">Ingresar</button>
  </form>

  <div class="divider"><span>o</span></div>

  <a href="registro.php" class="btn-register">✏️ Crear Cuenta Nueva</a>

  <p class="hint">Usuario: admin / operario &nbsp;|&nbsp; Contraseña: password</p>
</div>
</body>
</html>
