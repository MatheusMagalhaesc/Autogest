<?php
/**
 * Funções de acesso a dados. Sprints 2 a 5.
 */

require_once __DIR__ . '/../config/database.php';

// =====================================================================
// SPRINT 2 — CLIENTES
// =====================================================================

function listarClientes(string $busca = ''): array
{
    $pdo = Database::getConnection();
    if ($busca !== '') {
        $stmt = $pdo->prepare('SELECT * FROM clientes WHERE nome LIKE ? OR telefone LIKE ? OR cpf_cnpj LIKE ? ORDER BY nome ASC');
        $like = '%' . $busca . '%';
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }
    return $pdo->query('SELECT * FROM clientes ORDER BY nome ASC')->fetchAll();
}

function buscarCliente(int $id)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function criarCliente(string $nome, string $cpfCnpj, string $telefone, string $email, string $endereco)
{
    if ($nome === '') return 'O nome do cliente é obrigatório.';
    if ($telefone === '') return 'O telefone do cliente é obrigatório.';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('INSERT INTO clientes (nome, cpf_cnpj, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$nome, $cpfCnpj ?: null, $telefone, $email ?: null, $endereco ?: null]);
    return (int) $pdo->lastInsertId();
}

function atualizarCliente(int $id, string $nome, string $cpfCnpj, string $telefone, string $email, string $endereco)
{
    if ($nome === '') return 'O nome do cliente é obrigatório.';
    if ($telefone === '') return 'O telefone do cliente é obrigatório.';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE clientes SET nome = ?, cpf_cnpj = ?, telefone = ?, email = ?, endereco = ? WHERE id = ?');
    $stmt->execute([$nome, $cpfCnpj ?: null, $telefone, $email ?: null, $endereco ?: null, $id]);
    return true;
}

function excluirCliente(int $id): bool
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('DELETE FROM clientes WHERE id = ?');
    return $stmt->execute([$id]);
}

function contarClientes(): int
{
    $pdo = Database::getConnection();
    return (int) $pdo->query('SELECT COUNT(*) FROM clientes')->fetchColumn();
}

// =====================================================================
// SPRINT 3 — VEÍCULOS
// =====================================================================

const COMBUSTIVEIS = ['flex' => 'Flex', 'gasolina' => 'Gasolina', 'etanol' => 'Etanol', 'diesel' => 'Diesel', 'hibrido' => 'Híbrido', 'eletrico' => 'Elétrico'];

function listarVeiculos(string $busca = ''): array
{
    $pdo = Database::getConnection();
    $sql = 'SELECT v.*, c.nome AS cliente_nome FROM veiculos v JOIN clientes c ON c.id = v.cliente_id';
    if ($busca !== '') {
        $sql .= ' WHERE v.placa LIKE ? OR v.modelo LIKE ? OR c.nome LIKE ?';
        $stmt = $pdo->prepare($sql . ' ORDER BY v.modelo');
        $like = '%' . $busca . '%';
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }
    return $pdo->query($sql . ' ORDER BY v.modelo')->fetchAll();
}

function buscarVeiculoPorPlaca(string $placa): array
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        'SELECT v.*, c.nome AS cliente_nome FROM veiculos v JOIN clientes c ON c.id = v.cliente_id WHERE v.placa LIKE ? ORDER BY v.modelo'
    );
    $stmt->execute(['%' . $placa . '%']);
    return $stmt->fetchAll();
}

function listarVeiculosDoCliente(int $clienteId): array
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM veiculos WHERE cliente_id = ? ORDER BY modelo');
    $stmt->execute([$clienteId]);
    return $stmt->fetchAll();
}

function buscarVeiculo(int $id)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT v.*, c.nome AS cliente_nome FROM veiculos v JOIN clientes c ON c.id = v.cliente_id WHERE v.id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function criarVeiculo(int $clienteId, string $placa, string $marca, string $modelo, string $ano, $km, string $cor, string $combustivel)
{
    if (!$clienteId) return 'Selecione o cliente dono do veículo.';
    if ($placa === '') return 'Informe a placa do veículo.';
    if ($modelo === '') return 'Informe o modelo do veículo.';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('INSERT INTO veiculos (cliente_id, placa, marca, modelo, ano, km, cor, combustivel) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$clienteId, strtoupper($placa), $marca ?: null, $modelo, $ano ?: null, (int) $km, $cor ?: null, $combustivel ?: 'flex']);
    return (int) $pdo->lastInsertId();
}

function atualizarVeiculo(int $id, string $placa, string $marca, string $modelo, string $ano, $km, string $cor, string $combustivel)
{
    if ($placa === '') return 'Informe a placa do veículo.';
    if ($modelo === '') return 'Informe o modelo do veículo.';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE veiculos SET placa = ?, marca = ?, modelo = ?, ano = ?, km = ?, cor = ?, combustivel = ? WHERE id = ?');
    $stmt->execute([strtoupper($placa), $marca ?: null, $modelo, $ano ?: null, (int) $km, $cor ?: null, $combustivel ?: 'flex', $id]);
    return true;
}

function excluirVeiculo(int $id): bool
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('DELETE FROM veiculos WHERE id = ?');
    return $stmt->execute([$id]);
}

