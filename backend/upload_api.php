<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Diretório de uploads
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Configuração do banco
$dbHost = "localhost";
$dbUser = "root";   // ajuste conforme necessário
$dbPass = "root";       // ajuste conforme necessário
$dbName = "imageviwer";

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Erro ao conectar no banco"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "error" => "Erro no upload"]);
        exit;
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        echo json_encode(["success" => false, "error" => "Arquivo maior que 5MB"]);
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp'
    ];

    if (!array_key_exists($mime, $allowed)) {
        echo json_encode(["success" => false, "error" => "Tipo não permitido"]);
        exit;
    }

    $ext = $allowed[$mime];
    $uniqueName = bin2hex(random_bytes(8)) . "." . $ext;
    $dest = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $url = "uploads/" . $uniqueName;

        $stmt = $conn->prepare("
            INSERT INTO uploads (original_name, saved_name, file_type, file_size, url, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("sssds", $file['name'], $uniqueName, $mime, $file['size'], $url);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "url" => $url,
            "id" => $stmt->insert_id
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Falha ao mover o arquivo"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Nenhum arquivo enviado"]);
}
