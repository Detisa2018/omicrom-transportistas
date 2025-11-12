<?php

/**
 * Description of AuthSemestralResutladosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since Jul 2025
 */
include_once ('mysqlUtils.php');
include_once ('AuthSemestralResultadosVO.php');
include_once ('FunctionsDAO.php');

class AuthSemestralResultadosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "auth_semestral_resultados";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \AuthSemestralResultadosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id_auth_semestral, "
                . "id_authuser, "
                . "status_anterior, "
                . "status_actual ) "
                . "VALUES(?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiss",
                    $objectVO->getId_auth_semestral(),
                    $objectVO->getId_authuser(),
                    $objectVO->getStatus_anterior(),
                    $objectVO->getStatus_actual()
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
     * @return \AuthSemestralResultadosVO
     */
    public function fillObject($rs) {
        $objectVO = new AuthSemestralResultadosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setId_auth_semestral($rs["id_auth_semestral"]);
            $objectVO->setId_authuser($rs["id_authuser"]);
            $objectVO->setStatus_anterior($rs["status_anterior"]);
            $objectVO->setStatus_actual($rs["status_actual"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \AuthSemestralResultadosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new AuthSemestralResultadosVO();
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
     * @return array Arreglo de objetos \BancosVO
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
        
    }

    /**
     * 
     * @param \AuthSemestralResultadosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = AuthSemestralResultadosVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "status_anterior = ?, "
                . "status_actual = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssi",
                    $objectVO->getStatus_anterior(),
                    $objectVO->getStatus_actual(),
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