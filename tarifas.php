<?php
require_once 'config/session.php';
require_once 'config/db.php';
verificarAdmin();

$conn = conectar();
$mensaje = '';
$tipo_mensaje = '';

// Actualizar tarifa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)$_POST['id_tarifa'];
    $hora     = (float)str_replace(',', '.', $_POST['tarifa_hora']);
    $dia      = (float)str_replace(',', '.', $_POST['tarifa_dia']);

    $stmt = $conn->prepare("UPDATE tarifas SET tarifa_hora=?, tarifa_dia=? WHERE id_tarifa=?");
    $stmt->bind_param('ddi', $hora, $dia, $id);
    if ($stmt->execute()) {
        $mensaje = '✅ Tarifa actualizada correctamente.';
        $tipo_mensaje = 'success';
    } else {
        $mensaje = '❌ Error al actualizar la tarifa.';
        $tipo_mensaje = 'error';
    }
}

$tarifas = $conn->query("SELECT * FROM tarifas ORDER BY id_tarifa")->fetch_all(MYSQLI_ASSOC);
$conn->close();

$iconos = ['carro' => '🚗', 'moto' => '🏍️', 'bicicleta' => '🚲'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Tarifas</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<?php include 'includes/head_styles.php'; ?>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Gestión de Tarifas</h1>
    <p>Configura el valor por hora y por día para cada tipo de vehículo</p>
  </div>

  <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?>"><?= $mensaje ?></div>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:20px;">
    <?php foreach ($tarifas as $t): ?>
    <div class="card">
      <h3><?= $iconos[$t['tipo_vehiculo']] ?? '' ?> <?= ucfirst($t['tipo_vehiculo']) ?></h3>
      <form method="POST">
        <input type="hidden" name="id_tarifa" value="<?= $t['id_tarifa'] ?>">
        <div class="form-group" style="margin-bottom:14px;">
          <label>Tarifa por Hora ($COP)</label>
          <input type="number" name="tarifa_hora" value="<?= $t['tarifa_hora'] ?>" min="0" step="50" required>
        </div>
        <div class="form-group" style="margin-bottom:16px;">
          <label>Tarifa por Día ($COP)</label>
          <input type="number" name="tarifa_dia" value="<?= $t['tarifa_dia'] ?>" min="0" step="500" required>
        </div>
        <p style="font-size:.75rem; color:rgba(255,255,255,.3); margin-bottom:12px;">
          Última actualización: <?= date('d/m/Y H:i', strtotime($t['actualizado_en'])) ?>
        </p>
        <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
</main>
</body>
</html>
