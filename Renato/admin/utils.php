<?php

function handle_upload($file_key, $upload_dir, $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov']) {
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erro no upload do arquivo.'];
    }

    $file = $_FILES[$file_key];
    $file_name = $file['name'];
    $file_tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    $file_type = $file['type'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // 1. Validação de Extensão
    if (!in_array($file_ext, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Extensão de arquivo não permitida.'];
    }

    // 2. Criação do nome único
    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
    $destination = __DIR__ . '/../' . $upload_dir . '/' . $new_file_name;
    $relative_path = $upload_dir . '/' . $new_file_name;

    // 3. Criação do diretório se não existir
    $full_upload_dir = __DIR__ . '/../' . $upload_dir;
    if (!is_dir($full_upload_dir)) {
        mkdir($full_upload_dir, 0777, true);
    }

    // 4. Movimentação do arquivo
    if (move_uploaded_file($file_tmp_name, $destination)) {
        return [
            'success' => true,
            'message' => 'Upload realizado com sucesso.',
            'path' => $relative_path,
            'type' => $file_type,
            'ext' => $file_ext
        ];
    } else {
        return ['success' => false, 'message' => 'Falha ao mover o arquivo para o destino.'];
    }
}

// Função para verificar se o usuário está logado (será implementada na fase 5)
function is_logged_in() {
    session_start();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true; 
}

// Função para redirecionar
function redirect($url) {
    header("Location: $url");
    exit();
}

// Função para sanitizar
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
