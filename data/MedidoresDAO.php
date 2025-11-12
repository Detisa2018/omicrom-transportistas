<?php

/**
 * Description of MeDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('MedidoresVO.php');

class MedidoresDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "medidores";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \MedidoresVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = MeVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "num_dispensario,"
                . "posicion,"
                . "num_manguera,"
                . "num_medidor,"
                . "valor_calibracion,"
                . "vigencia_calibracion,"
                . "tipo_medidor,"
                . "modelo_medidor,"
                . "incertidumbre,"
                . "disp_asociado"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiiidsssds",
                    $objectVO->getNum_dispensario(),
                    $objectVO->getPosicion(),
                    $objectVO->getNum_manguera(),
                    $objectVO->getNum_medidor(),
                    $objectVO->getValor_calibracion(),
                    $objectVO->getVigencia_calibracion(),
                    $objectVO->getTipo_medidor(),
                    $objectVO->getModelo_medidor(),
                    $objectVO->getIncertidumbre(),
                    $objectVO->getDisp_asociado()
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
     * @return \MedidoresVO
     */
    public function fillObject($rs) {
        $objectVO = new MedidoresVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setNum_dispensario($rs["num_dispensario"]);
            $objectVO->setPosicion($rs["posicion"]);
            $objectVO->setNum_manguera($rs["num_manguera"]);
            $objectVO->setNum_medidor($rs["num_medidor"]);
            $objectVO->setValor_calibracion($rs["valor_calibracion"]);
            $objectVO->setVigencia_calibracion($rs["vigencia_calibracion"]);
            $objectVO->setTipo_medidor($rs["tipo_medidor"]);
            $objectVO->setModelo_medidor($rs["modelo_medidor"]);
            $objectVO->setIncertidumbre($rs["incertidumbre"]);
            $objectVO->setIncertidumbre($rs["disp_asociado"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \MedidoresVO
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
     * @return \MedidoresVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new MedidoresVO();
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
     * @param \MedidoresVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = MedidoresVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "num_dispensario = ?, "
                . "posicion = ?, "
                . "num_manguera = ?, "
                . "num_medidor = ?, "
                . "valor_calibracion = ?, "
                . "vigencia_calibracion = ?, "
                . "tipo_medidor = ?, "
                . "modelo_medidor = ?, "
                . "incertidumbre = ?, "
                . "disp_asociado = ? "
                . "WHERE id = ? ";
        error_log($sql);
        error_log(print_r($objectVO, true));
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiiidsssdsi",
                    $objectVO->getNum_dispensario(),
                    $objectVO->getPosicion(),
                    $objectVO->getNum_manguera(),
                    $objectVO->getNum_medidor(),
                    $objectVO->getValor_calibracion(),
                    $objectVO->getVigencia_calibracion(),
                    $objectVO->getTipo_medidor(),
                    $objectVO->getModelo_medidor(),
                    $objectVO->getIncertidumbre(),
                    $objectVO->getDisp_asociado(),
                    $objectVO->getId()
            );
            error_log("0");
            if ($ps->execute()) {
                error_log($this->conn->error);
                return true;
            }
        }
        error_log("3");
        error_log($this->conn->error);
        return false;
    }

    /**
     * 
     * @param \MedidoresVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function updateTanques($objectVO = MedidoresVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "vigencia_calibracion = ?, "
                . "tipo_medidor = ?, "
                . "modelo_medidor = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssi",
                    $objectVO->getVigencia_calibracion(),
                    $objectVO->getTipo_medidor(),
                    $objectVO->getModelo_medidor(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                return true;
            }
        }
        return false;
    }

    /**
     * 
     * @param \MedidoresVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function createTanques($objectVO = MeVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "num_dispensario,"
                . "vigencia_calibracion,"
                . "tipo_medidor,"
                . "modelo_medidor,"
                . "disp_asociado"
                . ") "
                . "VALUES(?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issss",
                    $objectVO->getNum_dispensario(),
                    $objectVO->getVigencia_calibracion(),
                    $objectVO->getTipo_medidor(),
                    $objectVO->getModelo_medidor(),
                    $objectVO->getDisp_asociado()
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

}
