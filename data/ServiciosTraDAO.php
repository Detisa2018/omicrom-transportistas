<?php

/**
 * Description of ServiciosTraDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since nov 2025
 */
include_once ('mysqlUtils.php');
include_once ('ServiciosTraVO.php');
include_once ('FunctionsDAO.php');

class ServiciosTraDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "servicios_tra";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ServiciosTraVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "sucursal,"
                . "folio,"
                . "nombre, "
                . "clave_unidad, "
                . "clave_producto, "
                . "precio,"
                . "identificador"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iisssds",
                    $objectVO->getSucursal(),
                    $objectVO->getFolio(),
                    $objectVO->getNombre(),
                    $objectVO->getClave_unidad(),
                    $objectVO->getClave_producto(),
                    $objectVO->getPrecio(),
                    $objectVO->getIdentificador()
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
     * @return \ServiciosTraVO
     */
    public function fillObject($rs) {
        $objectVO = new ServiciosTraVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setSucursal($rs["sucursal"]);
            $objectVO->setFolio($rs["folio"]);
            $objectVO->setNombre($rs["nombre"]);
            $objectVO->setClave_unidad($rs["clave_unidad"]);
            $objectVO->setClave_producto($rs["clave_producto"]);
            $objectVO->setPrecio($rs["precio"]);
            $objectVO->setIdentificador($rs["identificador"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \ServiciosTraVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new ServiciosTraVO();
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
     * @return array Arreglo de objetos \PuestosVO
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
     * @param \ServiciosTraVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PromosVO) {

        $sql = "UPDATE " . self::TABLA . " SET "
                . "nombre = ?, "
                . "clave_unidad = ?, "
                . "clave_producto = ?, "
                . "precio = ?, "
                . "identificador = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssdsi",
                    $objectVO->getNombre(),
                    $objectVO->getClave_unidad(),
                    $objectVO->getClave_producto(),
                    $objectVO->getPrecio(),
                    $objectVO->getIdentificador(),
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