function contarVeiculos(): int
{
    $pdo = Database::getConnection();
    return (int) $pdo->query('SELECT COUNT(*) FROM veiculos')->fetchColumn();
}

// =====================================================================
// SERVIÇOS (catálogo mínimo — expandido na Sprint 7)
// =====================================================================

function listarServicos(bool $somenteAtivos = false): array
{
    $pdo = Database::getConnection();
    $sql = 'SELECT * FROM servicos';
    if ($somenteAtivos) $sql .= ' WHERE ativo = 1';
    $sql .= ' ORDER BY nome ASC';
    return $pdo->query($sql)->fetchAll();
}

function buscarServico(int $id)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM servicos WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function criarServico(string $nome, string $descricao, $preco)
{
    if ($nome === '') return 'O nome do serviço é obrigatório.';
    $preco = str_replace(',', '.', (string) $preco);
    if (!is_numeric($preco) || (float) $preco < 0) return 'Informe um preço válido.';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('INSERT INTO servicos (nome, descricao, preco, ativo) VALUES (?, ?, ?, 1)');
    $stmt->execute([$nome, $descricao ?: null, (float) $preco]);
    return (int) $pdo->lastInsertId();
}

function atualizarServico(int $id, string $nome, string $descricao, $preco)
{
    if ($nome === '') return 'O nome do serviço é obrigatório.';
    $preco = str_replace(',', '.', (string) $preco);
    if (!is_numeric($preco) || (float) $preco < 0) return 'Informe um preço válido.';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE servicos SET nome = ?, descricao = ?, preco = ? WHERE id = ?');
    $stmt->execute([$nome, $descricao ?: null, (float) $preco, $id]);
    return true;
}

function alternarStatusServico(int $id): bool
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE servicos SET ativo = NOT ativo WHERE id = ?');
    return $stmt->execute([$id]);
}

// =====================================================================
// SPRINT 4 — AGENDA / AGENDAMENTOS
// =====================================================================

const STATUS_AGENDAMENTO = ['agendado' => 'Agendado', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];

function baseSelectAgendamentos(): string
{
    return 'SELECT a.*, c.nome AS cliente_nome, c.telefone AS cliente_telefone,
                   v.marca, v.modelo AS veiculo_modelo, v.placa AS veiculo_placa,
                   s.nome AS servico_nome, s.preco AS servico_preco,
                   m.nome AS mecanico_nome
            FROM agendamentos a
            JOIN clientes c ON c.id = a.cliente_id
            JOIN veiculos v ON v.id = a.veiculo_id
            JOIN servicos s ON s.id = a.servico_id
            LEFT JOIN usuarios m ON m.id = a.mecanico_id';
}

