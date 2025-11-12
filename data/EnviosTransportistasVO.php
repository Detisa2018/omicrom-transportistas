<?php

/**
 * Description of EnviosTransportistasVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since 17 Sep
 */
class EnviosTransportistasVO {

    private $id;
    private $idVenta;
    private $idCliente;
    private $cfdi;
    private $tipocfdi;
    private $contraPrestacion;
    private $tarifaDeTransporte;
    private $cargoPorCapacidadTrans;
    private $cargoPorUsoTrans;
    private $cargoVolumetricoTrans;
    private $descuento;
    private $fechaHoraTransaccion;
    private $volumenDocumentado;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getIdVenta() {
        return $this->idVenta;
    }

    public function getIdCliente() {
        return $this->idCliente;
    }

    public function setIdCliente($idCliente): void {
        $this->idCliente = $idCliente;
    }

    public function getCfdi() {
        return $this->cfdi;
    }

    public function getTipocfdi() {
        return $this->tipocfdi;
    }

    public function getContraPrestacion() {
        return $this->contraPrestacion;
    }

    public function getTarifaDeTransporte() {
        return $this->tarifaDeTransporte;
    }

    public function getCargoPorCapacidadTrans() {
        return $this->cargoPorCapacidadTrans;
    }

    public function getCargoPorUsoTrans() {
        return $this->cargoPorUsoTrans;
    }

    public function getCargoVolumetricoTrans() {
        return $this->cargoVolumetricoTrans;
    }

    public function getDescuento() {
        return $this->descuento;
    }

    public function getFechaHoraTransaccion() {
        return $this->fechaHoraTransaccion;
    }

    public function getVolumenDocumentado() {
        return $this->volumenDocumentado;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setIdVenta($idVenta): void {
        $this->idVenta = $idVenta;
    }

    public function setCfdi($cfdi): void {
        $this->cfdi = $cfdi;
    }

    public function setTipocfdi($tipocfdi): void {
        $this->tipocfdi = $tipocfdi;
    }

    public function setContraPrestacion($contraPrestacion): void {
        $this->contraPrestacion = $contraPrestacion;
    }

    public function setTarifaDeTransporte($tarifaDeTransporte): void {
        $this->tarifaDeTransporte = $tarifaDeTransporte;
    }

    public function setCargoPorCapacidadTrans($cargoPorCapacidadTrans): void {
        $this->cargoPorCapacidadTrans = $cargoPorCapacidadTrans;
    }

    public function setCargoPorUsoTrans($cargoPorUsoTrans): void {
        $this->cargoPorUsoTrans = $cargoPorUsoTrans;
    }

    public function setCargoVolumetricoTrans($cargoVolumetricoTrans): void {
        $this->cargoVolumetricoTrans = $cargoVolumetricoTrans;
    }

    public function setDescuento($descuento): void {
        $this->descuento = $descuento;
    }

    public function setFechaHoraTransaccion($fechaHoraTransaccion): void {
        $this->fechaHoraTransaccion = $fechaHoraTransaccion;
    }

    public function setVolumenDocumentado($volumenDocumentado): void {
        $this->volumenDocumentado = $volumenDocumentado;
    }

}
