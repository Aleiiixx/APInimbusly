<?php
require_once "apiSecurityFunctions.php";

// Funcion encargada de devolver un listado de todos los clientes de la base de datos
// [param1] : objeto DBconn
function getClients($db) {
    $sql = "SELECT * FROM Cliente";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($clients, JSON_PRETTY_PRINT);
}

// Funcion encargada de añadir la contraseña hasheada a la base de datos del cliente especificado
// [param1] : dni del cliente al que queremos modificar la contraseña
// [param2] : contraseña que se quiere añadir para el cliente
// [param3] : objeto DBconn
function updateClientHashedPassword($dni, $password, $db) {
    $passwordHash = generatePasswordHash($password);
    $sql = "UPDATE Usuario_Cliente SET password = :passwordHash WHERE fk_id_cliente = (SELECT id_cliente FROM Cliente WHERE dni = :dni)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':passwordHash', $passwordHash, PDO::PARAM_STR);
    $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
    
    try {
        $stmt->execute();
        return json_encode(['success' => 'Contraseña actualizada']);
    } catch (PDOException $e) {
        return json_encode(['error' => 'Error al actualizar la contraseña: ' . $e->getMessage()]);
    }
}


// Funcion encargada de retornar la informacion del empleado asignado al Cliente especificado
// [param1] : nombreUsuario del cliente en cuestion
// [param2] : objeto DBconn
function getAssignedEmployeeInfo($username, $db) {
    $sql = "SELECT e.nombre, e.email
            FROM Empleado e
            JOIN Usuario_Cliente uc ON e.id_empleado = uc.fk_id_empleado
            WHERE uc.nombreUsuario = :username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $employeeInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employeeInfo) {
        return json_encode($employeeInfo);
    } else {
        return json_encode(['error' => 'No se encontro información de empleado para el nombre de usuario proporcionado']);
    }
}

// Funcion para borrar clientes de la base de datos
// [param1] : dni del cliente a borrar
// [param2] : objeto DBconn
function deleteClient($dni_cliente, $db) {

    $sql = "DELETE FROM Cliente WHERE dni = :dni_cliente";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':dni_cliente', $dni_cliente, PDO::PARAM_STR);
    try {
    
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        if ($rowCount > 0) {
            return json_encode(['success' => 'Cliente eliminado con exito']);
        } else {
            return json_encode(['error' => 'No se encontró un cliente con el DNI proporcionado']);
        }
    } catch (PDOException $e) {
        return json_encode(['error' => 'Error al eliminar el cliente: ' . $e->getMessage()]);
    }
}

// Funcion para recuperar las facturas del cliente especificado
// [param1] : dni del cliente consultar
// [param2] : objeto DBconn
function getInvoicesByDni($dni, $db) {

    $sql = "SELECT f.*
            FROM Factura f
            JOIN Cliente c ON f.fk_id_cliente = c.id_cliente
            WHERE c.dni = :dni";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
    try {
        $stmt->execute();

        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($invoices, JSON_PRETTY_PRINT);
    } catch (PDOException $e) {
        return json_encode(['error' => 'Error al obtener las facturas del cliente: ' . $e->getMessage()]);
    }
}


function executeScriptCreateClient($username, $password, $name){
    exec("./createUsersNextcloud.sh ".$username. " " . $password);
}

