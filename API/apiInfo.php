<?php

require "DBconn.php";
require "env.php";
require_once "apiSecurityFunctions.php";
require_once "apiClientesFunctions.php";
require_once "apiEmpleadosFunctions.php";

class apiInfo {
    private $db;

    public function __construct($infoUrl){
        $this->db = (new DB())->connect();
        $this->mainRequestFunction($infoUrl);
    }

    private function mainRequestFunction($infoUrl) {
        
        if (!authenticate($infoUrl)) {
            echo json_encode(['error' => 'Autenticación fallida']);
            return;
        }

        if (isset($infoUrl['action'])) {
            switch ($infoUrl['action']) {
                case 'executeScriptCreateClient':
                    echo getClients($this->db);
                    break;
                case 'getClients':
                    echo getClients($this->db);
                    break;
                case 'updateClientPassword':
                    if (isset($infoUrl['dni']) && isset($infoUrl['password'])) {
                        echo updateClientHashedPassword($infoUrl['dni'], $infoUrl['password'], $this->db);
                    } else {
                        echo json_encode(['error' => 'Faltan parametros para actualizar la contraseña del cliente']);
                    }
                    break;
                case 'deleteClient':
                    if (isset($infoUrl['dni'])) {
                        echo deleteClient($infoUrl['dni'], $this->db);
                    } else {
                        echo json_encode(['error' => 'Faltan parametros para borrar al cliente']);
                    }
                    break;
                case 'getFacturasCliente':
                    if (isset($infoUrl['dni'])) {
                        echo getInvoicesByDni($infoUrl['dni'], $db);
                    } else {
                        echo json_encode(['error' => 'Falta el parametro DNI para obtener las facturas del cliente']);
                    }
                    break;
                case 'checkPassword':
                    if (isset($infoUrl['id']) && isset($infoUrl['password'])) {
                        echo checkPassword($infoUrl['id'], $infoUrl['password'], $infoUrl['type'], $this->db);
                    } else {
                        echo json_encode(['error' => 'Faltan parametros para comprobar la contraseña del cliente']);
                    }
                    break;
                case 'getInfoEmpleadoAsignado':
                    if (isset($infoUrl['username'])) {
                        echo getAssignedEmployeeInfo($infoUrl['username'], $this->db);
                    } else {
                        echo json_encode(['error' => 'Falta el parametro nombre de usuario']);
                    }
                    break;
                case 'getEmpleados':
                    echo getEmpleados($this->db);
                    break;
                case 'updateEmpleadoPassword':
                    if (isset($infoUrl['nombreUsuario']) && isset($infoUrl['password'])) {
                        echo updateEmployeeHashedPassword($infoUrl['nombreUsuario'], $infoUrl['password'], $this->db);
                    } else {
                        echo json_encode(['error' => 'Faltan parametros para actualizar la contraseña del empleado']);
                    }
                    break;
                case 'createEmpleado':
                    if (isset($infoUrl['email']) && isset($infoUrl['movil']) && isset($infoUrl['nombre']) && isset($infoUrl['apellido1'])) {
                        echo createNewEmployee($infoUrl['email'], $infoUrl['movil'], $infoUrl['nombre'], $infoUrl['apellido1'], $infoUrl['apellido2'], $this->db);
                    } else {
                        echo json_encode(['error' => 'Faltan parametros para crear el empleado']);
                    }
                    break;
                case 'deleteEmpleado':
                    if (isset($infoUrl['id'])) {
                        echo deleteEmployee($infoUrl['id'], $this->db);
                    } else {
                        echo json_encode(['error' => 'Faltan parametros para borrar al empleado']);
                    }
                    break;
                default:
                    echo json_encode(['error' => 'Revise el parametro introducido como action, puede que no exista o este escrito incorrectamente']);
            }
        } else {
            echo json_encode(['error' => 'No se proporciono ninguna accion, asegurese de añadir el parametro action en la url']);
        }
    }
    
}
