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
    overflow-y: auto;
  }
  .sidebar-logo { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,.07); }
  .sidebar-logo h2 { font-family: 'Bebas Neue', sans-serif; font-size: 1.6rem; color: var(--acento); letter-spacing: 3px; }
  .sidebar-logo p { font-size: .7rem; color: rgba(255,255,255,.3); letter-spacing: 1px; text-transform: uppercase; }
  .nav { flex: 1; padding: 16px 0; }
  .nav a {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 20px; color: rgba(255,255,255,.55);
    text-decoration: none; font-size: .875rem; font-weight: 500;
    transition: all .15s; border-left: 3px solid transparent;
  }
  .nav a:hover, .nav a.active {
    color: var(--blanco); background: rgba(118,255,3,.07); border-left-color: var(--acento);
  }
  .nav a .icon { font-size: 1.1rem; }
  .nav-section { padding: 12px 20px 4px; font-size: .65rem; color: rgba(255,255,255,.25); text-transform: uppercase; letter-spacing: 2px; }
  .sidebar-user {
    padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.07);
    display: flex; align-items: center; gap: 10px;
  }
  .avatar {
    width: 34px; height: 34px; background: var(--verde); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .9rem; flex-shrink: 0;
  }
  .user-info p { font-size: .8rem; font-weight: 600; }
  .user-info span { font-size: .7rem; color: rgba(255,255,255,.4); text-transform: capitalize; }
  .btn-logout {
    margin-left: auto; background: none; border: none;
    color: rgba(255,255,255,.3); cursor: pointer; font-size: 1rem; transition: color .15s;
    text-decoration: none;
  }
  .btn-logout:hover { color: #ef5350; }

  /* MAIN */
  .main { margin-left: var(--sidebar-w); flex: 1; padding: 32px; }
  .page-header { margin-bottom: 28px; }
  .page-header h1 { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; letter-spacing: 3px; color: var(--blanco); }
  .page-header p { color: rgba(255,255,255,.4); font-size: .85rem; margin-top: 2px; }

  /* CARD */
  .card {
    background: var(--gris-medio); border-radius: 12px; padding: 24px;
    border: 1px solid rgba(255,255,255,.07); margin-bottom: 20px;
  }
  .card h3 { font-family: 'Bebas Neue', sans-serif; letter-spacing: 2px; font-size: 1.2rem; margin-bottom: 16px; }

  /* TABLA */
  .tabla { width: 100%; border-collapse: collapse; font-size: .875rem; }
  .tabla thead th {
    text-align: left; padding: 10px 12px; font-size: .7rem;
    color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: 1.5px;
    border-bottom: 1px solid rgba(255,255,255,.07);
  }
  .tabla tbody td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,.05); }
  .tabla tbody tr:hover { background: rgba(255,255,255,.03); }
  .tabla tbody tr:last-child td { border-bottom: none; }

  /* BADGE */
  .badge {
    background: rgba(118,255,3,.15); color: var(--acento);
    font-size: .72rem; font-weight: 700; padding: 3px 8px;
    border-radius: 4px; letter-spacing: 1px;
  }

  /* BOTONES */
  .btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; border-radius: 8px; border: none;
    font-family: 'DM Sans', sans-serif; font-size: .875rem;
    font-weight: 600; cursor: pointer; text-decoration: none; transition: all .2s;
  }
  .btn-primary { background: var(--verde); color: #fff; }
  .btn-primary:hover { background: var(--verde-oscuro); }
  .btn-danger { background: #ef5350; color: #fff; }
  .btn-danger:hover { background: #c62828; }
  .btn-secondary { background: rgba(255,255,255,.1); color: var(--blanco); }
  .btn-secondary:hover { background: rgba(255,255,255,.15); }
  .btn-sm { padding: 6px 12px; font-size: .78rem; }

  /* FORMULARIOS */
  .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 20px; }
  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-group label { font-size: .75rem; font-weight: 600; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; }
  .form-group input, .form-group select, .form-group textarea {
    background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px; padding: 10px 14px; color: var(--blanco);
    font-family: 'DM Sans', sans-serif; font-size: .9rem; transition: border-color .2s;
  }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none; border-color: var(--acento);
  }
  .form-group select option { background: var(--gris-medio); }

  /* ALERTAS */
  .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: .9rem; }
  .alert-success { background: rgba(76,175,80,.15); border: 1px solid rgba(76,175,80,.4); color: #a5d6a7; }
  .alert-error { background: rgba(244,67,54,.15); border: 1px solid rgba(244,67,54,.4); color: #ef9a9a; }
  .alert-info { background: rgba(66,165,245,.15); border: 1px solid rgba(66,165,245,.4); color: #90caf9; }

  /* STATS GRID */
  .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
  .stat-card {
    background: var(--gris-medio); border-radius: 12px; padding: 24px;
    border: 1px solid rgba(255,255,255,.07); position: relative; overflow: hidden;
  }
  .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--acento); }
  .stat-card.blue::before { background: #42A5F5; }
  .stat-card.orange::before { background: #FFA726; }
  .stat-card.purple::before { background: #AB47BC; }
  .stat-label { font-size: .75rem; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
  .stat-value { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; color: var(--blanco); letter-spacing: 2px; }
  .stat-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 2.5rem; opacity: .15; }
</style>
