<?php

/**
 * Description of Ct_cerradoVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since ene 2025
 */
class Ct_cerradoVO {

    private $id;
    private $id_ct;
    private $id_usr;
    private $fecha;
    private $total;
    private $credito;
    private $bancos;
    private $consignaciones;
    private $monederos;
    private $aceites;
    private $dolares;
    private $gastos;
    private $efectivo;
    private $depositos;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getId_ct() {
        return $this->id_ct;
    }

    public function getId_usr() {
        return $this->id_usr;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getCredito() {
        return $this->credito;
    }

    public function getBancos() {
        return $this->bancos;
    }

    public function getConsignaciones() {
        return $this->consignaciones;
    }

    public function getMonederos() {
        return $this->monederos;
    }

    public function getAceites() {
        return $this->aceites;
    }

    public function getDolares() {
        return $this->dolares;
    }

    public function getGastos() {
        return $this->gastos;
    }

    public function getEfectivo() {
        return $this->efectivo;
    }

    public function getDepositos() {
        return $this->depositos;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setId_ct($id_ct): void {
        $this->id_ct = $id_ct;
    }

    public function setId_usr($id_usr): void {
        $this->id_usr = $id_usr;
    }

    public function setFecha($fecha): void {
        $this->fecha = $fecha;
    }

    public function setTotal($total): void {
        $this->total = $total;
    }

    public function setCredito($credito): void {
        $this->credito = $credito;
    }

    public function setBancos($bancos): void {
        $this->bancos = $bancos;
    }

    public function setConsignaciones($consignaciones): void {
        $this->consignaciones = $consignaciones;
    }

    public function setMonederos($monederos): void {
        $this->monederos = $monederos;
    }

    public function setAceites($aceites): void {
        $this->aceites = $aceites;
    }

    public function setDolares($dolares): void {
        $this->dolares = $dolares;
    }

    public function setGastos($gastos): void {
        $this->gastos = $gastos;
    }

    public function setEfectivo($efectivo): void {
        $this->efectivo = $efectivo;
    }

    public function setDepositos($depositos): void {
        $this->depositos = $depositos;
    }

}
