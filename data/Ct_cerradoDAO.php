<?php

/**
 * Description of Ct_cerradoDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since ene 2025
 */
include_once ('mysqlUtils.php');
include_once ('Ct_cerradoVO.php');
include_once ('FunctionsDAO.php');

class Ct_cerradoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "ct_cerrado";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \Ct_cerradoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id_ct,"
                . "id_usr, "
                . "fecha, "
                . "total, "
                . "credito, "
                . "bancos, "
                . "consignaciones, "
                . "monederos, "
                . "aceites, "
                . "dolares, "
                . "gastos, "
                . "efectivo, "
                . "deposito"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iisdddddddddd",
                    $objectVO->getId_ct(),
                    $objectVO->getId_usr(),
                    $objectVO->getFecha(),
                    $objectVO->getTotal(),
                    $objectVO->getCredito(),
                    $objectVO->getBancos(),
                    $objectVO->getConsignaciones(),
                    $objectVO->getMonederos(),
                    $objectVO->getAceites(),
                    $objectVO->getDolares(),
                    $objectVO->getGastos(),
                    $objectVO->getEfectivo(),
                    $objectVO->getDepositos()
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
     * @return \Ct_cerradoVO
     */
    public function fillObject($rs) {
        $objectVO = new Ct_cerradoVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setId_ct($rs["id_ct"]);
            $objectVO->setId_usr($rs["id_usr"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setTotal($rs["total"]);
            $objectVO->setCredito($rs["credito"]);
            $objectVO->setBancos($rs["bancos"]);
            $objectVO->setConsignaciones($rs["consignaciones"]);
            $objectVO->setMonederos($rs["monederos"]);
            $objectVO->setAceites($rs["aceites"]);
            $objectVO->setDolares($rs["dolares"]);
            $objectVO->setGastos($rs["gastos"]);
            $objectVO->setEfectivo($rs["efectivo"]);
            $objectVO->setDepositos($rs["desposito"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \Ct_cerradoVO
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
     * @param string $field Nombre del campo a buscar
     * @return \Ct_cerradoVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new Ct_cerradoVO();
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
     * @param \Ct_cerradoVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        return false;
    }

}
