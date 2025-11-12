<?php

namespace com\softcoatl\utils;

class Configuration {

    private static $dbc = array(
        "driver" => "mysql",
        "charset" => "utf8mb4",
        "host" => "20.23.26.6", //Demo 200 20.23.0.51 |  Transportadora demo 20.23.26.6
        "username" => "root",
        "pass" => "det15a",
        "database" => "omicrom"
    );

    public static function get() {
        return (object) Configuration::$dbc;
    }

    private static $dbcRepository = array(
        "driver" => "mysql",
        "charset" => "utf8mb4",
        "host" => "163.74.93.192",
        "username" => "root",
        "pass" => "det15a",
        "database" => "soporte"
    );

    public static function getRepository() {
        return (object) Configuration::$dbcRepository;
    }

    private static $dbcRepositoryGlobalFae = array(
        "driver" => "mysql",
        "charset" => "utf8mb4",
        "host" => "67.228.102.107",
        "username" => "root",
        "pass" => "det15a",
        "database" => "globalfae"
    );

    public static function getRepositoryGlobalFae() {
        return (object) Configuration::$dbcRepositoryGlobalFae;
    }

}
