<?php
// Configurações do banco de dados
$host = 'localhost'; // ou o endereço do servidor do banco de dados
$dbname = 'lqdidb';
$user = 'root';
$pass = '';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    // Conexão com o banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, $options);

    // Preparar consulta SQL
    $sql = "SELECT name, email FROM subscriptions"; // Nome da tabela e colunas atualizados
    $stmt = $pdo->prepare($sql);

    // Executar a consulta
    $stmt->execute();

    // Recuperar os resultados
    $results = $stmt->fetchAll();

    // Codificar os resultados em JSON e exibir
    header('Content-Type: application/json');
    echo json_encode($results, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Tratar erro
    echo "Erro de conexão: " . $e->getMessage();
}
