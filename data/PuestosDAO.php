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
include_once ('PuestosVO.php');
include_once ('FunctionsDAO.php');

class PuestosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "puestos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PuestosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "puesto,"
                . "descripcion,"
                . "id_departamento,"
                . "sueldo_base,"
                . "nivel_salarial,"
                . "tipo_contrato,"
                . "horario_laboral_entrada,"
                . "horario_laboral_salida,"
                . "estatus"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssidsssss",
                    $objectVO->getPuesto(),
                    $objectVO->getDescripcion(),
                    $objectVO->getId_departamento(),
                    $objectVO->getSueldo_base(),
                    $objectVO->getNivel_salarial(),
                    $objectVO->getTipo_contrato(),
                    $objectVO->getHorario_laboral_entrada(),
                    $objectVO->getHorario_laboral_salida(),
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
     * @return \PuestosVO
     */
    public function fillObject($rs) {
        $objectVO = new PuestosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setPuesto($rs["puesto"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setId_departamento($rs["id_departamento"]);
            $objectVO->setSueldo_base($rs["sueldo_base"]);
            $objectVO->setNivel_salarial($rs["nivel_salarial"]);
            $objectVO->setTipo_contrato($rs["tipo_contrato"]);
            $objectVO->setHorario_laboral_entrada($rs["horario_laboral_entrada"]);
            $objectVO->setHorario_laboral_salida($rs["horario_laboral_salida"]);
            $objectVO->setEstatus($rs["estatus"]);
            $objectVO->setFecha_creacion($rs["fecha_creacion"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \PuestosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PuestosVO();
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
     * @param \PuestosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PromosVO) {

        $sql = "UPDATE " . self::TABLA . " SET "
                . "puesto = ?, "
                . "descripcion = ?, "
                . "id_departamento = ?, "
                . "sueldo_base = ?, "
                . "nivel_salarial = ?, "
                . "tipo_contrato = ?, "
                . "horario_laboral_entrada = ?, "
                . "horario_laboral_salida = ?, "
                . "estatus = ?, "
                . "fecha_creacion = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssidssssssi",
                    $objectVO->getPuesto(),
                    $objectVO->getDescripcion(),
                    $objectVO->getId_departamento(),
                    $objectVO->getSueldo_base(),
                    $objectVO->getNivel_salarial(),
                    $objectVO->getTipo_contrato(),
                    $objectVO->getHorario_laboral_entrada(),
                    $objectVO->getHorario_laboral_salida(),
                    $objectVO->getEstatus(),
                    $objectVO->getFecha_creacion(),
                    $objectVO->getId()
            );
            error_log("UDPATEEEE " . $objectVO->getId_departamento());
            if ($ps->execute()) {
                error_log(print_r($ps, true));
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}
