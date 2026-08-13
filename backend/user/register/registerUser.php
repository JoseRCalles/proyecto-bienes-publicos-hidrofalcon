<?php
session_start();
require '../../connect/connect.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no válido. Solo se aceptan solicitudes POST.']);
    exit();
}

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetro "action" no proporcionado.']);
    exit();
}

$action = $_POST['action'];

if ($action === 'registerUser') {
    // =============================================
    // Acción: Registrar usuario
    // =============================================
    try {
        $stmt_config = $pdo->query("SELECT valor FROM configuracion WHERE nombre = 'permitir_registro'");
        $config_registro = $stmt_config->fetchColumn();

        if ($config_registro == 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El registro de nuevos usuarios está temporalmente deshabilitado por el administrador.'
            ]);
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error de base de datos al verificar la configuración: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ocurrió un error. Por favor, inténtalo de nuevo.'
        ]);
        exit();
    }

    // Recibir y limpiar los datos del formulario
    $nombre = strtolower(trim($_POST['nombres'] ?? ''));
    $apellido = strtolower(trim($_POST['apellidos'] ?? ''));
    $usuario = strtolower(trim($_POST['usuario'] ?? ''));
    $cedula = trim($_POST['cedula'] ?? '');
    $cargo = strtolower(trim($_POST['cargo'] ?? ''));
    $gerencia = trim($_POST['gerencia'] ?? '');
    $pregunta1 = $_POST['pregunta1'] ?? '';
    $respuesta1 = trim($_POST['respuesta1'] ?? '');
    $pregunta2 = $_POST['pregunta2'] ?? '';
    $respuesta2 = trim($_POST['respuesta2'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validaciones de Contraseña ---
    if (empty($contrasena) || $contrasena !== $confirm_password || strlen($contrasena) < 8 ||
        !preg_match('/[A-Z]/', $contrasena) || !preg_match('/[a-z]/', $contrasena) ||
        !preg_match('/[0-9]/', $contrasena) || !preg_match('/[^a-zA-Z0-9]/', $contrasena)) {
        echo json_encode(['success' => false, 'message' => 'Error en la validación de la contraseña. Por favor, revisa que cumpla con todos los requisitos.']);
        exit();
    }

    // --- Validación de Preguntas y Respuestas de Seguridad ---
    if (empty($pregunta1) || empty($respuesta1) || empty($pregunta2) || empty($respuesta2) || $pregunta1 == $pregunta2) {
        echo json_encode(['success' => false, 'message' => 'Error en la validación de las preguntas de seguridad. Por favor, verifica que las respuestas y preguntas sean correctas y diferentes.']);
        exit();
    }

    // Hashear la contraseña y las respuestas de seguridad
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
    $respuesta1_hash = password_hash($respuesta1, PASSWORD_DEFAULT);
    $respuesta2_hash = password_hash($respuesta2, PASSWORD_DEFAULT);
    $rango_por_defecto = 2; // Rango 2 para nuevos usuarios

    try {
        $pdo->beginTransaction();

        // 1. Verificar si el usuario o cédula ya existen
        $query_check_user = "SELECT id FROM usuarios WHERE usuario = :usuario OR cedula = :cedula";
        $stmt_check_user = $pdo->prepare($query_check_user);
        $stmt_check_user->execute([':usuario' => $usuario, ':cedula' => $cedula]);

        if ($stmt_check_user->fetch()) {
            throw new Exception("El nombre de usuario o la cédula ya están registrados.");
        }

        // 2. Insertar el nuevo usuario
        $query_insert_user = "INSERT INTO usuarios (nombres, apellidos, usuario, cedula, contrasena, cargo, gerencia, rango) VALUES (:nombres, :apellidos, :usuario, :cedula, :contrasena, :cargo, :gerencia, :rango)";
        $stmt_insert_user = $pdo->prepare($query_insert_user);
        $params_user = [
            ':nombres' => $nombre,
            ':apellidos' => $apellido,
            ':usuario' => $usuario,
            ':cedula' => $cedula,
            ':contrasena' => $hashed_password,
            ':cargo' => $cargo,
            ':gerencia' => $gerencia,
            ':rango' => $rango_por_defecto
        ];
        if (!$stmt_insert_user->execute($params_user)) {
            throw new Exception("Error al insertar usuario.");
        }
        $user_id = $pdo->lastInsertId();

        // 3. Insertar las respuestas de seguridad
        $query_insert_respuestas = "INSERT INTO resp_seguridad (id_usuario, id_pregunta, respuesta) VALUES (:id_usuario, :id_pregunta, :respuesta)";
        $stmt_insert_respuestas = $pdo->prepare($query_insert_respuestas);
        $stmt_insert_respuestas->execute([':id_usuario' => $user_id, ':id_pregunta' => $pregunta1, ':respuesta' => $respuesta1_hash]);
        $stmt_insert_respuestas->execute([':id_usuario' => $user_id, ':id_pregunta' => $pregunta2, ':respuesta' => $respuesta2_hash]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Usuario registrado exitosamente."]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error en el registro del usuario (PDOException): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error. Por favor, inténtalo de nuevo.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error en el registro del usuario (Exception): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error. Por favor, inténtalo de nuevo.']);
    }

} elseif ($action === 'checkUserOrCedula') {
    // =============================================
    // Acción: Verificar existencia de usuario o cédula
    // =============================================
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        echo json_encode(['exists' => false, 'error' => 'Error al conectar a la base de datos.'], JSON_PRETTY_PRINT);
        exit();
    }

    function verificarExistencia(PDO $pdo_pdo, string $tabla, string $campo, string $valor): bool {
        $sql = "SELECT $campo FROM $tabla WHERE $campo = :valor";
        $stmt = $pdo_pdo->prepare($sql);
        if ($stmt) {
            $stmt->bindParam(':valor', $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } else {
            error_log("Error al preparar la consulta SQL en verificarExistencia: " . implode(" ", $pdo_pdo->errorInfo()));
            return false;
        }
    }

    if (isset($_POST['usuario'])) {
        $usuario = $_POST['usuario'];
        $existeUsuario = verificarExistencia($pdo, 'usuarios', 'usuario', $usuario);
        echo json_encode(['exists' => $existeUsuario], JSON_PRETTY_PRINT);
    } elseif (isset($_POST['cedula'])) {
        $cedula = $_POST['cedula'];
        $existeCedula = verificarExistencia($pdo, 'usuarios', 'cedula', $cedula);
        echo json_encode(['exists' => $existeCedula], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['exists' => false, 'error' => 'No se proporcionó usuario ni cédula para verificar.'], JSON_PRETTY_PRINT);
    }

} elseif ($action === 'getFormData') {
    // =============================================
    // Acción: Obtener datos para el registro (gerencias, cargos, preguntas)
    // =============================================
    try {
        $stmt_gerencias = $pdo->query("SELECT id, gerencia AS nombre_gerencia FROM gerencia_adm ORDER BY gerencia ASC");
        $gerencias = $stmt_gerencias->fetchAll(PDO::FETCH_ASSOC);

        $stmt_cargos = $pdo->query("SELECT id, cargo AS nombre_cargo FROM cargo ORDER BY cargo ASC");
        $cargos = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);

        $stmt_preguntas = $pdo->query("SELECT id, pregunta FROM preg_seguridad ORDER BY id ASC");
        $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'gerencias' => $gerencias,
            'cargos' => $cargos,
            'preguntas' => $preguntas
        ]);

    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error de base de datos: " . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
}
?>
