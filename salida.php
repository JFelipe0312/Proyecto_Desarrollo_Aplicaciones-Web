<?php
require_once 'config/session.php';
require_once 'config/db.php';
verificarSesion();

$mensaje = '';
$tipo_mensaje = '';
$ticket_info = null;
$cobro = null;

// Buscar vehículo
if (isset($_GET['placa'])) {
    $placa = strtoupper(trim($_GET['placa']));
    $conn = conectar();
    $stmt = $conn->prepare("
        SELECT t.id_ticket, t.placa, t.tipo_vehiculo, t.hora_entrada,
               ta.tarifa_hora, ta.tarifa_dia
        FROM tickets t
        JOIN tarifas ta ON ta.tipo_vehiculo = t.tipo_vehiculo
        WHERE t.placa = ? AND t.estado = 'activo'
    ");
    $stmt->bind_param('s', $placa);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $entrada = new DateTime($row['hora_entrada']);
        $ahora   = new DateTime();
        $diff    = $entrada->diff($ahora);
        $minutos = $diff->days * 1440 + $diff->h * 60 + $diff->i;
        $horas   = ceil($minutos / 60);

        $valor = $horas * $row['tarifa_hora'];
        // Si supera 12h, aplicar tarifa día
        if ($horas >= 12) {
            $dias = ceil($horas / 24);
            $valor = $dias * $row['tarifa_dia'];
        }

        $ticket_info = $row;
        $cobro = [
            'minutos'  => $minutos,
            'horas'    => $horas,
            'valor'    => $valor,
            'hora_entrada' => $entrada->format('d/m/Y H:i'),
            'hora_salida'  => $ahora->format('d/m/Y H:i'),
        ];
    } else {
        $mensaje = "No se encontró un vehículo activo con la placa <strong>$placa</strong>.";
        $tipo_mensaje = 'error';
    }
    $conn->close();
}

// Confirmar salida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $id_ticket = (int)$_POST['id_ticket'];
    $valor     = (float)$_POST['valor'];
    $id_usuario = $_SESSION['id_usuario'];
    $hora_salida = date('Y-m-d H:i:s');

    $entrada_dt = new DateTime($_POST['hora_entrada_raw']);
    $salida_dt  = new DateTime($hora_salida);
    $diff = $entrada_dt->diff($salida_dt);
    $minutos = $diff->days * 1440 + $diff->h * 60 + $diff->i;

    $conn = conectar();
    $stmt = $conn->prepare("UPDATE tickets SET hora_salida=?, minutos_permanencia=?, valor_pagado=?, estado='cerrado', id_usuario_salida=? WHERE id_ticket=?");
    $stmt->bind_param('sidii', $hora_salida, $minutos, $valor, $id_usuario, $id_ticket);
    $stmt->execute();
    $conn->close();

    $mensaje = "✅ Salida registrada. Valor cobrado: <strong>$" . number_format($valor, 0, ',', '.') . "</strong>";
    $tipo_mensaje = 'success';
    $ticket_info = null;
    $cobro = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Registrar Salida</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<?php include 'includes/head_styles.php'; ?>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Registrar Salida</h1>
    <p>Busca el vehículo por placa y confirma la salida</p>
  </div>

  <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?>"><?= $mensaje ?></div>
  <?php endif; ?>

  <!-- Buscador -->
  <div class="card" style="max-width:500px; margin-bottom:24px;">
    <form method="GET">
      <div class="form-group">
        <label>Buscar por Placa</label>
        <div style="display:flex; gap:10px;">
          <input type="text" name="placa" placeholder="Ej: ABC123" style="text-transform:uppercase; flex:1;" value="<?= htmlspecialchars($_GET['placa'] ?? '') ?>">
          <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
      </div>
    </form>
  </div>

  <?php if ($ticket_info && $cobro): ?>
  <div class="card" style="max-width:500px;">
    <h3 style="margin-bottom:20px; font-family:'Bebas Neue',sans-serif; letter-spacing:2px; font-size:1.3rem;">🧾 Detalle de Cobro</h3>
    <table class="detail-table">
      <tr><td>Placa</td><td><strong><?= htmlspecialchars($ticket_info['placa']) ?></strong></td></tr>
      <tr><td>Tipo</td><td><?= ucfirst($ticket_info['tipo_vehiculo']) ?></td></tr>
      <tr><td>Entrada</td><td><?= $cobro['hora_entrada'] ?></td></tr>
      <tr><td>Salida</td><td><?= $cobro['hora_salida'] ?></td></tr>
      <tr><td>Tiempo</td><td><?= $cobro['horas'] ?> hora(s) (<?= $cobro['minutos'] ?> min)</td></tr>
      <tr class="total-row"><td>TOTAL A PAGAR</td><td><strong style="color:#76FF03; font-size:1.3rem;">$<?= number_format($cobro['valor'], 0, ',', '.') ?></strong></td></tr>
    </table>
    <form method="POST" style="margin-top:20px;">
      <input type="hidden" name="id_ticket" value="<?= $ticket_info['id_ticket'] ?>">
      <input type="hidden" name="valor" value="<?= $cobro['valor'] ?>">
      <input type="hidden" name="hora_entrada_raw" value="<?= $ticket_info['hora_entrada'] ?>">
      <input type="hidden" name="confirmar" value="1">
      <button type="submit" class="btn btn-danger">🚪 Confirmar Salida y Cobro</button>
    </form>
  </div>
  <?php endif; ?>
</main>

<style>
.detail-table { width:100%; border-collapse:collapse; }
.detail-table td { padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,.07); font-size:.9rem; }
.detail-table td:first-child { color: rgba(255,255,255,.5); width:45%; }
.total-row td { border-top: 2px solid rgba(118,255,3,.3); border-bottom: none; padding-top: 16px; }
</style>
</body>
</html>
