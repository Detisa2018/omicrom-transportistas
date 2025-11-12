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
include_once ('DepartamentosVO.php');
include_once ('FunctionsDAO.php');

class DepartamentosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "departamentos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \DepartamentosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "nombre, "
                . "descripcion, "
                . "id_superior, "
                . "id_responsable, "
                . "ubicacion, "
                . "estatus"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssiisi",
                    $objectVO->getNombre(),
                    $objectVO->getDescripcion(),
                    $objectVO->getId_superior(),
                    $objectVO->getId_responsable(),
                    $objectVO->getUbicacion(),
                    $objectVO->getEstatus()
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
     * @return \DepartamentosVO
     */
    public function fillObject($rs) {
        $objectVO = new DepartamentosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setNombre($rs["nombre"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setId_superior($rs["id_superior"]);
            $objectVO->setId_responsable($rs["id_responsable"]);
            $objectVO->setUbicacion($rs["ubicacion"]);
            $objectVO->setEstatus($rs["estatus"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \DepartamentosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new DepartamentosVO();
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
     * @return array Arreglo de objetos \DepartamentosVO
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
     * @param \DepartamentosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = DepartamentosVO) {

        $sql = "UPDATE " . self::TABLA . " SET "
                . "nombre = ?, "
                . "descripcion = ?, "
                . "id_superior = ?, "
                . "id_responsable = ?, "
                . "ubicacion = ?, "
                . "estatus = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssiisii",
                    $objectVO->getNombre(),
                    $objectVO->getDescripcion(),
                    $objectVO->getId_superior(),
                    $objectVO->getId_responsable(),
                    $objectVO->getUbicacion(),
                    $objectVO->getEstatus(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                error_log(print_r($ps, true));
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}
