<?php
require_once 'config/session.php';
require_once 'config/db.php';
verificarSesion();

$mensaje = '';
$tipo_mensaje = '';
$ticket_generado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa   = strtoupper(trim($_POST['placa'] ?? ''));
    $tipo    = $_POST['tipo'] ?? '';
    $marca   = trim($_POST['marca'] ?? '');
    $color   = trim($_POST['color'] ?? '');

    if ($placa && $tipo) {
        $conn = conectar();

        // Verificar que no haya un ticket activo para esa placa
        $check = $conn->prepare("SELECT id_ticket FROM tickets WHERE placa = ? AND estado = 'activo'");
        $check->bind_param('s', $placa);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $mensaje = "El vehículo con placa <strong>$placa</strong> ya está registrado en el parqueadero.";
            $tipo_mensaje = 'error';
        } else {
            // Insertar o actualizar vehículo
            $stmt = $conn->prepare("INSERT INTO vehiculos (placa, tipo, marca, color) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE tipo=VALUES(tipo), marca=VALUES(marca), color=VALUES(color)");
            $stmt->bind_param('ssss', $placa, $tipo, $marca, $color);
            $stmt->execute();

            // Crear ticket
            $hora = date('Y-m-d H:i:s');
            $id_usuario = $_SESSION['id_usuario'];
            $stmt2 = $conn->prepare("INSERT INTO tickets (placa, tipo_vehiculo, hora_entrada, estado, id_usuario_entrada) VALUES (?,?,?,'activo',?)");
            $stmt2->bind_param('sssi', $placa, $tipo, $hora, $id_usuario);
            $stmt2->execute();
            $id_ticket = $conn->insert_id;

            $ticket_generado = [
                'id_ticket'    => $id_ticket,
                'placa'        => $placa,
                'tipo'         => $tipo,
                'hora_entrada' => $hora,
            ];
            $mensaje = "Vehículo registrado exitosamente.";
            $tipo_mensaje = 'success';
        }
        $conn->close();
    } else {
        $mensaje = 'Por favor completa los campos requeridos.';
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Registrar Entrada</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<?php include 'includes/head_styles.php'; ?>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Registrar Entrada</h1>
    <p>Ingresa los datos del vehículo que entra al parqueadero</p>
  </div>

  <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?>"><?= $mensaje ?></div>
  <?php endif; ?>

  <?php if ($ticket_generado): ?>
  <div class="ticket-box">
    <div class="ticket-header">🎫 TICKET DE INGRESO</div>
    <div class="ticket-body">
      <div class="ticket-row"><span>N° Ticket:</span><strong>#<?= str_pad($ticket_generado['id_ticket'], 6, '0', STR_PAD_LEFT) ?></strong></div>
      <div class="ticket-row"><span>Placa:</span><strong><?= htmlspecialchars($ticket_generado['placa']) ?></strong></div>
      <div class="ticket-row"><span>Tipo:</span><strong><?= ucfirst($ticket_generado['tipo']) ?></strong></div>
      <div class="ticket-row"><span>Entrada:</span><strong><?= date('d/m/Y H:i', strtotime($ticket_generado['hora_entrada'])) ?></strong></div>
    </div>
    <div class="ticket-footer">Conserve este ticket para el cobro</div>
  </div>
  <?php endif; ?>

  <div class="card">
    <form method="POST">
      <div class="form-grid">
        <div class="form-group">
          <label>Placa *</label>
          <input type="text" name="placa" placeholder="Ej: ABC123" maxlength="10" required style="text-transform:uppercase">
        </div>
        <div class="form-group">
          <label>Tipo de Vehículo *</label>
          <select name="tipo" required>
            <option value="">Seleccionar...</option>
            <option value="carro">🚗 Carro</option>
            <option value="moto">🏍️ Moto</option>
            <option value="bicicleta">🚲 Bicicleta</option>
          </select>
        </div>
        <div class="form-group">
          <label>Marca</label>
          <input type="text" name="marca" placeholder="Ej: Chevrolet">
        </div>
        <div class="form-group">
          <label>Color</label>
          <input type="text" name="color" placeholder="Ej: Rojo">
        </div>
      </div>
      <button type="submit" class="btn btn-primary">🚗 Registrar Entrada</button>
    </form>
  </div>
</main>

<style>
.ticket-box {
  background: #fff;
  color: #1a1a2e;
  border-radius: 12px;
  overflow: hidden;
  max-width: 360px;
  margin-bottom: 24px;
  box-shadow: 0 8px 24px rgba(0,0,0,.3);
  border: 2px dashed #4CAF50;
}
.ticket-header {
  background: #2E7D32;
  color: #fff;
  font-family: 'Bebas Neue', sans-serif;
  font-size: 1.1rem;
  letter-spacing: 3px;
  padding: 12px 20px;
  text-align: center;
}
.ticket-body { padding: 16px 20px; }
.ticket-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px dashed #e0e0e0;
  font-size: .9rem;
}
.ticket-row:last-child { border-bottom: none; }
.ticket-row span { color: #666; }
.ticket-footer {
  background: #f5f5f5;
  text-align: center;
  padding: 10px;
  font-size: .75rem;
  color: #999;
}
</style>
</body>
</html>
