<?php

/**
 * Description of PuestosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since feb 2025
 */
include_once ('mysqlUtils.php');
include_once ('PercepcionesVO.php');
include_once ('FunctionsDAO.php');

class PercepcionesDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "percepciones";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PercepcionesVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "empleado_id, "
                . "tipo_percepcion_id, "
                . "monto, "
                . "fecha, "
                . "observaciones"
                . ") "
                . "VALUES(?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("isdss",
                    $objectVO->getEmpleado_id(),
                    $objectVO->getTipo_percepcion_id(),
                    $objectVO->getMonto(),
                    $objectVO->getFecha(),
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
     * @return \PercepcionesVO
     */
    public function fillObject($rs) {
        $objectVO = new PercepcionesVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setEmpleado_id($rs["empleado_id"]);
            $objectVO->setTipo_percepcion_id($rs["tipo_percepcion_id"]);
            $objectVO->setMonto($rs["monto"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setObservaciones($rs["observaciones"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \PercepcionesVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PercepcionesVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = " . $idObjectVO;
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
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PercepcionesVO
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
     * @param \PercepcionesVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PercepcionesVO) {

        $sql = "UPDATE " . self::TABLA . " SET "
                . "empleado_id = ?, "
                . "tipo_percepcion_id = ?, "
                . "monto = ?, "
                . "fecha = ?, "
                . "observaciones = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("isdssi",
                    $objectVO->getEmpleado_id(),
                    $objectVO->getTipo_percepcion_id(),
                    $objectVO->getMonto(),
                    $objectVO->getFecha(),
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
