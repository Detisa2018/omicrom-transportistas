<?php

/**
 * Description of PercepcionesVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since feb 2025
 */
class PercepcionesVO {

    private $id;
    private $empleado_id;
    private $tipo_percepcion_id;
    private $monto;
    private $fecha;
    private $observaciones;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getEmpleado_id() {
        return $this->empleado_id;
    }

    public function getTipo_percepcion_id() {
        return $this->tipo_percepcion_id;
    }

    public function getMonto() {
        return $this->monto;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getObservaciones() {
        return $this->observaciones;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setEmpleado_id($empleado_id): void {
        $this->empleado_id = $empleado_id;
    }

    public function setTipo_percepcion_id($tipo_percepcion_id): void {
        $this->tipo_percepcion_id = $tipo_percepcion_id;
    }

    public function setMonto($monto): void {
        $this->monto = $monto;
    }

    public function setFecha($fecha): void {
        $this->fecha = $fecha;
    }

    public function setObservaciones($observaciones): void {
        $this->observaciones = $observaciones;
    }

}
