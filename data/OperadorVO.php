<?php

/**
 * Description of OperadorVO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodriguez 
 * @version 1.0
 * @since ago 2022
 */
class OperadorVO {

    private $id;
    private $sucursal;
    private $folio;
    private $rfc_operador;
    private $nombre;
    private $num_licencia;
    private $registro_fiscal;
    private $recidencia_fiscal;

    function __construct() {
        
    }

    public function getSucursal() {
        return $this->sucursal;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function setSucursal($sucursal): void {
        $this->sucursal = $sucursal;
    }

    public function setFolio($folio): void {
        $this->folio = $folio;
    }

    public function getRegistro_fiscal() {
        return $this->registro_fiscal;
    }

    public function getRecidencia_fiscal() {
        return $this->recidencia_fiscal;
    }

    public function setRegistro_fiscal($registro_fiscal): void {
        $this->registro_fiscal = $registro_fiscal;
    }

    public function setRecidencia_fiscal($recidencia_fiscal): void {
        $this->recidencia_fiscal = $recidencia_fiscal;
    }

    function getId() {
        return $this->id;
    }

    function getRfc_operador() {
        return $this->rfc_operador;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getNum_licencia() {
        return $this->num_licencia;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setRfc_operador($rfc_operador) {
        $this->rfc_operador = $rfc_operador;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setNum_licencia($num_licencia) {
        $this->num_licencia = $num_licencia;
    }

}
