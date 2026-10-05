<?php
require_once 'config/session.php';
require_once 'config/db.php';
verificarSesion();

$conn = conectar();

// Estadísticas del día
$hoy = date('Y-m-d');
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_hoy,
        SUM(CASE WHEN estado='activo' THEN 1 ELSE 0 END) as en_parqueadero,
        COALESCE(SUM(CASE WHEN DATE(hora_salida)='$hoy' THEN valor_pagado END), 0) as ingresos_hoy
    FROM tickets
    WHERE DATE(hora_entrada) = '$hoy'
")->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SGP – Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --verde: #4CAF50;
    --verde-oscuro: #2E7D32;
    --azul: #1565C0;
    --gris-oscuro: #1a1a2e;
    --gris-medio: #2d2d44;
    --gris-claro: #3a3a55;
    --blanco: #f0f4f8;
    --acento: #76FF03;
    --sidebar-w: 240px;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: 'DM Sans', sans-serif; background: var(--gris-oscuro); color: var(--blanco); display: flex; min-height: 100vh; }

  /* SIDEBAR */
  .sidebar {
    width: var(--sidebar-w);
    background: var(--gris-medio);
    border-right: 1px solid rgba(255,255,255,.07);
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    z-index: 100;
  }
  .sidebar-logo {
    padding: 24px 20px;
    border-bottom: 1px solid rgba(255,255,255,.07);
  }
  .sidebar-logo h2 { font-family: 'Bebas Neue', sans-serif; font-size: 1.6rem; color: var(--acento); letter-spacing: 3px; }
  .sidebar-logo p { font-size: .7rem; color: rgba(255,255,255,.3); letter-spacing: 1px; text-transform: uppercase; }
  .nav { flex: 1; padding: 16px 0; }
  .nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: rgba(255,255,255,.55);
    text-decoration: none;
    font-size: .875rem;
    font-weight: 500;
    transition: all .15s;
    border-left: 3px solid transparent;
  }
  .nav a:hover, .nav a.active {
    color: var(--blanco);
    background: rgba(118,255,3,.07);
    border-left-color: var(--acento);
  }
  .nav a .icon { font-size: 1.1rem; }
  .nav-section { padding: 12px 20px 4px; font-size: .65rem; color: rgba(255,255,255,.25); text-transform: uppercase; letter-spacing: 2px; }
  .sidebar-user {
    padding: 16px 20px;
    border-top: 1px solid rgba(255,255,255,.07);
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .avatar {
    width: 34px; height: 34px;
    background: var(--verde);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .9rem;
  }
  .user-info p { font-size: .8rem; font-weight: 600; }
  .user-info span { font-size: .7rem; color: rgba(255,255,255,.4); text-transform: capitalize; }
  .btn-logout {
    margin-left: auto;
    background: none;
    border: none;
    color: rgba(255,255,255,.3);
    cursor: pointer;
    font-size: 1rem;
    transition: color .15s;
  }
  .btn-logout:hover { color: #ef5350; }

  /* MAIN */
  .main { margin-left: var(--sidebar-w); flex: 1; padding: 32px; }
  .page-header { margin-bottom: 28px; }
  .page-header h1 { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; letter-spacing: 3px; color: var(--blanco); }
  .page-header p { color: rgba(255,255,255,.4); font-size: .85rem; margin-top: 2px; }

  /* STAT CARDS */
  .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
  .stat-card {
    background: var(--gris-medio);
    border-radius: 12px;
    padding: 24px;
    border: 1px solid rgba(255,255,255,.07);
    position: relative;
    overflow: hidden;
  }
  .stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 4px; height: 100%;
    background: var(--acento);
  }
  .stat-card.blue::before { background: #42A5F5; }
  .stat-card.orange::before { background: #FFA726; }
  .stat-label { font-size: .75rem; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
  .stat-value { font-family: 'Bebas Neue', sans-serif; font-size: 2.4rem; color: var(--blanco); letter-spacing: 2px; }
  .stat-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 2.5rem; opacity: .15; }

  /* ACTION CARDS */
  .actions-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
  .action-card {
    background: var(--gris-medio);
    border-radius: 12px;
    padding: 28px;
    border: 1px solid rgba(255,255,255,.07);
    text-decoration: none;
    color: var(--blanco);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all .2s;
  }
  .action-card:hover {
    transform: translateY(-2px);
    border-color: var(--acento);
    box-shadow: 0 8px 24px rgba(118,255,3,.1);
  }
  .action-icon {
    width: 56px; height: 56px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    flex-shrink: 0;
  }
  .action-icon.green { background: rgba(76,175,80,.2); }
  .action-icon.red { background: rgba(239,83,80,.2); }
  .action-icon.blue { background: rgba(66,165,245,.2); }
  .action-icon.yellow { background: rgba(255,167,38,.2); }
  .action-card h3 { font-size: 1rem; font-weight: 600; margin-bottom: 4px; }
  .action-card p { font-size: .8rem; color: rgba(255,255,255,.4); }
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>🅿️ SGP</h2>
    <p>Gestión Parqueadero</p>
  </div>
  <nav class="nav">
    <div class="nav-section">Principal</div>
    <a href="dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a>
    <a href="entrada.php"><span class="icon">🚗</span> Registrar Entrada</a>
    <a href="salida.php"><span class="icon">🚪</span> Registrar Salida</a>
    <a href="vehiculos.php"><span class="icon">📋</span> Vehículos en Patio</a>
    <?php if ($_SESSION['rol'] === 'administrador'): ?>
    <div class="nav-section">Administración</div>
    <a href="reportes.php"><span class="icon">📊</span> Reportes</a>
    <a href="tarifas.php"><span class="icon">💰</span> Tarifas</a>
    <a href="usuarios.php"><span class="icon">👥</span> Usuarios</a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-user">
    <div class="avatar"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
    <div class="user-info">
      <p><?= htmlspecialchars($_SESSION['nombre']) ?></p>
      <span><?= $_SESSION['rol'] ?></span>
    </div>
    <a href="logout.php" class="btn-logout" title="Cerrar sesión">⏻</a>
  </div>
</aside>

<main class="main">
  <div class="page-header">
    <h1>Dashboard</h1>
    <p><?= date('l, d \d\e F \d\e Y') ?> &mdash; Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Vehículos hoy</div>
      <div class="stat-value"><?= $stats['total_hoy'] ?></div>
      <div class="stat-icon">🚗</div>
    </div>
    <div class="stat-card blue">
      <div class="stat-label">En parqueadero</div>
      <div class="stat-value"><?= $stats['en_parqueadero'] ?></div>
      <div class="stat-icon">🅿️</div>
    </div>
    <div class="stat-card orange">
      <div class="stat-label">Ingresos hoy</div>
      <div class="stat-value">$<?= number_format($stats['ingresos_hoy'], 0, ',', '.') ?></div>
      <div class="stat-icon">💵</div>
    </div>
  </div>

  <div class="actions-grid">
    <a href="entrada.php" class="action-card">
      <div class="action-icon green">🚗</div>
      <div>
        <h3>Registrar Entrada</h3>
        <p>Registrar ingreso de un nuevo vehículo al parqueadero</p>
      </div>
    </a>
    <a href="salida.php" class="action-card">
      <div class="action-icon red">🚪</div>
      <div>
        <h3>Registrar Salida</h3>
        <p>Procesar salida y calcular valor a pagar</p>
      </div>
    </a>
    <a href="vehiculos.php" class="action-card">
      <div class="action-icon blue">📋</div>
      <div>
        <h3>Vehículos en Patio</h3>
        <p>Ver todos los vehículos actualmente en el parqueadero</p>
      </div>
    </a>
    <?php if ($_SESSION['rol'] === 'administrador'): ?>
    <a href="reportes.php" class="action-card">
      <div class="action-icon yellow">📊</div>
      <div>
        <h3>Reportes</h3>
        <p>Ver ingresos diarios, semanales y mensuales</p>
      </div>
    </a>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
