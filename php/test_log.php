<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define o caminho ABSOLUTO para garantir
$logFile = __DIR__ . '\\test_log.txt';  // Usa a pasta do script

echo "<h3>Teste de Logs no Windows</h3>";
echo "Script path: " . __DIR__ . "<br>";
echo "Trying to create: " . $logFile . "<br>";

// Teste 0: Verificar existência prévia
if (file_exists($logFile)) {
    echo "Arquivo já existe!<br>";
    unlink($logFile);
}

// Teste 1: Criação
if (file_put_contents($logFile, "Teste 1 OK\n")) {
    echo "Arquivo criado!<br>";
    
    // Teste 2: Permissões
    echo "Permissões: " . decoct(fileperms($logFile)) . "<br>";
    if (is_writable($logFile)) {
        echo "Escrita OK<br>";
        
        // Teste 3: Append
        if (file_put_contents($logFile, "Teste 3 OK\n", FILE_APPEND)) {
            echo "Append OK<br>";
            
            // Teste 4: Conteúdo
            $content = htmlspecialchars(file_get_contents($logFile));
            echo "Conteúdo:<pre>$content</pre>";
            
            // Limpeza
            if (unlink($logFile)) {
                echo "Arquivo removido!";
            } else {
                echo "Falha ao remover!";
            }
        } else {
            die("Erro append: " . error_get_last()['message']);
        }
    } else {
        die("Arquivo não é gravável");
    }
} else {
    die("Falha criação. Erro: " . error_get_last()['message']);
}