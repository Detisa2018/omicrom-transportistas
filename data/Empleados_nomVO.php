<?php

/**
 * Description of Empleados_nomVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since ene 2025
 */
class Empleados_nomVO {

    private $id;
    private $rfc;
    private $curp;
    private $nombre;
    private $imss;
    private $cuenta_bancaria;
    private $fecha_ingreso;
    private $tipo_nomina;
    private $id_departamento;
    private $no_credencial;
    private $status;
    private $sueldo_diario;
    private $sueldo_integrado;
    private $baja;
    private $observaciones;

    public function __construct() {
        
    }
    public function getId() {
        return $this->id;
    }

    public function getRfc() {
        return $this->rfc;
    }

    public function getCurp() {
        return $this->curp;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getImss() {
        return $this->imss;
    }

    public function getCuenta_bancaria() {
        return $this->cuenta_bancaria;
    }

    public function getFecha_ingreso() {
        return $this->fecha_ingreso;
    }

    public function getTipo_nomina() {
        return $this->tipo_nomina;
    }

    public function getId_departamento() {
        return $this->id_departamento;
    }

    public function getNo_credencial() {
        return $this->no_credencial;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getSueldo_diario() {
        return $this->sueldo_diario;
    }

    public function getSueldo_integrado() {
        return $this->sueldo_integrado;
    }

    public function getBaja() {
        return $this->baja;
    }

    public function getObservaciones() {
        return $this->observaciones;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setRfc($rfc): void {
        $this->rfc = $rfc;
    }

    public function setCurp($curp): void {
        $this->curp = $curp;
    }

    public function setNombre($nombre): void {
        $this->nombre = $nombre;
    }

    public function setImss($imss): void {
        $this->imss = $imss;
    }

    public function setCuenta_bancaria($cuenta_bancaria): void {
        $this->cuenta_bancaria = $cuenta_bancaria;
    }

    public function setFecha_ingreso($fecha_ingreso): void {
        $this->fecha_ingreso = $fecha_ingreso;
    }

    public function setTipo_nomina($tipo_nomina): void {
        $this->tipo_nomina = $tipo_nomina;
    }

    public function setId_departamento($id_departamento): void {
        $this->id_departamento = $id_departamento;
    }

    public function setNo_credencial($no_credencial): void {
        $this->no_credencial = $no_credencial;
    }

    public function setStatus($status): void {
        $this->status = $status;
    }

    public function setSueldo_diario($sueldo_diario): void {
        $this->sueldo_diario = $sueldo_diario;
    }

    public function setSueldo_integrado($sueldo_integrado): void {
        $this->sueldo_integrado = $sueldo_integrado;
    }

    public function setBaja($baja): void {
        $this->baja = $baja;
    }

    public function setObservaciones($observaciones): void {
        $this->observaciones = $observaciones;
    }

}
