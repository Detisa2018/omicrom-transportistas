<?php

include_once ('mysqlUtils.php');
include_once ('AuthSemestralVO.php');
include_once ('BasicEnum.php');

include_once ("detisa/IConnection.php");

/**
 * Description of AuthSemestralDAO
 *
 * @author Alejandro Ayala Gonzalez
 */
class AuthSemestralDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "auth_semestral";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param AuthSemestralVO $objectVO = AuthSemestralVO
     * @return AuthSemestralVO
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "fecha, "
                . "descripcion, "
                . "id_authuser, "
                . "status) "
                . "VALUES(?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssis",
                    $objectVO->getFecha(),
                    $objectVO->getDescripcion(),
                    $objectVO->getId_authuser(),
                    $objectVO->getStatus()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
            } else {
                error_log($this->conn->error);
                $ps->close();
            }
        }
        return $id;
    }

    /**
     * 
     * @param array $rs
     * @return \AuthSemestralVO
     */
    public function fillObject($rs) {
        $objectVO = new AuthSemestralVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setId_authuser($rs["id_authuser"]);
            $objectVO->setStatus($rs["status"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @return array List users active
     */
    public function getAll($sql) {
        $array = array();
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $objectVO = $this->fillObject($rs);
                array_push($array, $objectVO);
            }
        }
        return $array;
    }

    /**
     * 
     * @param type $idObjectVO = Id User
     * @return \AuthSemestralVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new AuthSemestralVO();
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " "
                . "WHERE " . self::TABLA . "." . $field . " = " . $idObjectVO;
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            //error_log($objectVO);
            return $objectVO;
        }
        return null;
    }

    /**
     * 
     * @param AuthSemestralVO $objectVO
     * @return boolean
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "fecha = ?, "
                . "descripcion = ?, "
                . "status = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssi",
                    $objectVO->getFecha(),
                    $objectVO->getDescripcion(),
                    $objectVO->getStatus(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                return true;
            }
        }
        return false;
    }

    public function remove($idObjectVO, $field = "id") {
        
    }

}

abstract class StatusAuthSemestral extends BasicEnum {

    const ABIERTA = "Abierta";
    const CERRADA = "Cerrada";
    const CANCELADA = "Cancelada";

}
