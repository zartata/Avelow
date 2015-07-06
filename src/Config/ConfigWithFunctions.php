<?php
namespace src\Config;

abstract class ConfigWithFunctions{

    protected static $app;

    public static function generalConfig(){
        date_default_timezone_set('Europe/Paris');
    }

    public static function getApp(){

        // Avoir accès à app, en singleton
        if (self::$app == null){
            self::$app = new \Slim\Slim();
        }
        return self::$app;
    }

    public static function getEntitiesInDBInLowerCase(){

        $entites = array();

        foreach(static::getEntitiesInDB() as $entity){
            $entities[] = strtolower($entity);
        }

        return $entities;
    }

    // Récupère toutes les configs
    public static function getConfigs(){

        $configs = array();
        $entities = static::getEntitiesInDB();

        foreach ($entites as $entity){
            // On ajoute chaque config
            $configName = 'Config'.ucfirst($entity);
            $configs[$entity] = new $configName();
        }

        return $configs;

    }

    public static function getConfig($entityName){
        if (!in_array($entityName, static::getEntitiesInDB()))
            throw new \Exception("Config : getConfig : no such entity");

        $configName = 'src\Config\Config'.ucfirst($entityName);

        return new $configName();
    }

    public static function getEntitiesInDB(){
        // gérer l'url
        return json_decode(file_get_contents(dirname(__FILE__).'/EntitiesInDB.json')); // array of entity name
    }


    public function addEntityInDB($entity){

        $entities = json_decode(file_get_contents(dirname(__FILE__).'/EntitiesInDB.json'));
        $entities[] = $entity;

        file_put_contents(dirname(__FILE__).'/EntitiesInDB.json', json_encode($entities));


    }

}
