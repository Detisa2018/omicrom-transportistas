<?php

/**
 * Description of AuthSemestralResultadosVO
 *
 * @author Alejandro Ayala Gonzalez
 */
class AuthSemestralResultadosVO {

    private $id;
    private $id_auth_semestral;
    private $id_authuser;
    private $status_anterior;
    private $status_actual;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getId_auth_semestral() {
        return $this->id_auth_semestral;
    }

    public function getId_authuser() {
        return $this->id_authuser;
    }

    public function getStatus_anterior() {
        return $this->status_anterior;
    }

    public function getStatus_actual() {
        return $this->status_actual;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setId_auth_semestral($id_auth_semestral): void {
        $this->id_auth_semestral = $id_auth_semestral;
    }

    public function setId_authuser($id_authuser): void {
        $this->id_authuser = $id_authuser;
    }

    public function setStatus_anterior($status_anterior): void {
        $this->status_anterior = $status_anterior;
    }

    public function setStatus_actual($status_actual): void {
        $this->status_actual = $status_actual;
    }

}
