<?php

/**
 * Description of Empleados_nom_DAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since ene 2025
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('Empleados_nomVO.php');

class Empleados_nomDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "empleados_nom";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \Empleados_nomVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = Empleados_nomVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "rfc, "
                . "curp, "
                . "nombre, "
                . "imss, "
                . "cuenta_bancaria, "
                . "fecha_ingreso, "
                . "tipo_nomina, "
                . "id_departamento, "
                . "no_credencial, "
                . "status, "
                . "sueldo_diario, "
                . "sueldo_integrado, "
                . "baja,"
                . "observaciones"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssissddss",
                    $objectVO->getRfc(),
                    $objectVO->getCurp(),
                    $objectVO->getNombre(),
                    $objectVO->getImss(),
                    $objectVO->getCuenta_bancaria(),
                    $objectVO->getTipo_nomina(),
                    $objectVO->getId_departamento(),
                    $objectVO->getNo_credencial(),
                    $objectVO->getStatus(),
                    $objectVO->getSueldo_diario(),
                    $objectVO->getSueldo_integrado(),
                    $objectVO->getBaja(),
                    $objectVO->getObservaciones()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log($this->conn->error);
            }
            $ps->close();
        } else {
            error_log($this->conn->error);
        }
        return $id;
    }

    /**
     * 
     * @param array() $rs
     * @return \Empleados_nomVO
     */
    public function fillObject($rs) {
        $objectVO = new Empleados_nomVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setRfc($rs["rfc"]);
            $objectVO->setCurp($rs["curp"]);
            $objectVO->setNombre($rs["nombre"]);
            $objectVO->setImss($rs["imss"]);
            $objectVO->setCuenta_bancaria($rs["cuenta_bancaria"]);
            $objectVO->setFecha_ingreso($rs["fecha_ingreso"]);
            $objectVO->setTipo_nomina($rs["tipo_nomina"]);
            $objectVO->setId_departamento($rs["id_departamento"]);
            $objectVO->setNo_credencial($rs["no_credencial"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setSueldo_diario($rs["sueldo_diario"]);
            $objectVO->setSueldo_integrado($rs["sueldo_integrado"]);
            $objectVO->setBaja($rs["baja"]);
            $objectVO->setObservaciones($rs["observaciones"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \Empleados_nomVO
     */
    public function getAll($sql) {
        $array = array();
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $objectVO = $this->fillObject($rs);
                array_push($array, $objectVO);
            }
        } else {
            error_log($this->conn->error);
        }
        return $array;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function remove($idObjectVO, $field = "id") {
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \Empleados_nomVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new Empleados_nomVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
        //error_log($sql);
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \Empleados_nomVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = Empleados_nomVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "rfc = ?, "
                . "curp = ?, "
                . "nombre = ?, "
                . "imss = ?, "
                . "cuenta_bancaria = ?, "
                . "fecha_ingreso = ?, "
                . "tipo_nomina = ?, "
                . "id_departamento = ?, "
                . "no_credencial = ?, "
                . "status = ?, "
                . "sueldo_diario = ?, "
                . "sueldo_integrado = ?, "
                . "baja = ?, "
                . "observaciones = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssissddssi",
                    $objectVO->getRfc(),
                    $objectVO->getCurp(),
                    $objectVO->getNombre(),
                    $objectVO->getImss(),
                    $objectVO->getCuenta_bancaria(),
                    $objectVO->getFecha_ingreso(),
                    $objectVO->getTipo_nomina(),
                    $objectVO->getId_departamento(),
                    $objectVO->getNo_credencial(),
                    $objectVO->getStatus(),
                    $objectVO->getSueldo_diario(),
                    $objectVO->getSueldo_integrado(),
                    $objectVO->getBaja(),
                    $objectVO->getObservaciones(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}
