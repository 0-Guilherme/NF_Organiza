<?php
/**
 * NF Organiza - Backend API (v5.0)
 * Suporte completo a autenticação, busca flexível e persistência de XML.
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

function loadEnvFile(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '#') === 0) {
            continue;
        }

        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        $firstChar = substr($value, 0, 1);
        $lastChar = substr($value, -1);
        if (
            ($firstChar === '"' && $lastChar === '"') ||
            ($firstChar === "'" && $lastChar === "'")
        ) {
            $value = substr($value, 1, -1);
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

loadEnvFile(__DIR__ . '/.env');

$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'nforganiza';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

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

    $action = $data['action'] ?? null;

    // ========== AUTENTICAÇÃO - SIGNUP ==========
    if ($action === 'signup') {
        try {
            $nome = $data['nome'] ?? null;
            $email = $data['email'] ?? null;
            $senha = $data['senha'] ?? null;

            if (!$nome || !$email || !$senha) {
                echo json_encode(['error' => 'Nome, e-mail e senha são obrigatórios']);
                exit;
            }

            // Verificar se o e-mail já existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['error' => 'E-mail já cadastrado']);
                exit;
            }

            // Inserir novo usuário
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $email, $senhaHash]);

            $usuarioId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'id' => $usuarioId, 'nome' => $nome, 'email' => $email]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erro ao cadastrar: ' . $e->getMessage()]);
        }
    }
    // ========== AUTENTICAÇÃO - LOGIN ==========
    elseif ($action === 'login') {
        try {
            $email = $data['email'] ?? null;
            $senha = $data['senha'] ?? null;

            if (!$email || !$senha) {
                echo json_encode(['error' => 'E-mail e senha são obrigatórios']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if (!$usuario || !password_verify($senha, $usuario['senha'])) {
                echo json_encode(['error' => 'E-mail ou senha inválidos']);
                exit;
            }

            echo json_encode(['success' => true, 'id' => $usuario['id'], 'nome' => $usuario['nome'], 'email' => $usuario['email']]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erro ao fazer login: ' . $e->getMessage()]);
        }
    }
    // ========== SALVAR NOTA FISCAL ==========
    else {
        try {
            $usuarioId = $data['usuario_id'] ?? null;
            $numero = $data['numero_nota'] ?? null;
            $dataEmissao = $data['data_emissao'] ?? null;
            $razaoSocial = $data['razao_social_emitente'] ?? null;
            $cnpj = $data['cnpj_emitente'] ?? null;
            $valor = $data['valor_total'] ?? null;
            $xmlConteudo = $data['xml_conteudo'] ?? null;

            if (!$usuarioId || !$numero || !$dataEmissao || !$razaoSocial || !$cnpj || !$valor || !$xmlConteudo) {
                echo json_encode(['error' => 'Dados incompletos para salvar a nota']);
                exit;
            }

            $sql = "INSERT INTO notas_fiscais (usuario_id, numero_nota, data_emissao, razao_social_emitente, cnpj_emitente, valor_total, xml_conteudo) 
                    VALUES (:usuario_id, :numero, :data_emissao, :razao_social, :cnpj, :valor, :xml)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':usuario_id'   => $usuarioId,
                ':numero'       => $numero,
                ':data_emissao' => $dataEmissao,
                ':razao_social' => $razaoSocial,
                ':cnpj'         => $cnpj,
                ':valor'        => $valor,
                ':xml'          => $xmlConteudo
            ]);

            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erro ao salvar no banco: ' . $e->getMessage()]);
        }
    }
} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    $usuarioId = $_GET['usuario_id'] ?? null;

// ========== ESTATÍSTICAS (MÊS E ANO) ==========
    if ($action === 'stats') {
        try {
            $periodo = $_GET['periodo'] ?? 'mes'; 
            $anoAtual = date('Y');

            if ($periodo === 'mes') {
                $mes = date('m');
                $sql = "SELECT COUNT(*) as total, SUM(valor_total) as valor_total 
                        FROM notas_fiscais 
                        WHERE usuario_id = :usuario_id AND MONTH(data_emissao) = :mes AND YEAR(data_emissao) = :ano";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':usuario_id' => $usuarioId, ':mes' => $mes, ':ano' => $anoAtual]);
                
                $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                $mes_referencia = $meses[(int)$mes] . ' ' . $anoAtual;

            } elseif ($periodo === 'ano') {
                // Filtra APENAS pelo ano, ignorando o mês
                $sql = "SELECT COUNT(*) as total, SUM(valor_total) as valor_total 
                        FROM notas_fiscais 
                        WHERE usuario_id = :usuario_id AND YEAR(data_emissao) = :ano";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':usuario_id' => $usuarioId, ':ano' => $anoAtual]);
                
                $mes_referencia = 'Ano ' . $anoAtual;

            } else {
                $sql = "SELECT COUNT(*) as total, SUM(valor_total) as valor_total 
                        FROM notas_fiscais 
                        WHERE usuario_id = :usuario_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':usuario_id' => $usuarioId]);
                $mes_referencia = 'Geral';
            }

            $result = $stmt->fetch();
            $result['total'] = $result['total'] ?? 0;
            $result['valor_total'] = $result['valor_total'] ?? 0;
            $result['mes_referencia'] = $mes_referencia;
            
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erro ao carregar estatísticas: ' . $e->getMessage()]);
        }
    }
    // ========== OBTER XML PARA DOWNLOAD ==========
    elseif ($action === 'get_xml') {
        try {
            $id = $_GET['id'] ?? null;
            if ($id && $usuarioId) {
                $stmt = $pdo->prepare("SELECT xml_conteudo, numero_nota FROM notas_fiscais WHERE id = ? AND usuario_id = ?");
                $stmt->execute([$id, $usuarioId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                    exit;
                }
            }
            echo json_encode(['error' => 'XML não encontrado']);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erro ao obter XML: ' . $e->getMessage()]);
        }
    }
    // ========== LISTAGEM COM FILTROS FLEXÍVEIS ==========
    else {
        try {
            $dataInicio = $_GET['data_inicio'] ?? null;
            $dataFim = $_GET['data_fim'] ?? null;
            $fornecedor = $_GET['fornecedor'] ?? null;

            $sql = "SELECT id, numero_nota, data_emissao, razao_social_emitente, cnpj_emitente, valor_total FROM notas_fiscais WHERE usuario_id = :usuario_id";
            $params = [':usuario_id' => $usuarioId];

            if ($dataInicio && $dataFim) {
                $sql .= " AND data_emissao BETWEEN :inicio AND :fim";
                $params[':inicio'] = $dataInicio . ' 00:00:00';
                $params[':fim'] = $dataFim . ' 23:59:59';
            }

            if ($fornecedor) {
                $sql .= " AND razao_social_emitente LIKE :fornecedor";
                $params[':fornecedor'] = '%' . $fornecedor . '%';
            }

            $sql .= " ORDER BY data_emissao DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erro ao listar notas: ' . $e->getMessage()]);
        }
    }
} elseif ($method === 'DELETE') {
    try {
        $id = $_GET['id'] ?? null;
        $usuarioId = $_GET['usuario_id'] ?? null;
        
        if ($id && $usuarioId) {
            $stmt = $pdo->prepare("DELETE FROM notas_fiscais WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $usuarioId]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'ID ou usuário não fornecido']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao excluir: ' . $e->getMessage()]);
    }
}
?>
