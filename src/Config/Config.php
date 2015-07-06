<?php
namespace src\Config;

class Config extends ConfigWithFunctions{

    public static function getDbHost(){
        return '...'; // db host
    }
    public static function getDbLogin(){
        return '...'; // db login
    }
    public static function getDbPassword(){
        return '...'; // db password
    }
    public static function getDbName(){
        return '...'; // db name
    }

    public static function isDebugMode(){
        return true/false; // or false
    }

    public static function getUserEntity(){
        return 'BasicUser';
    }

    // admin by default
    public static function getSuperAdminPassword(){
        return '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918';
    }
}