function listarAgendamentos(array $filtros = []): array
{
    $pdo = Database::getConnection();
    $sql = baseSelectAgendamentos();
    $cond = [];
    $params = [];

    if (!empty($filtros['data']))        { $cond[] = 'a.data_agendamento = ?'; $params[] = $filtros['data']; }
    if (!empty($filtros['status']))      { $cond[] = 'a.status = ?'; $params[] = $filtros['status']; }
    if (!empty($filtros['cliente_id']))  { $cond[] = 'a.cliente_id = ?'; $params[] = $filtros['cliente_id']; }
    if (!empty($filtros['veiculo_id']))  { $cond[] = 'a.veiculo_id = ?'; $params[] = $filtros['veiculo_id']; }
    if (!empty($filtros['mecanico_id'])) { $cond[] = 'a.mecanico_id = ?'; $params[] = $filtros['mecanico_id']; }
    if (!empty($filtros['data_ini']))    { $cond[] = 'a.data_agendamento >= ?'; $params[] = $filtros['data_ini']; }
    if (!empty($filtros['data_fim']))    { $cond[] = 'a.data_agendamento <= ?'; $params[] = $filtros['data_fim']; }

    if ($cond) $sql .= ' WHERE ' . implode(' AND ', $cond);
    $sql .= ' ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function buscarAgendamento(int $id)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(baseSelectAgendamentos() . ' WHERE a.id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Verifica conflito de horário PARA O MESMO MECÂNICO (Sprint 4 — HU2).
 * Se o agendamento não tiver mecânico definido, não há conflito a verificar.
 */
function existeConflitoMecanico(?int $mecanicoId, string $data, string $hora, ?int $idIgnorar = null): bool
{
    if (!$mecanicoId) {
        return false;
    }

    $pdo = Database::getConnection();
    $sql = "SELECT COUNT(*) FROM agendamentos
            WHERE mecanico_id = ? AND data_agendamento = ? AND hora_agendamento = ? AND status != 'cancelado'";
    $params = [$mecanicoId, $data, $hora];

    if ($idIgnorar !== null) {
        $sql .= ' AND id != ?';
        $params[] = $idIgnorar;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return ((int) $stmt->fetchColumn()) > 0;
}

function criarAgendamento(int $clienteId, int $veiculoId, int $servicoId, ?int $mecanicoId, string $data, string $hora, string $observacoes)
{
    if (!$clienteId || !$veiculoId || !$servicoId) return 'Selecione o cliente, o veículo e o serviço.';
    if ($data === '' || $hora === '') return 'Informe a data e o horário do agendamento.';
    if (existeConflitoMecanico($mecanicoId, $data, $hora)) {
        return 'Este mecânico já possui outro agendamento neste mesmo horário.';
    }

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO agendamentos (cliente_id, veiculo_id, servico_id, mecanico_id, data_agendamento, hora_agendamento, status, observacoes)
         VALUES (?, ?, ?, ?, ?, ?, "agendado", ?)'
    );
    $stmt->execute([$clienteId, $veiculoId, $servicoId, $mecanicoId ?: null, $data, $hora, $observacoes ?: null]);
    return (int) $pdo->lastInsertId();
}

function atualizarAgendamento(int $id, int $veiculoId, int $servicoId, ?int $mecanicoId, string $data, string $hora, string $observacoes)
{
    if (!$veiculoId || !$servicoId || $data === '' || $hora === '') return 'Preencha todos os campos obrigatórios.';
    if (existeConflitoMecanico($mecanicoId, $data, $hora, $id)) {
        return 'Este mecânico já possui outro agendamento neste mesmo horário.';
    }

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        'UPDATE agendamentos SET veiculo_id = ?, servico_id = ?, mecanico_id = ?, data_agendamento = ?, hora_agendamento = ?, observacoes = ? WHERE id = ?'
    );
    $stmt->execute([$veiculoId, $servicoId, $mecanicoId ?: null, $data, $hora, $observacoes ?: null, $id]);
    return true;
}

function cancelarAgendamento(int $id): bool
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE agendamentos SET status = "cancelado" WHERE id = ?');
    return $stmt->execute([$id]);
}

/**
 * Agendamentos com data futura próxima (próximos 2 dias) — usado como "lembrete" no dashboard.
 */
function agendamentosProximos(int $dias = 2): array
{
    $hoje = date('Y-m-d');
    $limite = date('Y-m-d', strtotime("+$dias days"));
    return listarAgendamentos(['data_ini' => $hoje, 'data_fim' => $limite, 'status' => 'agendado']);
}

function agendamentosDoDia(string $data): array
{
    return listarAgendamentos(['data' => $data]);
}

// =====================================================================
// SPRINT 5 — ORDEM DE SERVIÇO
// =====================================================================

const STATUS_OS = ['agendado' => 'Agendado', 'em_atendimento' => 'Em atendimento', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];

function baseSelectOS(): string
{
    return "SELECT os.*, c.nome AS cliente_nome, c.telefone AS cliente_telefone,
                   v.marca, v.modelo AS veiculo_modelo, v.placa AS veiculo_placa,
                   m.nome AS mecanico_nome
            FROM ordens_servico os
            JOIN clientes c ON c.id = os.cliente_id
            JOIN veiculos v ON v.id = os.veiculo_id
            LEFT JOIN usuarios m ON m.id = os.mecanico_id";
}

function listarOS(array $filtros = []): array
{
    $pdo = Database::getConnection();
    $sql = baseSelectOS();
    $cond = [];
    $params = [];

    if (!empty($filtros['status']))      { $cond[] = 'os.status = ?'; $params[] = $filtros['status']; }
    if (!empty($filtros['cliente_id']))  { $cond[] = 'os.cliente_id = ?'; $params[] = $filtros['cliente_id']; }
    if (!empty($filtros['veiculo_id']))  { $cond[] = 'os.veiculo_id = ?'; $params[] = $filtros['veiculo_id']; }
    if (!empty($filtros['busca'])) {
        $cond[] = '(c.nome LIKE ? OR v.placa LIKE ?)';
        $like = '%' . $filtros['busca'] . '%';
        $params[] = $like; $params[] = $like;
    }

    if ($cond) $sql .= ' WHERE ' . implode(' AND ', $cond);
    $sql .= ' ORDER BY os.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function buscarOS(int $id)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(baseSelectOS() . ' WHERE os.id = ?');
    $stmt->execute([$id]);
    $os = $stmt->fetch();
    if (!$os) return false;

    $os['itens'] = listarItensOS($id);
    $os['fotos'] = listarFotosOS($id);
    $os['total'] = array_reduce($os['itens'], fn($acc, $i) => $acc + ($i['quantidade'] * $i['valor_unitario']), 0.0);

    return $os;
}

