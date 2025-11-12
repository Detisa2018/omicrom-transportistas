<?php

/**
 * Description of AuthSemestralVO
 *
 * @author Alejandro Ayala Gonzalez
 */
class AuthSemestralVO {

    private $id;
    private $fecha;
    private $descripcion;
    private $id_authuser;
    private $status;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getId_authuser() {
        return $this->id_authuser;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setFecha($fecha): void {
        $this->fecha = $fecha;
    }

    public function setDescripcion($descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setId_authuser($id_authuser): void {
        $this->id_authuser = $id_authuser;
    }

    public function setStatus($status): void {
        $this->status = $status;
    }

}
