<?php

/**
 * Description of MeVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class MedidoresVO {

    private $id;
    private $num_dispensario = 0;
    private $posicion = 0;
    private $num_manguera = 0;
    private $num_medidor = 0;
    private $valor_calibracion = 0;
    private $vigencia_calibracion;
    private $tipo_medidor;
    private $modelo_medidor;
    private $incertidumbre = 0;
    private $disp_asociado = '';

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getNum_dispensario() {
        return $this->num_dispensario;
    }

    public function getPosicion() {
        return $this->posicion;
    }

    public function getNum_manguera() {
        return $this->num_manguera;
    }

    public function getNum_medidor() {
        return $this->num_medidor;
    }

    public function getValor_calibracion() {
        return $this->valor_calibracion;
    }

    public function getVigencia_calibracion() {
        return $this->vigencia_calibracion;
    }

    public function getTipo_medidor() {
        return $this->tipo_medidor;
    }

    public function getModelo_medidor() {
        return $this->modelo_medidor;
    }

    public function getIncertidumbre() {
        return $this->incertidumbre;
    }

    public function getDisp_asociado() {
        return $this->disp_asociado;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setNum_dispensario($num_dispensario): void {
        $this->num_dispensario = $num_dispensario;
    }

    public function setPosicion($posicion): void {
        $this->posicion = $posicion;
    }

    public function setNum_manguera($num_manguera): void {
        $this->num_manguera = $num_manguera;
    }

    public function setNum_medidor($num_medidor): void {
        $this->num_medidor = $num_medidor;
    }

    public function setValor_calibracion($valor_calibracion): void {
        $this->valor_calibracion = $valor_calibracion;
    }

    public function setVigencia_calibracion($vigencia_calibracion): void {
        $this->vigencia_calibracion = $vigencia_calibracion;
    }

    public function setTipo_medidor($tipo_medidor): void {
        $this->tipo_medidor = $tipo_medidor;
    }

    public function setModelo_medidor($modelo_medidor): void {
        $this->modelo_medidor = $modelo_medidor;
    }

    public function setIncertidumbre($incertidumbre): void {
        $this->incertidumbre = $incertidumbre;
    }

    public function setDisp_asociado($disp_asociado): void {
        $this->disp_asociado = $disp_asociado;
    }

}
