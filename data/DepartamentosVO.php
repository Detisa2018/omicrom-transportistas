<?php

/**
 * Description of PuestosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since feb 2025
 */
class DepartamentosVO {

    private $id;
    private $nombre;
    private $descripcion;
    private $id_superior;
    private $id_responsable;
    private $ubicacion;
    private $estatus;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getId_superior() {
        return $this->id_superior;
    }

    public function getId_responsable() {
        return $this->id_responsable;
    }

    public function getUbicacion() {
        return $this->ubicacion;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setNombre($nombre): void {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setId_superior($id_superior): void {
        $this->id_superior = $id_superior;
    }

    public function setId_responsable($id_responsable): void {
        $this->id_responsable = $id_responsable;
    }

    public function setUbicacion($ubicacion): void {
        $this->ubicacion = $ubicacion;
    }

    public function setEstatus($estatus): void {
        $this->estatus = $estatus;
    }

}
