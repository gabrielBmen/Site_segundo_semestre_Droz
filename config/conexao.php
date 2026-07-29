<?php
$host = "192.168.56.102";
$user = "gabrielBM";
$password = "Rafa2020"; // Variável corrigida
$database = "droz_robotica"; // Variável corrigida

try {
    // 1. Conexão usando as variáveis com os nomes corretos
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Consulta SQL com INNER JOIN para buscar o "nome" da categoria na tabela "categorias"
    $sql = "SELECT p.*, c.nome AS categoria 
            FROM produtos p 
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria";
    
    $stmt = $pdo->query($sql);
    
    // 3. Salva os resultados no array que a sua página usa
    $catalogoProdutos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Pega os produtos para o destaque (ex: os 3 primeiros)
    $produtosDestaque = $catalogoProdutos;

    
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>