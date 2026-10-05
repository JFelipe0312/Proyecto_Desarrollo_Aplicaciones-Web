<?php
require_once 'config/session.php';
require_once 'config/db.php';
verificarAdmin();

$conn = conectar();
$periodo = $_GET['periodo'] ?? 'dia';

// Definir rango de fechas según período
switch ($periodo) {
    case 'semana':
        $desde = date('Y-m-d', strtotime('monday this week'));
        $hasta = date('Y-m-d', strtotime('sunday this week'));
        $label = 'Esta Semana';
        break;
    case 'mes':
        $desde = date('Y-m-01');
        $hasta = date('Y-m-t');
        $label = 'Este Mes';
        break;
    default: // dia
        $desde = date('Y-m-d');
        $hasta = date('Y-m-d');
        $label = 'Hoy';
}

// Estadísticas generales del período
$stats = $conn->query("
    SELECT
        COUNT(*) as total_tickets,
        SUM(CASE WHEN estado='cerrado' THEN 1 ELSE 0 END) as cerrados,
        COALESCE(SUM(valor_pagado), 0) as ingresos_total,
        COALESCE(AVG(valor_pagado), 0) as ticket_promedio,
        COALESCE(AVG(minutos_permanencia), 0) as tiempo_promedio
    FROM tickets
    WHERE DATE(hora_entrada) BETWEEN '$desde' AND '$hasta'
")->fetch_assoc();

// Ingresos por tipo de vehículo
$por_tipo = $conn->query("
    SELECT tipo_vehiculo,
           COUNT(*) as cantidad,
           COALESCE(SUM(valor_pagado), 0) as ingresos
    FROM tickets
    WHERE DATE(hora_entrada) BETWEEN '$desde' AND '$hasta'
      AND estado = 'cerrado'
    GROUP BY tipo_vehiculo
    ORDER BY ingresos DESC
");
$tipos = $por_tipo->fetch_all(MYSQLI_ASSOC);

// Detalle de tickets del período
$detalle = $conn->query("
    SELECT t.id_ticket, t.placa, t.tipo_vehiculo,
           t.hora_entrada, t.hora_salida,
           t.minutos_permanencia, t.valor_pagado, t.estado,
           u.nombre as operario
    FROM tickets t
    LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario_entrada
    WHERE DATE(t.hora_entrada) BETWEEN '$desde' AND '$hasta'
    ORDER BY t.hora_entrada DESC
    LIMIT 100
");
$tickets = $detalle->fetch_all(MYSQLI_ASSOC);

$conn->close();

function formatMin($m) {
    if (!$m) return '—';
    if ($m < 60) return $m . ' min';
    return floor($m/60) . 'h ' . ($m%60) . 'm';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Reportes</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<?php include 'includes/head_styles.php'; ?>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Reportes</h1>
    <p>Estadísticas e ingresos del parqueadero</p>
  </div>

  <!-- Selector de período -->
  <div style="display:flex; gap:10px; margin-bottom:24px; flex-wrap:wrap;">
    <a href="?periodo=dia"    class="btn <?= $periodo==='dia'    ? 'btn-primary' : 'btn-secondary' ?>">📅 Hoy</a>
    <a href="?periodo=semana" class="btn <?= $periodo==='semana' ? 'btn-primary' : 'btn-secondary' ?>">📅 Esta Semana</a>
    <a href="?periodo=mes"    class="btn <?= $periodo==='mes'    ? 'btn-primary' : 'btn-secondary' ?>">📅 Este Mes</a>
  </div>

  <!-- Estadísticas -->
  <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); margin-bottom:24px;">
    <div class="stat-card">
      <div class="stat-label">Vehículos <?= $label ?></div>
      <div class="stat-value"><?= $stats['total_tickets'] ?></div>
      <div class="stat-icon">🚗</div>
    </div>
    <div class="stat-card orange">
      <div class="stat-label">Ingresos <?= $label ?></div>
      <div class="stat-value">$<?= number_format($stats['ingresos_total'], 0, ',', '.') ?></div>
      <div class="stat-icon">💵</div>
    </div>
    <div class="stat-card blue">
      <div class="stat-label">Ticket Promedio</div>
      <div class="stat-value">$<?= number_format($stats['ticket_promedio'], 0, ',', '.') ?></div>
      <div class="stat-icon">🎫</div>
    </div>
    <div class="stat-card purple">
      <div class="stat-label">Tiempo Promedio</div>
      <div class="stat-value"><?= formatMin((int)$stats['tiempo_promedio']) ?></div>
      <div class="stat-icon">⏱️</div>
    </div>
  </div>

  <!-- Ingresos por tipo -->
  <?php if (!empty($tipos)): ?>
  <div class="card" style="margin-bottom:24px;">
    <h3>Ingresos por Tipo de Vehículo — <?= $label ?></h3>
    <table class="tabla">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Cantidad</th>
          <th>Ingresos</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tipos as $t): ?>
        <tr>
          <td><?= ucfirst($t['tipo_vehiculo']) ?></td>
          <td><?= $t['cantidad'] ?></td>
          <td><strong style="color:var(--acento);">$<?= number_format($t['ingresos'], 0, ',', '.') ?></strong></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Detalle de tickets -->
  <div class="card">
    <h3>Detalle de Tickets — <?= $label ?></h3>
    <?php if (empty($tickets)): ?>
      <p style="color:rgba(255,255,255,.3); text-align:center; padding:32px;">No hay registros en este período.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="tabla">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Placa</th>
            <th>Tipo</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Tiempo</th>
            <th>Valor</th>
            <th>Estado</th>
            <th>Operario</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tickets as $t): ?>
          <tr>
            <td><span class="badge">#<?= str_pad($t['id_ticket'], 6, '0', STR_PAD_LEFT) ?></span></td>
            <td><strong><?= htmlspecialchars($t['placa']) ?></strong></td>
            <td><?= ucfirst($t['tipo_vehiculo']) ?></td>
            <td><?= $t['hora_entrada'] ? date('d/m H:i', strtotime($t['hora_entrada'])) : '—' ?></td>
            <td><?= $t['hora_salida'] ? date('d/m H:i', strtotime($t['hora_salida'])) : '—' ?></td>
            <td><?= formatMin($t['minutos_permanencia']) ?></td>
            <td><?= $t['valor_pagado'] ? '$'.number_format($t['valor_pagado'],0,',','.') : '—' ?></td>
            <td>
              <span style="color:<?= $t['estado']==='activo' ? '#76FF03' : 'rgba(255,255,255,.4)' ?>; font-size:.8rem; font-weight:600;">
                <?= ucfirst($t['estado']) ?>
              </span>
            </td>
            <td style="color:rgba(255,255,255,.5);"><?= htmlspecialchars($t['operario'] ?? '—') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
