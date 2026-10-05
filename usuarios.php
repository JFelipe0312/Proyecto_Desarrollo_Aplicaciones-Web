<?php
require_once 'config/session.php';
require_once 'config/db.php';
verificarAdmin();

$conn = conectar();
$mensaje = '';
$tipo_mensaje = '';

// Crear usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'crear') {
        $nombre   = trim($_POST['nombre']);
        $usuario  = trim($_POST['usuario']);
        $password = $_POST['password'];
        $rol      = $_POST['rol'];

        if ($nombre && $usuario && $password && $rol) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, usuario, contrasena, rol) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $nombre, $usuario, $hash, $rol);
            if ($stmt->execute()) {
                $mensaje = '✅ Usuario creado exitosamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = '❌ El nombre de usuario ya existe.';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = '❌ Completa todos los campos.';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'toggle') {
        $id = (int)$_POST['id_usuario'];
        if ($id !== $_SESSION['id_usuario']) { // No desactivarse a sí mismo
            $conn->query("UPDATE usuarios SET activo = NOT activo WHERE id_usuario = $id");
            $mensaje = '✅ Estado del usuario actualizado.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '⚠️ No puedes desactivar tu propia cuenta.';
            $tipo_mensaje = 'error';
        }
    } elseif ($accion === 'cambiar_password') {
        $id  = (int)$_POST['id_usuario'];
        $pwd = $_POST['nueva_password'];
        if (strlen($pwd) >= 6) {
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET contrasena=? WHERE id_usuario=?");
            $stmt->bind_param('si', $hash, $id);
            $stmt->execute();
            $mensaje = '✅ Contraseña actualizada.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ La contraseña debe tener al menos 6 caracteres.';
            $tipo_mensaje = 'error';
        }
    }
}

$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY rol, nombre")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Usuarios</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<?php include 'includes/head_styles.php'; ?>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Gestión de Usuarios</h1>
    <p>Administra los accesos al sistema</p>
  </div>

  <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?>"><?= $mensaje ?></div>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns:1fr 1.6fr; gap:20px; align-items:start;">

    <!-- Formulario crear usuario -->
    <div class="card">
      <h3>➕ Nuevo Usuario</h3>
      <form method="POST">
        <input type="hidden" name="accion" value="crear">
        <div class="form-group" style="margin-bottom:12px;">
          <label>Nombre Completo</label>
          <input type="text" name="nombre" placeholder="Ej: Carlos López" required>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label>Usuario (login)</label>
          <input type="text" name="usuario" placeholder="Ej: carlos_lopez" required>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
          <label>Contraseña</label>
          <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
        </div>
        <div class="form-group" style="margin-bottom:16px;">
          <label>Rol</label>
          <select name="rol" required>
            <option value="operario">Operario</option>
            <option value="administrador">Administrador</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Crear Usuario</button>
      </form>
    </div>

    <!-- Lista de usuarios -->
    <div class="card">
      <h3>👥 Usuarios del Sistema</h3>
      <table class="tabla">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><strong><?= htmlspecialchars($u['nombre']) ?></strong></td>
            <td style="color:rgba(255,255,255,.5);"><?= htmlspecialchars($u['usuario']) ?></td>
            <td>
              <span style="font-size:.75rem; font-weight:700; color:<?= $u['rol']==='administrador' ? '#FFA726' : '#42A5F5' ?>;">
                <?= ucfirst($u['rol']) ?>
              </span>
            </td>
            <td>
              <span style="color:<?= $u['activo'] ? '#76FF03' : '#ef5350' ?>; font-size:.8rem; font-weight:600;">
                <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
              </span>
            </td>
            <td>
              <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <!-- Toggle activo -->
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="accion" value="toggle">
                  <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                  <button type="submit" class="btn btn-sm btn-secondary">
                    <?= $u['activo'] ? '🔴 Desactivar' : '🟢 Activar' ?>
                  </button>
                </form>
                <!-- Cambiar contraseña -->
                <button class="btn btn-sm btn-secondary" onclick="togglePwd(<?= $u['id_usuario'] ?>)">🔑</button>
              </div>
              <!-- Form cambiar password (oculto) -->
              <form method="POST" id="pwd-<?= $u['id_usuario'] ?>" style="display:none; margin-top:8px;">
                <input type="hidden" name="accion" value="cambiar_password">
                <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                <div style="display:flex; gap:6px;">
                  <input type="password" name="nueva_password" placeholder="Nueva contraseña" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:6px;padding:6px 10px;color:#f0f4f8;font-size:.8rem;flex:1;">
                  <button type="submit" class="btn btn-sm btn-primary">✓</button>
                </div>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
function togglePwd(id) {
  const f = document.getElementById('pwd-' + id);
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>
