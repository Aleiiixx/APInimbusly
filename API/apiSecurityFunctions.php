<?php

// Esta funcion es llamada siempre, verifica que la KEY de la API ha sido recibida correctamente y que es la correcta
// tanto si la key es correcta como si no lo es, gurdara en un LOG quien ha realizado la accion y que accion ha realizado asi como el dia y hora
// [param1] : url recibida
function authenticate($infoUrl) {
    global $env;

    if (isset($infoUrl['apiKey'])) {

        $apiKeys = explode(',', $env['DB_API_KEY']);

        if (in_array($infoUrl['apiKey'], $apiKeys)) {
            $currentDateTime = date('Y-m-d H:i:s');
            $logMessage = $currentDateTime . "; " . $infoUrl['apiKey'] . "; " . $_SERVER['REQUEST_URI'] . PHP_EOL;
            file_put_contents('apiLogs', $logMessage, FILE_APPEND);
            return true;
        } else {
            $currentDateTime = date('Y-m-d H:i:s');
            $logMessage = $currentDateTime . "; " . $infoUrl['apiKey'] . "; " . $_SERVER['REQUEST_URI'] . PHP_EOL;
            file_put_contents('failedApiLogs', $logMessage, FILE_APPEND);
        }

    } else {
        return false;
    }
}

// 
// Funcion encargada de hashear la contraseña, es utilizado tanto para guardar por primera vez la contraseña como para comprovar la contraseña en el login
// [param1] : contraseña
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Funcion encargada de consultar el hash de la contraseña en la base de datos mediante el id recibido
// [param1] : dni del cliente o en caso de ser para consultar empleados se pasara como parametro el nombreUsuario del mismo
// [param2] : puede ser ¨cliente¨ o ¨empleado¨, sirve para determinar en que tabla se trabajara
// [param3] : objeto DBconn
function getPasswordHashFromId($id, $type, $db) {

    if ($type == "cliente"){
        $sql = "SELECT password FROM Usuario_Cliente WHERE fk_id_cliente = (SELECT id_cliente FROM Cliente WHERE dni = :dni)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':dni', $id, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['password'];
        } else {
            return null;
        }
    } else if ($type == "empleado"){
        $sql = "SELECT password FROM Usuario_Empleado WHERE nombreUsuario = :nombreUsuario";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombreUsuario', $id, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['password'];
        } else {
            return null;
        }
    }
}

// Funcion que comprueba que el hash de la contraseña coincide con el hash de la contraseña guardada para el cliente o empleado con dni/nombreUsuario X
// [param1] : dni del cliente o en caso de ser para consultar empleados se pasara como parametro el nombreUsuario del mismo
// [param2] : contraseña a comprovar
// [param3] : puede ser ¨cliente¨ o ¨empleado¨, sirve para determinar en que tabla se trabajara
// [param4] : objeto DBconn
function checkPassword($id, $password, $type, $db) {
    $storedPasswordHash = getPasswordHashFromId($id, $type, $db);
    if ($storedPasswordHash) {
        if (password_verify($password, $storedPasswordHash)) {
            return json_encode(['result' => true]);
        } else {
            return json_encode(['result' => false]);
        }
    } else {
        return json_encode(['error' => 'No se encontro el DNI en la base de datos']);
    }
}
