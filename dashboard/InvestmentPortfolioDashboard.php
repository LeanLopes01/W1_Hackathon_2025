<?php
require_once __DIR__ . '/../php/config.php';

session_start();


// Verificar autenticação
if (!isset($_SESSION['logged_in'])) {
    header('Location: ../index.html');
    exit();
}

// Verificar User-Agent
if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header('Location: ../index.html');
    exit();
}

// Verificar timeout (30 minutos)
$inactive = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
    header('Location: ../index.html?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time(); // Atualiza tempo de atividade

// Verificação adicional de IP (opcional)
$ip_check = true; // Altere para false para desativar
if ($ip_check && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header('Location: ../index.html');
    exit();
}

// Headers de segurança contra cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>W1 Consultoria Patrimonial — Minha Jornada</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="brand">
      <h2>W1</h2>
      <span>Consultoria Patrimonial</span>
    </div>
    <div class="user-info">
      <!-- Exibe “Olá, [Nome]!” -->
      <p>Olá, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?>!</p>
    </div>
    <nav class="nav">
      <a href="#" class="nav-item active"><i class="fa-solid fa-route"></i> Minha jornada</a>
      <a href="simulacao.php" class="nav-item"><i class="fa-solid fa-calculator"></i> Fazer Simulação</a>
      <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
      <a href="consultor.php" class="nav-item"><i class="fa-solid fa-comments"></i> Fale com seu consultor</a>
      <a href="patrimonio.php" class="nav-item"><i class="fa-solid fa-comments"></i>Calcule seu patrimônio</a>
    </nav>
  </aside>

  <!-- CONTEÚDO -->
  <main class="content">
    <h1>Sua jornada de construção da Holding</h1>

    <!-- Barra de progresso -->
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill" id="progressFill"></div>
      </div>
      <span class="progress-text" id="progressText"></span>
      <span class="stage-text" id="stageText"></span>
    </div>

    <!-- Timeline de etapas -->
    <ul class="timeline">
      <li class="step completed" data-step="1">
        <div class="icon"><i class="fa-solid fa-folder-open"></i></div>
        <div class="details">
          <h3>Organização de documentos</h3>
          <p><small>Concluído</small></p>
        </div>
        <button class="btn-detail">Ver detalhes</button>
      </li>
      <li class="step in-progress" data-step="2">
        <div class="icon"><i class="fa-solid fa-gavel"></i></div>
        <div class="details">
          <h3>Estruturação Jurídica</h3>
          <p>
            <input type="checkbox" checked disabled> Desenho da estrutura societária<br>
            <input type="checkbox" checked disabled> Aprovação do plano patrimonial
          </p>
        </div>
        <button class="btn-detail">Ver detalhes</button>
      </li>
      <li class="step upcoming" data-step="3">
        <div class="icon"><i class="fa-solid fa-pen-to-square"></i></div>
        <div class="details">
          <h3>Execução e registro</h3>
          <p><input type="checkbox" disabled> Formalização organizacional</p>
        </div>
        <button class="btn-detail">Ver detalhes</button>
      </li>
    </ul>
  </main>

  <!-- Modal Overlay -->
  <div id="modalOverlay" class="modal-overlay" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
      <button class="modal-close" aria-label="Fechar">&times;</button>
      <div id="modalBody"></div>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
