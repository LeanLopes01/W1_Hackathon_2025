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
  <title>W1 Consultoria — Simulador Premium</title>
  <link rel="stylesheet" href="styles-simulator.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <header class="topbar">
    <div class="logo">W1 Consultoria Patrimonial</div>
    <nav>
      <a href="InvestmentPortfolioDashboard.php" class="btn-back">Voltar a tela inicial</a>
      <a href="dashboard.html" class="btn-back">Voltar ao Dashboard</a>
    </nav>
  </header>

  <main class="container">
    <section class="simulator-panel">
      <h2>Simulação de Investimentos</h2>

      <div class="inputs">
        <div class="input-group">
          <label for="initial">Valor Inicial (R$)</label>
          <input type="number" id="initial" value="10000">
        </div>
        <div class="input-group">
          <label for="monthly">Aporte Mensal (R$)</label>
          <input type="number" id="monthly" value="500">
        </div>
        <div class="input-group">
          <label for="rate">Rentabilidade Anual (%)</label>
          <input type="number" id="rate" value="8">
        </div>
        <div class="input-group">
          <label for="years">Período (anos)</label>
          <input type="number" id="years" value="10">
        </div>
      </div>

      <button class="btn-simular" onclick="simulate()">Calcular</button>

      <div class="results" id="results" style="display:none;">
        <h3>Resultados</h3>
        <div class="kpis">
          <div class="kpi-card">
            <small>Valor Futuro</small>
            <strong id="finalValue">R$ 0,00</strong>
          </div>
          <div class="kpi-card">
            <small>Total de Aportes</small>
            <strong id="totalContributions">R$ 0,00</strong>
          </div>
          <div class="kpi-card">
            <small>Rendimento Obtido</small>
            <strong id="totalEarnings">R$ 0,00</strong>
          </div>
        </div>

        <div class="charts">
          <div class="chart-box">
            <h4>Crescimento do Saldo</h4>
            <canvas id="growthChart"></canvas>
          </div>
          <div class="chart-box">
            <h4>Contribuições vs Rendimento</h4>
            <canvas id="breakdownChart"></canvas>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Nosso script vem por último -->
  <script src="script-simulator.js"></script>
</body>
</html>