function historicoOSDoVeiculo(int $veiculoId): array
{
    return listarOS(['veiculo_id' => $veiculoId]);
}

function historicoOSDoCliente(int $clienteId): array
{
    return listarOS(['cliente_id' => $clienteId]);
}

function criarOS(int $clienteId, int $veiculoId, ?int $mecanicoId, $kmAtual, string $problema, ?int $agendamentoId = null)
{
    if (!$clienteId || !$veiculoId) return 'Selecione o cliente e o veículo.';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO ordens_servico (agendamento_id, cliente_id, veiculo_id, mecanico_id, km_atual, problema_relatado, status)
         VALUES (?, ?, ?, ?, ?, ?, "agendado")'
    );
    $stmt->execute([$agendamentoId ?: null, $clienteId, $veiculoId, $mecanicoId ?: null, (int) $kmAtual, $problema ?: null]);
    return (int) $pdo->lastInsertId();
}

function atualizarDiagnosticoOS(int $id, string $diagnostico, ?int $mecanicoId)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE ordens_servico SET diagnostico = ?, mecanico_id = ? WHERE id = ?');
    return $stmt->execute([$diagnostico ?: null, $mecanicoId ?: null, $id]);
}

function atualizarStatusOS(int $id, string $novoStatus)
{
    if (!array_key_exists($novoStatus, STATUS_OS)) return 'Status inválido.';
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('UPDATE ordens_servico SET status = ? WHERE id = ?');
    $stmt->execute([$novoStatus, $id]);
    return true;
}

function listarItensOS(int $osId): array
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM os_itens WHERE os_id = ? ORDER BY id');
    $stmt->execute([$osId]);
    return $stmt->fetchAll();
}

function adicionarItemOS(int $osId, string $tipo, ?int $servicoId, string $descricao, $quantidade, $valorUnitario)
{
    if ($descricao === '') return 'Informe a descrição do item.';
    $quantidade = max(1, (int) $quantidade);
    $valorUnitario = str_replace(',', '.', (string) $valorUnitario);
    if (!is_numeric($valorUnitario) || (float) $valorUnitario < 0) return 'Informe um valor válido.';

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO os_itens (os_id, tipo, servico_id, descricao, quantidade, valor_unitario) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$osId, $tipo === 'peca' ? 'peca' : 'servico', $servicoId ?: null, $descricao, $quantidade, (float) $valorUnitario]);
    return true;
}

function excluirItemOS(int $itemId): bool
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('DELETE FROM os_itens WHERE id = ?');
    return $stmt->execute([$itemId]);
}

function listarFotosOS(int $osId): array
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT * FROM os_fotos WHERE os_id = ? ORDER BY id');
    $stmt->execute([$osId]);
    return $stmt->fetchAll();
}

function adicionarFotoOS(int $osId, string $caminhoArquivo): bool
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('INSERT INTO os_fotos (os_id, caminho_arquivo) VALUES (?, ?)');
    return $stmt->execute([$osId, $caminhoArquivo]);
}

function excluirFotoOS(int $fotoId)
{
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT caminho_arquivo FROM os_fotos WHERE id = ?');
    $stmt->execute([$fotoId]);
    $foto = $stmt->fetch();
    if ($foto) {
        $caminhoCompleto = UPLOAD_OS_DIR . '/' . basename($foto['caminho_arquivo']);
        if (file_exists($caminhoCompleto)) {
            @unlink($caminhoCompleto);
        }
        $pdo->prepare('DELETE FROM os_fotos WHERE id = ?')->execute([$fotoId]);
    }
    return true;
}

// =====================================================================
// DASHBOARD (indicadores básicos disponíveis nas Sprints 1-5)
// =====================================================================

function statsDashboard(): array
{
    $pdo = Database::getConnection();
    $hoje = date('Y-m-d');

    $stmtHoje = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = ? AND status != 'cancelado'");
    $stmtHoje->execute([$hoje]);

    $stmtOSAbertas = $pdo->query("SELECT COUNT(*) FROM ordens_servico WHERE status IN ('agendado','em_atendimento')");

    return [
        'clientes'          => contarClientes(),
        'veiculos'          => contarVeiculos(),
        'agendamentos_hoje' => (int) $stmtHoje->fetchColumn(),
        'os_abertas'        => (int) $stmtOSAbertas->fetchColumn(),
    ];
}
