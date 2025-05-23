<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Caminho ABSOLUTO corrigido para Windows com espaços
$logFile = __DIR__ . '\\login_log.txt'; 

// Verificação inicial de escrita
if (!file_put_contents($logFile, "Iniciando processo de login...\n", FILE_APPEND)) {
    die("Falha crítica: Não foi possível escrever no log em: " . $logFile);
}

// Cria arquivo de log
file_put_contents($logFile, "\n\n" . date('Y-m-d H:i:s') . " - Iniciando processo de login", FILE_APPEND);

require_once __DIR__ . '/../php/config.php';
session_start();

file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - Configurações carregadas", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = 'Método de requisição inválido';
    file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - ERRO: $error", FILE_APPEND);
    die($error);
}

// Coleta dados do formulário
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$senha = $_POST['password'] ?? '';

file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - Dados recebidos - Email: $email", FILE_APPEND);

// Validação básica
if (empty($email) || empty($senha)) {
    $error = 'Campos obrigatórios vazios';
    file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - ERRO: $error", FILE_APPEND);
    die($error);
}

try {
    file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - Tentando conexão com o banco", FILE_APPEND);
    
    // Busca o usuário no banco
    $stmt = $pdo->prepare("SELECT id, senha FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $stmt1 = $pdo->prepare("SELECT nome FROM usuario WHERE email = ?");
    $stmt1->execute([$email]);
    $user_name = $stmt1->fetchColumn();

    file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - Query executada. Linhas encontradas: " . $stmt->rowCount(), FILE_APPEND);

    if (!$user) {
        $error = 'Usuário não encontrado';
        file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - ERRO: $error", FILE_APPEND);
        die($error);
    }

    file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - Usuário encontrado. ID: " . $user['id'], FILE_APPEND);

    if (!password_verify($senha, $user['senha'])) {
        $error = 'Senha incorreta';
        file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - ERRO: $error", FILE_APPEND);
        die($error);
    }

    file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - Senha validada com sucesso", FILE_APPEND);
    
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

// Cria nova sessão com ID regenerado
    session_start();
    session_regenerate_id(true); // Força novo ID com exclusão do anterior

    $_SESSION = [
        'user_name' => $user_name,
        'user_id' => $user['id'],
        'logged_in' => true,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'ip_address' => $_SERVER['REMOTE_ADDR'], // Adicione esta linha
        'last_activity' => time()
    ];          
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - Sessão criada. Redirecionando...", FILE_APPEND);
    
    header('Location: ../dashboard/InvestmentPortfolioDashboard.php');
    exit();

} catch (PDOException $e) {
    $error = 'Erro no banco de dados: ' . $e->getMessage();
    file_put_contents($logFile, "\n" . date('Y-m-d H:i:s') . " - ERRO: $error", FILE_APPEND);
    die($error);
}

?>