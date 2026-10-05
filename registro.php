<?php
// =============================================
// Página de registro de nuevo usuario
// Archivo: registro.php
// =============================================
require_once 'config/session.php';
require_once 'config/db.php';

// Si ya está logueado, redirigir al dashboard
if (estaLogueado()) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$exito   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre']    ?? '');
    $usuario   = trim($_POST['usuario']   ?? '');
    $contrasena = $_POST['contrasena']    ?? '';
    $confirmar  = $_POST['confirmar']     ?? '';
    $rol        = $_POST['rol']           ?? 'operario';

    // Validaciones
    if (!$nombre || !$usuario || !$contrasena || !$confirmar) {
        $error = 'Por favor completa todos los campos.';
    } elseif (strlen($usuario) < 4) {
        $error = 'El nombre de usuario debe tener al menos 4 caracteres.';
    } elseif (strlen($contrasena) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($contrasena !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!in_array($rol, ['operario', 'administrador'])) {
        $error = 'Rol no válido.';
    } else {
        $conn = conectar();

        // Verificar si el usuario ya existe
        $check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
        $check->bind_param('s', $usuario);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Ese nombre de usuario ya está en uso. Elige otro.';
        } else {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, usuario, contrasena, rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $nombre, $usuario, $hash, $rol);

            if ($stmt->execute()) {
                $exito = '¡Cuenta creada exitosamente! Ya puedes iniciar sesión.';
            } else {
                $error = 'Ocurrió un error al crear la cuenta. Intenta nuevamente.';
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Crear Cuenta</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --verde: #4CAF50;
    --verde-oscuro: #2E7D32;
    --gris-oscuro: #1a1a2e;
    --gris-medio: #2d2d44;
    --gris-claro: #3a3a55;
    --blanco: #f0f4f8;
    --acento: #76FF03;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

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
    padding: 24px;
  }

  .register-card {
    background: var(--gris-medio);
    border-radius: 16px;
    padding: 44px 40px;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 24px 60px rgba(0,0,0,.5);
    border: 1px solid rgba(255,255,255,.07);
    animation: fadeUp .35s ease both;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* LOGO */
  .logo {
    text-align: center;
    margin-bottom: 28px;
  }
  .logo-icon { font-size: 42px; display: block; margin-bottom: 6px; }
  .logo h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 2.2rem;
    color: var(--acento);
    letter-spacing: 4px;
    line-height: 1;
  }
  .logo p {
    color: rgba(255,255,255,.35);
    font-size: .78rem;
    margin-top: 4px;
    letter-spacing: 2px;
    text-transform: uppercase;
  }

  /* DIVIDER */
  .divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
  }
  .divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,.08);
  }
  .divider span {
    font-size: .72rem;
    color: rgba(255,255,255,.3);
    letter-spacing: 2px;
    text-transform: uppercase;
  }

  /* FORM */
  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
  }
  .form-group.full { grid-column: 1 / -1; }

  label {
    display: block;
    color: rgba(255,255,255,.5);
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
  }

  input[type="text"],
  input[type="password"],
  select {
    width: 100%;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 8px;
    padding: 11px 14px;
    color: var(--blanco);
    font-family: 'DM Sans', sans-serif;
    font-size: .9rem;
    transition: border-color .2s, background .2s;
  }
  input:focus, select:focus {
    outline: none;
    border-color: var(--acento);
    background: rgba(118,255,3,.04);
  }
  input::placeholder { color: rgba(255,255,255,.2); }
  select option { background: var(--gris-medio); }

  /* Indicador de fortaleza de contraseña */
  .pwd-strength {
    display: flex;
    gap: 4px;
    margin-top: 6px;
  }
  .pwd-bar {
    height: 3px;
    flex: 1;
    border-radius: 2px;
    background: rgba(255,255,255,.1);
    transition: background .3s;
  }
  .pwd-label {
    font-size: .7rem;
    color: rgba(255,255,255,.3);
    margin-top: 4px;
    min-height: 16px;
    transition: color .3s;
  }

  /* Rol selector */
  .rol-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }
  .rol-option input[type="radio"] { display: none; }
  .rol-option label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 8px;
    cursor: pointer;
    font-size: .82rem;
    font-weight: 500;
    color: rgba(255,255,255,.55);
    text-transform: none;
    letter-spacing: 0;
    transition: all .2s;
  }
  .rol-option input[type="radio"]:checked + label {
    border-color: var(--acento);
    background: rgba(118,255,3,.08);
    color: var(--blanco);
  }
  .rol-option .rol-icon { font-size: 1.2rem; }

  /* Botón */
  .btn-register {
    width: 100%;
    background: var(--verde);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 14px;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.15rem;
    letter-spacing: 3px;
    cursor: pointer;
    transition: background .2s, transform .1s, box-shadow .2s;
    margin-top: 6px;
  }
  .btn-register:hover {
    background: var(--verde-oscuro);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(76,175,80,.3);
  }
  .btn-register:active { transform: translateY(0); }

  /* Alertas */
  .alert {
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: .875rem;
    display: flex;
    align-items: flex-start;
    gap: 8px;
  }
  .alert-error {
    background: rgba(244,67,54,.13);
    border: 1px solid rgba(244,67,54,.35);
    color: #ef9a9a;
  }
  .alert-success {
    background: rgba(76,175,80,.13);
    border: 1px solid rgba(76,175,80,.35);
    color: #a5d6a7;
  }

  /* Link de vuelta al login */
  .login-link {
    text-align: center;
    margin-top: 22px;
    font-size: .82rem;
    color: rgba(255,255,255,.3);
  }
  .login-link a {
    color: var(--acento);
    text-decoration: none;
    font-weight: 600;
  }
  .login-link a:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="register-card">

  <div class="logo">
    <span class="logo-icon">🅿️</span>
    <h1>PARKING SGP</h1>
    <p>Crear nueva cuenta</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($exito): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($exito) ?></div>
    <div style="text-align:center; margin-top:4px; margin-bottom:20px;">
      <a href="index.php" style="color:var(--acento); font-weight:600; text-decoration:none; font-size:.9rem;">
        → Ir al inicio de sesión
      </a>
    </div>
  <?php endif; ?>

  <?php if (!$exito): ?>
  <form method="POST" id="formRegistro" novalidate>

    <div class="form-row">
      <!-- Nombre completo -->
      <div class="form-group full">
        <label>Nombre Completo</label>
        <input
          type="text"
          name="nombre"
          placeholder="Ej: Carlos Rodríguez"
          value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
          required
          autocomplete="name"
        >
      </div>

      <!-- Usuario -->
      <div class="form-group full">
        <label>Nombre de Usuario</label>
        <input
          type="text"
          name="usuario"
          placeholder="Mínimo 4 caracteres"
          value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
          required
          autocomplete="username"
          minlength="4"
        >
      </div>

      <!-- Contraseña -->
      <div class="form-group">
        <label>Contraseña</label>
        <input
          type="password"
          name="contrasena"
          id="contrasena"
          placeholder="Mínimo 6 caracteres"
          required
          autocomplete="new-password"
        >
        <div class="pwd-strength">
          <div class="pwd-bar" id="bar1"></div>
          <div class="pwd-bar" id="bar2"></div>
          <div class="pwd-bar" id="bar3"></div>
          <div class="pwd-bar" id="bar4"></div>
        </div>
        <div class="pwd-label" id="pwdLabel"></div>
      </div>

      <!-- Confirmar contraseña -->
      <div class="form-group">
        <label>Confirmar Contraseña</label>
        <input
          type="password"
          name="confirmar"
          id="confirmar"
          placeholder="Repite la contraseña"
          required
          autocomplete="new-password"
        >
        <div class="pwd-label" id="matchLabel" style="margin-top:10px;"></div>
      </div>
    </div>

    <!-- Rol -->
    <div class="form-group" style="margin-bottom:20px;">
      <label style="margin-bottom:10px; display:block;">Rol</label>
      <div class="rol-options">
        <div class="rol-option">
          <input type="radio" name="rol" id="rol-operario" value="operario"
            <?= (!isset($_POST['rol']) || $_POST['rol'] === 'operario') ? 'checked' : '' ?>>
          <label for="rol-operario">
            <span class="rol-icon">👷</span>
            <div>
              <div style="font-weight:600;">Operario</div>
              <div style="font-size:.7rem; opacity:.5; margin-top:1px;">Entradas y salidas</div>
            </div>
          </label>
        </div>
        <div class="rol-option">
          <input type="radio" name="rol" id="rol-admin" value="administrador"
            <?= (isset($_POST['rol']) && $_POST['rol'] === 'administrador') ? 'checked' : '' ?>>
          <label for="rol-admin">
            <span class="rol-icon">🛡️</span>
            <div>
              <div style="font-weight:600;">Administrador</div>
              <div style="font-size:.7rem; opacity:.5; margin-top:1px;">Acceso total</div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <button type="submit" class="btn-register">✅ Crear Cuenta</button>
  </form>
  <?php endif; ?>

  <p class="login-link">
    ¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a>
  </p>

