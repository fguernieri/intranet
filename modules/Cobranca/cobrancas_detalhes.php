<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 0); // Em produção, erros devem ir para log
ini_set('log_errors',   1);

require_once $_SERVER['DOCUMENT_ROOT'].'/auth.php';
session_start();
if (empty($_SESSION['usuario_id'])) {
    header('Location:/login.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';
require_once __DIR__ . '/../../sidebar.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

$total_por_vendedor = [];
$total_por_periodo = [
    '1_6'   => ['label' => '1 a 6 dias', 'total' => 0.0, 'count' => 0],
    '7_14'  => ['label' => '7 a 14 dias', 'total' => 0.0, 'count' => 0],
    '15_30' => ['label' => '15 a 30 dias', 'total' => 0.0, 'count' => 0],
    '31_90' => ['label' => '31 a 90 dias', 'total' => 0.0, 'count' => 0],
    'gt_90' => ['label' => 'Acima de 90 dias', 'total' => 0.0, 'count' => 0],
];
$grand_total_aberto = 0.0;
$database_error_message = null;

if (!$conn->connect_error) {
    $sql = "SELECT VENDEDOR, 
                   TOTAL_COM_JUROS AS VALOR_VENCIDO, 
                   DIAS_VENCIDOS 
            FROM vw_cobrancas_vencidas";
    
    if ($rs = $conn->query($sql)) {
        while ($row = $rs->fetch_assoc()) {
            $vendedor = $row['VENDEDOR'] ?: '(Não Especificado)';
            $valor_vencido = (float)$row['VALOR_VENCIDO'];
            $dias_vencidos = (int)$row['DIAS_VENCIDOS'];

            // Total por Vendedor
            $total_por_vendedor[$vendedor] = ($total_por_vendedor[$vendedor] ?? 0.0) + $valor_vencido;

            // Total por Período
            if ($dias_vencidos >= 1 && $dias_vencidos <= 6) {
                $total_por_periodo['1_6']['total'] += $valor_vencido;
                $total_por_periodo['1_6']['count']++;
            } elseif ($dias_vencidos >= 7 && $dias_vencidos <= 14) {
                $total_por_periodo['7_14']['total'] += $valor_vencido;
                $total_por_periodo['7_14']['count']++;
            } elseif ($dias_vencidos >= 15 && $dias_vencidos <= 30) {
                $total_por_periodo['15_30']['total'] += $valor_vencido;
                $total_por_periodo['15_30']['count']++;
            } elseif ($dias_vencidos >= 31 && $dias_vencidos <= 90) {
                $total_por_periodo['31_90']['total'] += $valor_vencido;
                $total_por_periodo['31_90']['count']++;
            } elseif ($dias_vencidos > 90) {
                $total_por_periodo['gt_90']['total'] += $valor_vencido;
                $total_por_periodo['gt_90']['count']++;
            }
            $grand_total_aberto += $valor_vencido;
        }
        $rs->free();
        if (!empty($total_por_vendedor)) {
            arsort($total_por_vendedor); // Ordena vendedores por maior valor apenas se não estiver vazio
        }
    } else {
        error_log('SQL falhou em cobrancas_detalhes.php: ' . $conn->error);
        $database_error_message = "Erro ao consultar os dados de cobrança.";
    }
    $conn->close();
} else {
    error_log('Falha de conexão em cobrancas_detalhes.php: ' . $conn->connect_error);
    $database_error_message = "Não foi possível conectar ao banco de dados.";
}

function format_currency(float $value): string {
    return 'R$ ' . number_format($value, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhamento de Cobranças</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        /* body { Tailwind já cuida do bg-gray-900 text-gray-100 } */
        .panel { background: #1a1a1a; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .panel-title { font-size: 1.5em; font-weight: bold; color: #facc15; /* Tailwind yellow-400 */ margin-bottom: 1rem; text-align: center; }
        .detail-table { width: 100%; border-collapse: collapse; font-size: 0.9em; }
        .detail-table th, .detail-table td { padding: 0.75rem 1rem; border: 1px solid #333; text-align: left; }
        .detail-table thead th { background: #2a2a2a; color: #facc15; /* Tailwind yellow-400 */ }
        .detail-table tbody tr:nth-child(even) { background: #222; }
        .detail-table td.currency { text-align: right; font-weight: bold; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; } /* Renomeado e ajustado minmax */
        .summary-item-card { background: #2a2a2a; padding: 0.75rem; border-radius: 8px; text-align: center; display: flex; flex-direction: column; justify-content: space-between; height: 100%;}
        .summary-item-card .label { font-size: 0.8rem; color: #cbd5e1; margin-bottom: 0.25rem; }
        .summary-item-card .value { font-size: 1.1rem; font-weight: bold; margin-bottom: 0.1rem; }
        .summary-item-card .count { font-size: 0.7rem; color: #94a3b8; }
        .summary-item-card.total-geral-card { background: #111; /* Fundo mais escuro para o total geral */ border: 1px solid #333; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 flex min-h-screen">

    <!-- SIDEBAR -->

    <main class="flex-1 bg-gray-900 p-6">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-yellow-400">Detalhamento de Cobranças</h1>
            <a href="/modules/Cobranca/cobrancas.php" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg text-sm shadow-md transition duration-150 ease-in-out">
                &larr; Voltar ao Painel Principal
            </a>
        </div>

        <?php if ($database_error_message): ?>
            <div class="panel bg-red-700 text-white">
                <h2 class="panel-title text-white">Erro no Sistema</h2>
                <p class="text-center"><?= htmlspecialchars($database_error_message) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$database_error_message): // Só mostra os painéis de dados se não houver erro de banco ?>
            <!-- Painel Único para Todos os Resumos -->
            <div class="panel mb-6">
                <h2 class="panel-title mb-3">Resumo de cobranças em aberto</h2>
                <div class="summary-grid">
                    <!-- Card Total Geral em Aberto (com destaque) -->
                    <div class="summary-item-card total-geral-card">
                        <div class="label">Total Geral em Aberto</div>
                        <div class="value text-red-400"><?= format_currency($grand_total_aberto) ?></div>
                        <div class="count">&nbsp;</div>
                    </div>

                    <!-- Cards por Período de Vencimento -->
                    <?php foreach ($total_por_periodo as $periodo_key => $data): ?>
                    <?php if ($data['total'] > 0 || $data['count'] > 0): // Opcional: mostrar apenas se houver dados ?>
                    <div class="summary-item-card">
                        <div class="label"><?= htmlspecialchars($data['label']) ?></div>
                        <div class="value text-red-400"><?= format_currency($data['total']) ?></div>
                        <div class="count">(<?= $data['count'] ?> ocorrências)</div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Painel Tabela Total em Aberto por Vendedor (Abaixo dos resumos) -->
            <div class="panel">
                <h2 class="panel-title">Total em aberto por vendedor</h2>
                <?php if (!empty($total_por_vendedor)): ?>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th class="currency">Total em Aberto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($total_por_vendedor as $vendedor => $total): ?>
                        <tr>
                            <td><?= htmlspecialchars($vendedor) ?></td>
                            <td class="currency"><?= format_currency($total) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center text-gray-400">Nenhum dado de vendedor encontrado.</p>
                <?php endif; ?>
            </div>
        <?php endif; // Fim do if (!$database_error_message) ?>
    </main>

</body>
</html>