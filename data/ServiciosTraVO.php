<?php

/**
 * Description of ServiciosTraVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since nov 2025
 */
class ServiciosTraVO {

    private $id;
    private $sucursal;
    private $folio;
    private $nombre;
    private $clave_unidad;
    private $clave_producto;
    private $precio;
    private $identificador;

    public function __construct() {
        
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

    public function getIdentificador() {
        return $this->identificador;
    }

    public function setIdentificador($identificador): void {
        $this->identificador = $identificador;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function setPrecio($precio): void {
        $this->precio = $precio;
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getClave_unidad() {
        return $this->clave_unidad;
    }

    public function getClave_producto() {
        return $this->clave_producto;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setNombre($nombre): void {
        $this->nombre = $nombre;
    }

    public function setClave_unidad($clave_unidad): void {
        $this->clave_unidad = $clave_unidad;
    }

    public function setClave_producto($clave_producto): void {
        $this->clave_producto = $clave_producto;
    }

}
