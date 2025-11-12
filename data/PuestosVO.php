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
class PuestosVO {

    private $id;
    private $puesto;
    private $descripcion;
    private $id_departamento;
    private $sueldo_base;
    private $nivel_salarial;
    private $tipo_contrato;
    private $horario_laboral_entrada;
    private $horario_laboral_salida;
    private $estatus;
    private $fecha_creacion;
    
    public function __construct() {
        
    }
    
    public function getId() {
        return $this->id;
    }

    public function getPuesto() {
        return $this->puesto;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getId_departamento() {
        return $this->id_departamento;
    }

    public function getSueldo_base() {
        return $this->sueldo_base;
    }

    public function getNivel_salarial() {
        return $this->nivel_salarial;
    }

    public function getTipo_contrato() {
        return $this->tipo_contrato;
    }

    public function getHorario_laboral_entrada() {
        return $this->horario_laboral_entrada;
    }

    public function getHorario_laboral_salida() {
        return $this->horario_laboral_salida;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    public function getFecha_creacion() {
        return $this->fecha_creacion;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setPuesto($puesto): void {
        $this->puesto = $puesto;
    }

    public function setDescripcion($descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setId_departamento($id_departamento): void {
        $this->id_departamento = $id_departamento;
    }

    public function setSueldo_base($sueldo_base): void {
        $this->sueldo_base = $sueldo_base;
    }

    public function setNivel_salarial($nivel_salarial): void {
        $this->nivel_salarial = $nivel_salarial;
    }

    public function setTipo_contrato($tipo_contrato): void {
        $this->tipo_contrato = $tipo_contrato;
    }

    public function setHorario_laboral_entrada($horario_laboral_entrada): void {
        $this->horario_laboral_entrada = $horario_laboral_entrada;
    }

    public function setHorario_laboral_salida($horario_laboral_salida): void {
        $this->horario_laboral_salida = $horario_laboral_salida;
    }

    public function setEstatus($estatus): void {
        $this->estatus = $estatus;
    }

    public function setFecha_creacion($fecha_creacion): void {
        $this->fecha_creacion = $fecha_creacion;
    }

}


