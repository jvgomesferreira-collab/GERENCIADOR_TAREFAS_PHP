<?php
// index.php
include 'includes/conexao.php'; // Conexão com o BD 

// Lógica para Adicionar Nova Tarefa (Create)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar'])) {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $categoria_id = (int)$_POST['categoria_id'];

    if (!empty($titulo)) {
        $sql = "INSERT INTO tarefas (titulo, descricao, categoria_id) VALUES ('$titulo', '$descricao', $categoria_id)";
        
        if ($conn->query($sql) === TRUE) {
            // Sucesso - redireciona para evitar reenvio do form
            header("Location: index.php");
            exit();
        } else {
            $erro = "Erro ao adicionar tarefa: " . $conn->error;
        }
    } else {
        $erro = "O título da tarefa não pode ser vazio.";
    }
}

// Lógica para Marcar/Desmarcar como Concluída (Update Simples)
if (isset($_GET['concluir_id'])) {
    $id = (int)$_GET['concluir_id'];
    $sql_select = "SELECT concluida FROM tarefas WHERE id = $id";
    $result_select = $conn->query($sql_select);
    if ($result_select->num_rows > 0) {
        $row = $result_select->fetch_assoc();
        $nova_situacao = $row['concluida'] ? 0 : 1; // Inverte o estado
        $sql_update = "UPDATE tarefas SET concluida = $nova_situacao WHERE id = $id";
        $conn->query($sql_update);
        header("Location: index.php");
        exit();
    }
}

// Consulta para Listar Tarefas (Read)
$sql_tarefas = "SELECT t.id, t.titulo, t.descricao, t.concluida, c.nome as categoria_nome 
                FROM tarefas t 
                LEFT JOIN categorias c ON t.categoria_id = c.id 
                ORDER BY t.concluida ASC, t.data_criacao DESC";
$result_tarefas = $conn->query($sql_tarefas);

// Consulta para Categorias (para o formulário)
$sql_categorias = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$result_categorias = $conn->query($sql_categorias);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Tarefas</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>📋 Gerenciador de Tarefas</h1>
    </header>

    <main>
        <a href="admin/admin.php" class="btn-admin">Painel Administrativo</a>

        <div class="form-adicionar">
            <h2>Adicionar Nova Tarefa</h2>
            <?php if (isset($erro)) echo "<p style='color: red;'>$erro</p>"; ?>
            <form action="index.php" method="POST" onsubmit="return validarFormulario();">
                <label for="titulo">Título da Tarefa:</label>
                <input type="text" id="titulo" name="titulo" required>

                <label for="descricao">Descrição (Opcional):</label>
                <textarea id="descricao" name="descricao"></textarea>
                
                <label for="categoria_id">Categoria:</label>
                <select id="categoria_id" name="categoria_id">
                    <option value="">Nenhuma</option>
                    <?php while($cat = $result_categorias->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" name="adicionar" class="btn-submit">Adicionar</button>
            </form>
        </div>

        <h2>Minhas Tarefas</h2>
        <ul class="lista-tarefas">
            <?php 
            if ($result_tarefas->num_rows > 0): 
                while($tarefa = $result_tarefas->fetch_assoc()):
            ?>
                <li class="tarefa-item <?php echo $tarefa['concluida'] ? 'tarefa-concluida' : ''; ?>">
                    <div class="tarefa-conteudo">
                        <p class="tarefa-titulo"><?php echo htmlspecialchars($tarefa['titulo']); ?></p>
                        <p class="tarefa-descricao"><?php echo htmlspecialchars($tarefa['descricao']); ?></p>
                        <?php if ($tarefa['categoria_nome']): ?>
                            <small>Categoria: <?php echo htmlspecialchars($tarefa['categoria_nome']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="tarefa-acoes">
                        <a href="index.php?concluir_id=<?php echo $tarefa['id']; ?>">
                            <button class="btn-concluir">
                                <?php echo $tarefa['concluida'] ? 'Desfazer' : 'Concluir'; ?>
                            </button>
                        </a>
                        <a href="admin/admin.php?editar_id=<?php echo $tarefa['id']; ?>"><button class="btn-editar">Editar</button></a>
                        <a href="admin/admin.php?excluir_id=<?php echo $tarefa['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');"><button class="btn-excluir">Excluir</button></a>
                    </div>
                </li>
            <?php 
                endwhile;
            else: 
            ?>
                <p>Nenhuma tarefa cadastrada. Adicione uma nova!</p>
            <?php endif; ?>
        </ul>
    </main>

    <script src="js/script.js"></script>
</body>
</html>
<?php
$conn->close();
?>