<?php
namespace src\Config;

use src\Bdd\AwField as Field;
use src\Entity\BasicUser;

class ConfigAwTable extends ConfigBaseEntity{

    public function init(){
        $field_name = array(
            'name' => 'name',
            'id_field' => 'name',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_STRING,
            'null' => false,
            'default' => null,
            'unique' => true,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false
        );

        $field_entity = array(
            'name' => 'entity',
            'id_field' => 'entity',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_STRING,
            'null' => false,
            'default' => null,
            'unique' => true,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false
        );

        $field_fields = array(
            'name' => 'fields',
            'id_field' => 'fields',
            'table' => static::TABLE(),
            'type' => FIELD::TYPE_ARRAY,
            'null' => false,
            'default' => null,
            'unique' => false,
            'array' => true,
            'critic' => false,
            'const' => false,
            'linked' => false
        );

        $field_lastUpdate = array(
            'name' => 'lastUpdate',
            'id_field' => 'lastUpdate',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_DATETIME,
            'null' => false,
            'default' => date('Y-m-d H:i:s'),
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false
        );

        $this->add(new Field($field_name))
             ->add(new Field($field_entity))
             ->add(new Field($field_fields))
             ->add(new Field($field_lastUpdate));
    }

    public function ABLE_TO_GET(){ return BasicUser::ROLE_ADMIN; }
    public function ABLE_TO_ADD(){ return BasicUser::ROLE_ADMIN; }
    public function ABLE_TO_UPDATE(){ return BasicUser::ROLE_ADMIN; }
    public function ABLE_TO_DELETE(){ return BasicUser::ROLE_ADMIN; }

    public function CONFIG_NAME(){ return 'src\Config\ConfigAwTable'; }
    public function CLASS_NAME(){ return 'src\Entity\AwTable'; }
    public function ENTITY_NAME(){ return 'AwTable'; }

    public function ONE_TO_ONE(){ return []; }
    public function ONE_TO_MANY(){ return []; }
    public function MANY_TO_ONE(){ return []; }
    public function MANY_TO_MANY(){ return []; }

    public function verifyConditionsToAdd($entity, $user){ return false; }
    public function verifyConditionsToUpdate($entity, $user, $data){ return false; }
    public function verifyConditionsToDelete($entity, $user){ return false; }
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
}
