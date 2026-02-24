<?php
/**
 * NF Organiza - Backend API (v4.0)
 * Suporte a busca flexível e persistência de XML.
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$host = 'localhost';
$db   = 'nforganiza';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['error' => 'Falha na conexão com o banco de dados: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        echo json_encode(['error' => 'Dados inválidos']);
        exit;
    }

    try {
        $sql = "INSERT INTO notas_fiscais (numero_nota, data_emissao, razao_social_emitente, cnpj_emitente, valor_total, xml_conteudo) 
                VALUES (:numero, :data_emissao, :razao_social, :cnpj, :valor, :xml)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':numero'       => $data['numero_nota'],
            ':data_emissao' => $data['data_emissao'],
            ':razao_social' => $data['razao_social_emitente'],
            ':cnpj'         => $data['cnpj_emitente'],
            ':valor'        => $data['valor_total'],
            ':xml'          => $data['xml_conteudo']
        ]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
    }
} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    if ($action === 'stats') {
        $mes = date('m');
        $ano = date('Y');
        
        $sql = "SELECT COUNT(*) as total, SUM(valor_total) as valor_total 
                FROM notas_fiscais 
                WHERE MONTH(data_emissao) = :mes AND YEAR(data_emissao) = :ano";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':mes' => $mes, ':ano' => $ano]);
        $result = $stmt->fetch();
        
        $result['total'] = $result['total'] ?? 0;
        $result['valor_total'] = $result['valor_total'] ?? 0;
        $result['mes_referencia'] = date('F/Y');
        
        echo json_encode($result);
    } elseif ($action === 'get_xml') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("SELECT xml_conteudo, numero_nota FROM notas_fiscais WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            if ($result) {
                echo json_encode($result);
                exit;
            }
        }
        echo json_encode(['error' => 'XML não encontrado']);
    } else {
        // LISTAGEM COM FILTROS FLEXÍVEIS
        $data_inicio = $_GET['data_inicio'] ?? null;
        $data_fim = $_GET['data_fim'] ?? null;
        $fornecedor = $_GET['fornecedor'] ?? null;

        $sql = "SELECT id, numero_nota, data_emissao, razao_social_emitente, cnpj_emitente, valor_total FROM notas_fiscais WHERE 1=1";
        $params = [];

        if ($data_inicio && $data_fim) {
            $sql .= " AND data_emissao BETWEEN :inicio AND :fim";
            $params[':inicio'] = $data_inicio . ' 00:00:00';
            $params[':fim'] = $data_fim . ' 23:59:59';
        }

        if ($fornecedor) {
            $sql .= " AND razao_social_emitente LIKE :fornecedor";
            $params[':fornecedor'] = '%' . $fornecedor . '%';
        }

        $sql .= " ORDER BY data_emissao DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    }
}
?>
