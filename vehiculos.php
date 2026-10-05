<?php
require_once 'config/session.php';
require_once 'config/db.php';
verificarSesion();

$conn = conectar();
$result = $conn->query("
    SELECT t.id_ticket, t.placa, t.tipo_vehiculo, t.hora_entrada,
           v.marca, v.color,
           TIMESTAMPDIFF(MINUTE, t.hora_entrada, NOW()) AS minutos
    FROM tickets t
    LEFT JOIN vehiculos v ON v.placa = t.placa
    WHERE t.estado = 'activo'
    ORDER BY t.hora_entrada ASC
");
$vehiculos = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

function formatTiempo($min) {
    if ($min < 60) return "$min min";
    $h = floor($min / 60);
    $m = $min % 60;
    return "{$h}h {$m}m";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Vehículos en Patio</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<?php include 'includes/head_styles.php'; ?>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Vehículos en Patio</h1>
    <p><?= count($vehiculos) ?> vehículo(s) actualmente en el parqueadero</p>
  </div>

  <div class="card">
    <?php if (empty($vehiculos)): ?>
      <div style="text-align:center; padding:48px; color:rgba(255,255,255,.3);">
        <div style="font-size:3rem; margin-bottom:12px;">🅿️</div>
        <p>No hay vehículos en el parqueadero en este momento.</p>
      </div>
    <?php else: ?>
    <table class="tabla">
      <thead>
        <tr>
          <th>Ticket</th>
          <th>Placa</th>
          <th>Tipo</th>
          <th>Marca / Color</th>
          <th>Entrada</th>
          <th>Tiempo</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vehiculos as $v): ?>
        <tr>
          <td><span class="badge">#<?= str_pad($v['id_ticket'], 6, '0', STR_PAD_LEFT) ?></span></td>
          <td><strong><?= htmlspecialchars($v['placa']) ?></strong></td>
          <td><?= ucfirst($v['tipo_vehiculo']) ?></td>
          <td><?= htmlspecialchars(trim($v['marca'] . ' ' . $v['color'])) ?: '—' ?></td>
          <td><?= date('d/m H:i', strtotime($v['hora_entrada'])) ?></td>
          <td><?= formatTiempo($v['minutos']) ?></td>
          <td>
            <a href="salida.php?placa=<?= urlencode($v['placa']) ?>" class="btn btn-sm btn-danger">Salida</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
