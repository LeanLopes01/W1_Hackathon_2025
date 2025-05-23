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
  <title>W1 Consultoria — Fale com seu Consultor</title>
  <link rel="stylesheet" href="consultor.css">
</head>
<body>
  <header class="topbar">
    <div class="logo">W1 Consultoria Patrimonial</div>
    <nav>
        <a href="InvestmentPortfolioDashboard.php" class="btn-nav">Voltar ao menu</a>
      <a href="consultor.html" class="btn-nav active">Fale com Consultor</a>
    </nav>
  </header>

  <main class="container">
    <section class="intro">
      <h1>Fale com seu Consultor</h1>
      <p>
        Precisa de ajuda personalizada? Nosso time de consultores está à disposição para
        responder suas dúvidas, orientar sobre abertura de holding e otimização patrimonial.
      </p>
    </section>

    <section class="contact-panel">
      <h2>Entre em Contato</h2>
      <form id="contactForm" novalidate>
        <div class="form-group">
          <label for="name">Nome Completo</label>
          <input type="text" id="name" name="name" required aria-required="true" />
        </div>
        <div class="form-group">
          <label for="email">E-mail</label>
          <input type="email" id="email" name="email" required aria-required="true" />
        </div>
        <div class="form-group">
          <label for="phone">Telefone (com DDD)</label>
          <input type="tel" id="phone" name="phone" placeholder="(11) 91234-5678" required aria-required="true" />
        </div>
        <div class="form-group">
          <label for="method">Preferência de Contato</label>
          <select id="method" name="method" required aria-required="true">
            <option value="">Selecione...</option>
            <option value="email">E-mail</option>
            <option value="telefone">Telefone</option>
            <option value="whatsapp">WhatsApp</option>
          </select>
        </div>
        <div class="form-group">
          <label for="message">Mensagem / Dúvidas</label>
          <textarea id="message" name="message" rows="4" required aria-required="true"></textarea>
        </div>
        <button type="submit" class="btn-submit">Enviar Mensagem</button>
      </form>
      <div id="formFeedback" class="form-feedback" role="alert" aria-live="polite"></div>
    </section>
  </main>

  <script src="script-consultor.js"></script>
</body>
</html>
