<?php

/**
 * Description of EnviosTransportistasDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @since sep 2025
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('EnviosTransportistasVO.php');

class EnviosTransportistasDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "envios_transportistas";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \EnviosTransportistasVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id_venta,"
                . "id_cliente, "
                . "cfdi,"
                . "tipocfdi,"
                . "contra_prestacion,"
                . "tarifa_de_transporte,"
                . "cargo_por_capacidad_trans,"
                . "cargo_por_uso_trans,"
                . "cargo_volumetrico_trans,"
                . "descuento,"
                . "fecha_hora_transaccion,"
                . "volumen_documentado"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iissddddddsd",
                    $objectVO->getIdVenta(),
                    $objectVO->getIdCliente(),
                    $objectVO->getCfdi(),
                    $objectVO->getTipocfdi(),
                    $objectVO->getContraPrestacion(),
                    $objectVO->getTarifaDeTransporte(),
                    $objectVO->getCargoPorCapacidadTrans(),
                    $objectVO->getCargoPorUsoTrans(),
                    $objectVO->getCargoVolumetricoTrans(),
                    $objectVO->getDescuento(),
                    $objectVO->getFechaHoraTransaccion(),
                    $objectVO->getVolumenDocumentado()
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
     * @return \EnviosTransportistasVO
     */
    public function fillObject($rs) {
        $objectVO = new EnviosTransportistasVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIdVenta($rs["id_venta"]);
            $objectVO->setIdCliente($rs["id_cliente"]);
            $objectVO->setCfdi($rs["cfdi"]);
            $objectVO->setTipocfdi($rs["tipocfdi"]);
            $objectVO->setContraPrestacion($rs["contra_prestacion"]);
            $objectVO->setTarifaDeTransporte($rs["tarifa_de_transporte"]);
            $objectVO->setCargoPorCapacidadTrans($rs["cargo_por_capacidad_trans"]);
            $objectVO->setCargoPorUsoTrans($rs["cargo_por_uso_trans"]);
            $objectVO->setCargoVolumetricoTrans($rs["cargo_volumetrico_trans"]);
            $objectVO->setDescuento($rs["descuento"]);
            $objectVO->setFechaHoraTransaccion($rs["fecha_hora_transaccion"]);
            $objectVO->setVolumenDocumentado($rs["volumen_documentado"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \EnviosTransportistasVO
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
     * @return \EnviosTransportistasVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new EnviosTransportistasVO();
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
     * @param \EnviosTransportistasVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "cfdi, "
                . "tipocfdi, "
                . "contra_prestacion = ?, "
                . "tarifa_de_transporte = ?, "
                . "cargo_por_capacidad_trans = ?, "
                . "cargo_por_uso_trans = ?, "
                . "cargo_volumetrico_trans = ?, "
                . "descuento = ?, "
                . "fecha_hora_transaccion = ?, "
                . "volumen_documentado = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssddddddsdi",
                    $objectVO->getCfdi(),
                    $objectVO->getTipocfdi(),
                    $objectVO->getContraPrestacion(),
                    $objectVO->getTarifaDeTransporte(),
                    $objectVO->getCargoPorCapacidadTrans(),
                    $objectVO->getCargoPorUsoTrans(),
                    $objectVO->getCargoVolumetricoTrans(),
                    $objectVO->getDescuento(),
                    $objectVO->getFechaHoraTransaccion(),
                    $objectVO->getVolumenDocumentado(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
