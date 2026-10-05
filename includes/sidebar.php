<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>🅿️ SGP</h2>
    <p>Gestión Parqueadero</p>
  </div>
  <nav class="nav">
    <div class="nav-section">Principal</div>
    <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : '' ?>>
      <span class="icon">🏠</span> Dashboard
    </a>
    <a href="entrada.php" <?= basename($_SERVER['PHP_SELF']) === 'entrada.php' ? 'class="active"' : '' ?>>
      <span class="icon">🚗</span> Registrar Entrada
    </a>
    <a href="salida.php" <?= basename($_SERVER['PHP_SELF']) === 'salida.php' ? 'class="active"' : '' ?>>
      <span class="icon">🚪</span> Registrar Salida
    </a>
    <a href="vehiculos.php" <?= basename($_SERVER['PHP_SELF']) === 'vehiculos.php' ? 'class="active"' : '' ?>>
      <span class="icon">📋</span> Vehículos en Patio
    </a>
    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
    <div class="nav-section">Administración</div>
    <a href="reportes.php" <?= basename($_SERVER['PHP_SELF']) === 'reportes.php' ? 'class="active"' : '' ?>>
      <span class="icon">📊</span> Reportes
    </a>
    <a href="tarifas.php" <?= basename($_SERVER['PHP_SELF']) === 'tarifas.php' ? 'class="active"' : '' ?>>
      <span class="icon">💰</span> Tarifas
    </a>
    <a href="usuarios.php" <?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'class="active"' : '' ?>>
      <span class="icon">👥</span> Usuarios
    </a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-user">
    <div class="avatar"><?= strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 1)) ?></div>
    <div class="user-info">
      <p><?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></p>
      <span><?= $_SESSION['rol'] ?? '' ?></span>
    </div>
    <a href="logout.php" class="btn-logout" title="Cerrar sesión">⏻</a>
  </div>
</aside>
