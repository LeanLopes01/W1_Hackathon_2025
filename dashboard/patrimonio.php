

<?php
/*
ESSE CÓDIGO É APENAS PARA O USUARIO COLOCAR UM VALOR DE PATRIMONIO BRUTO TOTAL PARA MOSTRAR COMO O DASHBOARD FUNCIONA,
Claramente terá que ser alterado para o que realmente o cliente possui, aqui é apenas para desmonstrar a funcionabilidade do dashboard.
Também vamos colocar ações, bdrs, tesouro direto "aleatórios" também para o propósito da visibilidade do funcionamento.
*/
require_once __DIR__ . '/../php/config.php';
session_start();


// Verificar autenticação
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit();
}

$error = '';
$success = '';



// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt_check = $pdo->prepare("SELECT id FROM usuario WHERE id = ?");
        $stmt_check->execute([$_SESSION['user_id']]);
        $user_exists = $stmt_check->fetch();

        if (!$user_exists) {
            throw new Exception("Usuário não encontrado!");
        }
        // Sanitizar e validar inputs
        $valores = [
            'aposentadoria' => filter_input(INPUT_POST, 'aposentadoria', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'viagens' => filter_input(INPUT_POST, 'viagens', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'casamento' => filter_input(INPUT_POST, 'casamento', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'filhos' => filter_input(INPUT_POST, 'filhos', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'bolsa' => filter_input(INPUT_POST, 'bolsa', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'fundos' => filter_input(INPUT_POST, 'fundos', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'bdrs' => filter_input(INPUT_POST, 'bdrs', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'crypto' => filter_input(INPUT_POST, 'crypto', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)
        ];

        // Validar valores
        foreach ($valores as $key => $value) {
            if (!filter_var($value, FILTER_VALIDATE_FLOAT) && $value !== '') {
                throw new Exception("Valor inválido para " . ucfirst($key));
            }
        }

        // Inserir no banco
        $stmt = $pdo->prepare("INSERT INTO dados_patri (
            qtd_aposentadoria, 
            qtd_viagens, 
            qtd_casamento, 
            qtd_filhos, 
            qtd_bolsa, 
            qtd_fundInves, 
            qtd_bdrs, 
            qtd_crypto, 
            user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $success = $params = [
            $valores['aposentadoria'] ?? 0.00,
            $valores['viagens'] ?? 0.00,
            $valores['casamento'] ?? 0.00,
            $valores['filhos'] ?? 0.00,
            $valores['bolsa'] ?? 0.00,
            $valores['fundos'] ?? 0.00,
            $valores['bdrs'] ?? 0.00,
            $valores['crypto'] ?? 0.00,
            $_SESSION['user_id']
        ];

        if ($stmt->execute($params)) {

            $user_id = $_SESSION['user_id'];
            $pythonPath = 'C:\\Program Files\\Python311\\python.exe'; // Caminho completo com escape
            $scriptPath = realpath(__DIR__ . '/../dashboard/api/dados.py');
            $command = 'START /B "CalculoPatrimonio" "' . $pythonPath . '" "' . $scriptPath . '" --user-id=' . escapeshellarg($user_id);
            shell_exec($command);

            $_SESSION['success_message'] = 'Dados salvos com sucesso!';
            header('Location: InvestmentPortfolioDashboard.php');
            exit();
        } else {
            throw new Exception('Erro ao salvar dados: ' . implode(', ', $stmt->errorInfo()));
        }

        if ($stmt->execute([/* valores */])) {
            $_SESSION['success_message'] = 'Dados salvos com sucesso!';
            header('Location: InvestmentPortfolioDashboard.php');
            exit();
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cálculo Patrimonial</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .patrimonio-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn-salvar {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background 0.3s;
        }

        .btn-salvar:hover {
            background: #34495e;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-voltar {
            display: inline-block;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <main class="content">
        <div class="patrimonio-container">
            <a href="InvestmentPortfolioDashboard.php" class="btn-voltar">← Voltar</a>
            <h1>Calcule seu Patrimônio</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="input-group">
                        <label>Quantia em Aposentadoria</label>
                        <input type="number" step="0.01" name="aposentadoria" required>
                    </div>

                    <div class="input-group">
                        <label>Quantia salva para viagens</label>
                        <input type="number" step="0.01" name="viagens" required>
                    </div>

                    <div class="input-group">
                        <label>Quantia salva para casamento</label>
                        <input type="number" step="0.01" name="casamento" required>
                    </div>

                    <div class="input-group">
                        <label>Quantia salva para filhos</label>
                        <input type="number" step="0.01" name="filhos" required>
                    </div>

                    <div class="input-group">
                        <label>Quantia em Bolsa</label>
                        <input type="number" step="0.01" name="bolsa" required>
                    </div>

                    <div class="input-group">
                        <label>Fundos de investimento</label>
                        <input type="number" step="0.01" name="fundos" required>
                    </div>

                    <div class="input-group">
                        <label>Ações Globais (BDRs)</label>
                        <input type="number" step="0.01" name="bdrs" required>
                    </div>

                    <div class="input-group">
                        <label>Crypto</label>
                        <input type="number" step="0.01" name="crypto" required>
                    </div>
                </div>

                <button type="submit" class="btn-salvar">Salvar Patrimônio</button>
            </form>
        </div>
    </main>

    <script src="../script.js"></script>
</body>
</html>