</div>

<script>
  // Indicador de fortaleza de contraseña
  const pwdInput  = document.getElementById('contrasena');
  const confirmInput = document.getElementById('confirmar');
  const bars      = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3'), document.getElementById('bar4')];
  const pwdLabel  = document.getElementById('pwdLabel');
  const matchLabel = document.getElementById('matchLabel');

  const colors = ['#ef5350', '#FFA726', '#FFEE58', '#76FF03'];
  const labels = ['Muy débil', 'Débil', 'Moderada', 'Fuerte'];

  function calcStrength(pwd) {
    let score = 0;
    if (pwd.length >= 6)  score++;
    if (pwd.length >= 10) score++;
    if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd) && /[^A-Za-z0-9]/.test(pwd)) score++;
    return score;
  }

  pwdInput.addEventListener('input', () => {
    const pwd = pwdInput.value;
    if (!pwd) {
      bars.forEach(b => b.style.background = 'rgba(255,255,255,.1)');
      pwdLabel.textContent = '';
      return;
    }
    const strength = calcStrength(pwd);
    bars.forEach((b, i) => {
      b.style.background = i < strength ? colors[strength - 1] : 'rgba(255,255,255,.1)';
    });
    pwdLabel.textContent   = labels[strength - 1] || '';
    pwdLabel.style.color   = colors[strength - 1] || 'rgba(255,255,255,.3)';
    checkMatch();
  });

  function checkMatch() {
    const pwd  = pwdInput.value;
    const conf = confirmInput.value;
    if (!conf) { matchLabel.textContent = ''; return; }
    if (pwd === conf) {
      matchLabel.textContent = '✓ Las contraseñas coinciden';
      matchLabel.style.color = '#76FF03';
    } else {
      matchLabel.textContent = '✗ No coinciden';
      matchLabel.style.color = '#ef5350';
    }
  }

  confirmInput.addEventListener('input', checkMatch);
</script>

</body>
</html>
