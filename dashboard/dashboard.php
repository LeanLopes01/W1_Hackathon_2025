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

function getPatrimonioTotal($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT 
        SUM(qtd_aposentadoria + qtd_viagens + qtd_casamento + 
            qtd_filhos + qtd_bolsa + qtd_fundInves + 
            qtd_bdrs + qtd_crypto) AS total
        FROM dados_patri 
        WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

$patrimonioTotal = number_format(getPatrimonioTotal($_SESSION['user_id'], $pdo), 2, ',', '.');

function getPatrimonioSnap($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT 
        patrimonio_liquido, 
        total_rendimentos, 
        rentabilidade_total_percentual,
        max_alta_ativo,
        max_alta_valor,
        max_baixa_ativo,
        max_baixa_valor
    FROM patrimony_snapshots
    WHERE user_id = ?
    LIMIT 1"); // Removeu ORDER BY data_snapshot

    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$dadosPatrimonio = getPatrimonioSnap($_SESSION['user_id'], $pdo);
$rentabilidadeTotal = $dadosPatrimonio ? number_format($dadosPatrimonio['rentabilidade_total_percentual'], 2, ',', '.') : '0,00';
$patrimonioLiquido = $dadosPatrimonio ? number_format($dadosPatrimonio['patrimonio_liquido'], 2, ',', '.') : '0,00';
$maxAltaValor = $dadosPatrimonio ? number_format($dadosPatrimonio['max_alta_valor'], 2, ',', '.') : '0,00';
$maxBaixaValor = $dadosPatrimonio ? number_format($dadosPatrimonio['max_baixa_valor'], 2, ',', '.') : '0,00';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>W1 Consultoria — Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
  <header class="topbar">
    <div class="logo">W1 Consultoria Patrimonial</div>
    <nav>
      <button class="btn-nav active" data-section="overview">Visão Geral</button>
      <a href="ativos.php" class="btn-nav">Ativos</a>
      <a href="InvestmentPortfolioDashboard.php" class="btn-nav">Voltar ao menu</a>
    </nav>
  </header>

  <main class="container">

    <!-- VISÃO GERAL -->
    <section id="overview" class="section active">
      <div class="cards">
        <div class="card">
          <h3>Total Investido</h3>
          <p id="patrimonioTotal">R$ <?= $patrimonioTotal ?></p>
        </div>
        <div class="card">
          <h3>Rentabilidade</h3>
          <p id="rentabilidadeTotal"><?php echo htmlspecialchars($rentabilidadeTotal); ?>%</p>
        </div>
        <div class="card">
          <h3>Patrimônio</h3>
          <p id="patrimonioLiquido">R$<?php echo htmlspecialchars($patrimonioLiquido); ?></p>
        </div>
      </div>

      <div class="chart-wrapper">
        <h4>Distribuição por Classe de Ativos</h4>
        <!-- Canvas deve vir ANTES dos scripts -->
        <canvas id="overviewDonut" width="400" height="200"></canvas>
      </div>

      <div class="info-text">
        <p>Nesta visão geral você acompanha de forma rápida:</p>
        <ul>
          <li><strong>Total Investido:</strong> soma de todos os seus ativos</li>
          <li><strong>Rentabilidade:</strong> variação percentual do último período</li>
          <li><strong>Patrimônio:</strong> valor atual consolidado</li>
          <li><strong>Distribuição de Ativos:</strong> como seu capital está alocado</li>
        </ul>
      </div>
    </section>

        <!-- PERFORMANCE -->
    <section id="performance" class="section">
        <!-- Controles de Filtro -->
        <div class="perf-controls">
            <label>Período:
            <select id="perfRange">
                <option value="1">1 mês</option>
                <option value="6">6 meses</option>
                <option value="12" selected>12 meses</option>
            </select>
            </label>
            <label>Tipo de Gráfico:
            <select id="perfType">
                <option value="line">Linha</option>
                <option value="bar">Barras</option>
            </select>
            </label>
        </div>

        <!-- KPI Cards -->
        <div class="perf-kpis">
            <div class="kpi">
            <small>Maior Alta</small>
            <p id="maxAlta"><?php echo htmlspecialchars($maxAltaValor); ?>%</p>
            </div>
            <div class="kpi">
            <small>Maior Baixa</small>
            <p id="maxBaixa"><?php echo htmlspecialchars($maxBaixaValor); ?>%</p>
            </div>
        </div>

        <!-- Gráfico Dinâmico -->
        <div class="chart-wrapper">
            <h4 id="perfTitle">Evolução Mensal (12 meses)</h4>
            <canvas id="perfChart" width="400" height="200"></canvas>
        </div>

        <!-- Tabela de Retornos -->
        <div class="table-wrapper">
            <h4>Retornos Mensais (%)</h4>
            <table class="perf-table">
            <thead>
                <tr><th>Mês</th><th>Retorno</th></tr>
            </thead>
            <tbody id="perfTableBody">
                <!-- preenche via JS -->
            </tbody>
            </table>
        </div>
    </section>
  </main>

  <!-- nosso script só roda após todo o HTML existir -->
  <script src="script-dashboard.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
