<?php
namespace src\GeneratePhp;

class GeneratePhp {

    public static function getEntityCode($name, $number){
        $content = '<?php
namespace src\Entity;

class '.$name.' extends BaseEntity
{
    // Fields
    // protected ...

    // Constructeur qui initialise l\'user
    public function __construct($donnees){
        parent::__construct($donnees);
    }

    // Setters / Getters';
    for ($i = 0; $i<$number;$i++){
        $content = $content.'
    public function get(){ return $this->; }
    public function set($value){
        $this-> = $value;
        return $this;
    }
        ';
    }

    $content = $content.'
}';

        return $content;
    }

    public static function getConfigEntityCode($name, $number){
        $content = '<?php
namespace src\Config;

use src\Bdd\AwField as Field;
use src\Entity\BasicUser;

class Config'.$name.' extends ConfigBaseEntity{

    public function init(){';

        for ($i = 0; $i<$number;$i++){
            $content = $content.'
            $field_'.$i.' = array(
                \'name\' => \'...\',
                \'id_field\' => \'...\',
                \'table\' => $this->TABLE(),
                \'type\' => FIELD::TYPE_...,
                \'null\' => false/true,
                \'default\' => null or \'...\' or array(),
                \'unique\' => false/true,
                \'array\' => false/true,
                \'critic\' => false/true,
                \'const\' => false/true,
                \'linked\' => false/true
            );
            ';
        }

        for ($i = 0; $i<$number;$i++){
            $content = $content.'
            $this->add(new Field($field_'.$i.'));';
        }


        $content = $content.'
    }

    public function ABLE_TO_GET(){ return BasicUser::ROLE_...; }
    public function ABLE_TO_ADD(){ return BasicUser::ROLE_...; }
    public function ABLE_TO_UPDATE(){ return BasicUser::ROLE_...; }
    public function ABLE_TO_DELETE(){ return BasicUser::ROLE_...; }

    public function CONFIG_NAME(){ return \'src\Config\\Config'.$name.'\'; }
    public function CLASS_NAME(){ return \'src\Entity\\'.$name.'\'; }
    public function ENTITY_NAME(){ return \''.$name.'\'; }

    public function ONE_TO_ONE(){ return []; }
    public function ONE_TO_MANY(){ return []; }
    public function MANY_TO_ONE(){ return []; }
    public function MANY_TO_MANY(){ return []; }

    public function verifyConditionsToAdd($entity, $user){ return true; }
    public function verifyConditionsToUpdate($entity, $user, $data){ return true; }
    public function verifyConditionsToDelete($entity, $user){ return true; }
    public function verifyConditionsToGet($entity, $user){ return true; }

    public function beforeAdd($entity, $moreData){}
    public function afterAdd($entity, $moreData){
        parent::afterAdd($entity, $moreData);
    }
    public function beforeUpdate($entity, $moreData){}
    public function afterUpdate($entity, $moreData){
        parent::afterUpdate($entity, $moreData);
    }
    public function beforeDelete($entity){}
    public function afterDelete($entity){
        parent::afterDelete($entity);
    }
}';

        return $content;
    }

}
