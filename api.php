<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'], 
        $_ENV['DB_USER'], 
        $_ENV['DB_PASS'], 
        $options
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao conectar ao banco de dados.']);
    exit;
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT name, email FROM subscriptions";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($results);
        break;

    case 'POST':
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos fornecidos.']);
            break;
        }

        // Verifique se o email já existe
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email já existe
            http_response_code(409); // Código de conflito
            echo json_encode(['error' => 'Email já cadastrado.']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO subscriptions (name, email) VALUES (:name, :email)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $lastId = $pdo->lastInsertId();
        
            $mail = new PHPMailer(true);
        
            try {
                // Configurações do servidor
                $mail->isSMTP();
                $mail->CharSet = 'UTF-8';
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'lucasdeandrade0077@gmail.com'; // Seu endereço de e-mail do Gmail
                $mail->Password = 'lvyx fjvl agah akyw'; // Sua senha de aplicativo gerada
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
            
                // Remetentes e destinatários
                $mail->setFrom('lucasdeandrade0077@gmail.com', 'mcbfk');
                $mail->addAddress($email); // Adiciona o destinatário usando o e-mail fornecido no formulário
            
                // Conteúdo do e-mail
                $mail->isHTML(true); // Defina o formato do e-mail para HTML
                $mail->Subject = 'Confirmação de Inscrição';
                $mail->Body = 'Hello, ' . $name . '. Seu ID de inscrição é ' . $lastId;
                $mail->AltBody = 'Texto alternativo para clientes de e-mail que não aceitam HTML';
            
                $mail->send();
                echo json_encode(['id' => $lastId, 'message' => 'Inscrição e e-mail enviados com sucesso.']);
            } catch (Exception $e) {
                echo json_encode(['id' => $lastId, 'error' => 'Inscrição foi bem-sucedida, mas o e-mail não pôde ser enviado. Mailer Error: ' . $mail->ErrorInfo]);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao inserir inscrição: ' . $e->getMessage()]);
        }

        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Método HTTP não suportado']);
}
