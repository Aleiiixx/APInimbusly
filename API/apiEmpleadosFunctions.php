<?php

require_once "apiSecurityFunctions.php";

// Funcion encargada de devolver un listado de todos los empleados de la base de datos
// [param1] : objeto DBconn
function getEmpleados($db) {
    $sql = "SELECT * FROM Empleado";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($empleados, JSON_PRETTY_PRINT);
}

// Funcion para actualizar la password de un empleado
// [param1] : nombre de usuario del empleado
// [param2] : nueva contraseña del empleado (sin hashear, ya se encarga la api de acerlo)
// [param3] : objeto DBconn
function updateEmployeeHashedPassword($nombreUsuario, $password, $db) {
    $passwordHash = generatePasswordHash($password);
    $sql = "UPDATE Usuario_Empleado SET password = :passwordHash WHERE nombreUsuario = :nombreUsuario";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':passwordHash', $passwordHash, PDO::PARAM_STR);
    $stmt->bindParam(':nombreUsuario', $nombreUsuario, PDO::PARAM_STR);
    
    try {
        $stmt->execute();
        return json_encode(['success' => 'Contraseña actualizada']);
    } catch (PDOException $e) {
        return json_encode(['error' => 'Error al actualizar la contraseña: ' . $e->getMessage()]);
    }
}

// Funcion encargada de crear un nuevo empleado
// [param1] : email del empleado
// [param2] : telefono movil del empleado
// [param3] : nombre del empleado
// [param4] : primer apellido del empleado
// [param5] : segundo apellido del empleado
// [param6] : objeto DBconn
function createNewEmployee($email, $movil, $nombre, $apellido1, $apellido2, $db) {

    $sql = "SELECT COUNT(*) AS count FROM Empleado WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        return json_encode(['error' => 'El correo electrónico ya esta en uso']);
    }

    $sql = "INSERT INTO Empleado (email, movil, nombre, apellido1, apellido2) VALUES (:email, :movil, :nombre, :apellido1, :apellido2)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':movil', $movil, PDO::PARAM_STR);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':apellido1', $apellido1, PDO::PARAM_STR);
    $stmt->bindParam(':apellido2', $apellido2, PDO::PARAM_STR);

    try {
        $stmt->execute();
        return json_encode(['success' => 'Empleado creado con exito']);
    } catch (PDOException $e) {
        return json_encode(['error' => 'Error al crear el empleado: ' . $e->getMessage()]);
    }
}

// Funcion para borrar empleados de la base de datos
// [param1] : id del empleado a borrar
// [param2] : objeto DBconn
function deleteEmployee($id_empleado, $db) {

    $sql = "DELETE FROM Empleado WHERE id_empleado = :id_empleado";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);

    try {
    
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        if ($rowCount > 0) {
            return json_encode(['success' => 'Empleado eliminado con exito']);
        } else {
            return json_encode(['error' => 'No se encontró un empleado con el ID proporcionado']);
        }
    } catch (PDOException $e) {
        return json_encode(['error' => 'Error al eliminar el empleado: ' . $e->getMessage()]);
    }
}



