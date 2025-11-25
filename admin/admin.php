<?php
// admin/admin.php
include '../includes/conexao.php'; // Conexão com o BD 

// --- Lógica de CRUD (Update e Delete) --- 

// 1. Excluir Tarefa (Delete)
if (isset($_GET['excluir_id'])) {
    $id = (int)$_GET['excluir_id'];
    $sql = "DELETE FROM tarefas WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin.php?sucesso=exclusao");
        exit();
    } else {
        $erro = "Erro ao excluir: " . $conn->error;
    }
}

// 2. Processar Edição de Tarefa (Update)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar'])) {
    $id = (int)$_POST['id'];
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $concluida = isset($_POST['concluida']) ? 1 : 0;
    $categoria_id = (int)$_POST['categoria_id'];

    if (!empty($titulo)) {
        $sql = "UPDATE tarefas SET 
                titulo = '$titulo', 
                descricao = '$descricao', 
                concluida = $concluida,
                categoria_id = $categoria_id
                WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: admin.php?sucesso=edicao");
            exit();
        } else {
            $erro = "Erro ao atualizar: " . $conn->error;
        }
    } else {
        $erro = "O título da tarefa não pode ser vazio.";
    }
}

// Consulta para Listar Tarefas (Read - Tabela de Gerenciamento)
$sql_tarefas = "SELECT t.id, t.titulo, t.descricao, t.concluida, c.nome as categoria_nome 
                FROM tarefas t 
                LEFT JOIN categorias c ON t.categoria_id = c.id 
                ORDER BY t.data_criacao DESC";
$result_tarefas = $conn->query($sql_tarefas);

// Consulta para Categorias (para o formulário de edição)
$sql_categorias = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$result_categorias = $conn->query($sql_categorias);

// Buscar dados da tarefa para edição (se 'editar_id' estiver na URL)
$tarefa_editar = null;
$categorias_edicao = [];
if (isset($_GET['editar_id'])) {
    $id_editar = (int)$_GET['editar_id'];
    $sql_edit = "SELECT id, titulo, descricao, concluida, categoria_id FROM tarefas WHERE id = $id_editar";
    $result_edit = $conn->query($sql_edit);
    if ($result_edit->num_rows > 0) {
        $tarefa_editar = $result_edit->fetch_assoc();
        // Resetar o ponteiro das categorias para o formulário
        $result_categorias_edicao = $conn->query($sql_categorias);
        while($cat = $result_categorias_edicao->fetch_assoc()) {
            $categorias_edicao[] = $cat;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Tarefas</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .tabela-admin {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tabela-admin th, .tabela-admin td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .tabela-admin th {
            background-color: #eee;
            font-weight: 700;
        }
        .form-edicao {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            border-top: 5px solid #f39c12;
        }
    </style>
</head>
<body>
    <header>
        <h1>🛠️ Painel Administrativo de Tarefas</h1>
    </header>

    <main>
        <?php if (isset($erro)): ?>
            <p style='color: red; padding: 10px; background: #fdd; border: 1px solid red; border-radius: 4px;'>
                Erro: <?php echo $erro; ?>
            </p>
        <?php endif; ?>
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'edicao'): ?>
            <p style='color: green; padding: 10px; background: #dfd; border: 1px solid green; border-radius: 4px;'>
                Tarefa editada com sucesso!
            </p>
        <?php endif; ?>
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'exclusao'): ?>
            <p style='color: orange; padding: 10px; background: #ffe; border: 1px solid orange; border-radius: 4px;'>
                Tarefa excluída com sucesso!
            </p>
        <?php endif; ?>

        <?php if ($tarefa_editar): ?>
            <div class="form-edicao">
                <h2>Editar Tarefa #<?php echo $tarefa_editar['id']; ?></h2>
                <form action="admin.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $tarefa_editar['id']; ?>">
                    
                    <label for="titulo_edit">Título da Tarefa:</label>
                    <input type="text" id="titulo_edit" name="titulo" value="<?php echo htmlspecialchars($tarefa_editar['titulo']); ?>" required>

                    <label for="descricao_edit">Descrição (Opcional):</label>
                    <textarea id="descricao_edit" name="descricao"><?php echo htmlspecialchars($tarefa_editar['descricao']); ?></textarea>

                    <label for="categoria_id_edit">Categoria:</label>
                    <select id="categoria_id_edit" name="categoria_id">
                        <option value="">Nenhuma</option>
                        <?php foreach($categorias_edicao as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $tarefa_editar['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label style="display: flex; align-items: center;">
                        <input type="checkbox" name="concluida" value="1" <?php echo $tarefa_editar['concluida'] ? 'checked' : ''; ?> style="margin-right: 10px; width: auto;">
                        Tarefa Concluída
                    </label>

                    <button type="submit" name="editar" class="btn-submit" style="margin-top: 15px;">Salvar Edição</button>
                    <a href="admin.php" class="btn-submit" style="background-color: #555;">Cancelar</a>
                </form>
            </div>
        <?php endif; ?>

        <h2>Gerenciar Todas as Tarefas</h2>
        <a href="../index.php" class="btn-admin" style="background-color: #555; margin-bottom: 20px;">Voltar para a Lista</a>

        <table class="tabela-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Categoria</th>
                    <th>Concluída</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result_tarefas->num_rows > 0): 
                    while($tarefa = $result_tarefas->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $tarefa['id']; ?></td>
                        <td><?php echo htmlspecialchars($tarefa['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($tarefa['categoria_nome'] ?? 'N/A'); ?></td>
                        <td><?php echo $tarefa['concluida'] ? 'Sim' : 'Não'; ?></td>
                        <td>
                            <a href="admin.php?editar_id=<?php echo $tarefa['id']; ?>"><button class="btn-editar">Editar</button></a>
                            <a href="admin.php?excluir_id=<?php echo $tarefa['id']; ?>" onclick="return confirm('ATENÇÃO: Deseja realmente excluir esta tarefa?');"><button class="btn-excluir">Excluir</button></a>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                    <tr><td colspan="5">Nenhuma tarefa encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
<?php
$conn->close();
?>