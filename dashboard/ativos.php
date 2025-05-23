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

$stmt = $pdo->prepare("
    SELECT id 
    FROM patrimony_snapshots 
    WHERE user_id = ? 
    ORDER BY timestamp DESC 
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$snapshot = $stmt->fetch(PDO::FETCH_ASSOC);

if ($snapshot && isset($snapshot['id'])) {
    $stmt = $pdo->prepare("
        SELECT ativo, valor_investido, rendimento_percentual, valor_atual 
        FROM patrimony_allocations 
        WHERE snapshot_id = ?
    ");
    $stmt->execute([$snapshot['id']]);
    $ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $ativos = []; // Nenhum snapshot encontrado
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>W1 Consultoria — Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="topbar">
        <div class="logo">Consulta dos Ativos</div>
        <nav>
        <a href="dashboard.php" class="btn-nav">Visão Geral</a>
        <button class="btn-nav active" data-section="overview">Ativos</button>
        <a href="InvestmentPortfolioDashboard.php" class="btn-nav">Voltar ao menu</a>
        </nav>
  </header>
    <main class="container">
        <section id="overview" class="section active">
            <div class="cards">
                <?php if (!empty($ativos)): ?>
                    <?php foreach ($ativos as $ativo): ?>
                        <div class="card">
                            <h3><?= htmlspecialchars($ativo['ativo']) ?></h3>
                            <p>R$ <?= number_format($ativo['valor_investido'], 2, ',', '.') ?></p>
                            <p><?= number_format($ativo['rendimento_percentual'], 2, ',', '.') ?>%</p>
                            <p>R$ <?= number_format($ativo['valor_atual'], 2, ',', '.') ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card">
                        <p>Nenhum ativo registrado</p>
                    </div>
                <?php endif; ?>
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
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sua Empresa. Todos os direitos reservados.</p>
    </footer>
</body>
</html>