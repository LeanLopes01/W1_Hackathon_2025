<?php
    define('CONFIG_LOADED', true);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']), // TRUE se HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    ini_set('session.cookie_httponly', 1); // Impede acesso via JavaScript
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
        ini_set('session.cookie_secure', 0); // Desativa SSL localmente
        error_reporting(E_ALL); // Mostra todos os erros
        ini_set('display_errors', 1);
    } else {
        ini_set('session.cookie_secure', 1); // Ativa em produção
    }
    ini_set('session.use_strict_mode', 1); // Prevenção contra session fixation
    ini_set('session.gc_maxlifetime', 1800); // Tempo de vida da sessão (30 minutos)
    ini_set('session.cookie_samesite', 'Lax'); // Proteção contra CSRF
    
    // config.php: dados de conexão com o banco de dados
    $host   = 'localhost';
    $db     = 'site_w1';      // ajuste para o nome do seu banco
    $user   = 'hack';
    $pass   = '1234';                // padrão do MAMP
    $charset= 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die('Erro de conexão: ' . $e->getMessage());
    }
?>