<?php
namespace src\Config;

use src\Bdd\AwField as Field;
use src\Entity\BasicUser;

class ConfigBasicUser extends ConfigBaseEntity{

    public function init(){
        $field_pseudo = array(
            'name' => 'pseudo',
            'id_field' => 'pseudo',
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
        $field_password = array(
            'name' => 'password',
            'id_field' => 'pw',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_STRING,
            'null' => false,
            'default' => null,
            'unique' => false,
            'array' => false,
            'critic' => true,
            'const' => false,
            'linked' => false
        );
        $field_roles = array(
            'name' => 'roles',
            'id_field' => 'roles',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_ARRAY,
            'null' => false,
            'default' => [BasicUser::ROLE_USER],
            'unique' => false,
            'array' => true,
            'critic' => true,
            'const' => false,
            'linked' => false
        );

        $this->add(new Field($field_pseudo))
             ->add(new Field($field_password))
             ->add(new Field($field_roles));
    }

    public function ABLE_TO_GET(){ return BasicUser::ROLE_ANONYME; }
    public function ABLE_TO_ADD(){ return BasicUser::ROLE_ANONYME; }
    public function ABLE_TO_UPDATE(){ return BasicUser::ROLE_USER; }
    public function ABLE_TO_DELETE(){ return BasicUser::ROLE_USER; }

    public function CONFIG_NAME(){ return 'src\Config\ConfigBasicUser'; }
    public function CLASS_NAME(){ return 'src\Entity\BasicUser'; }
    public function ENTITY_NAME(){ return 'BasicUser'; }

    public function ONE_TO_ONE(){ return []; }
    public function ONE_TO_MANY(){ return []; }
    public function MANY_TO_ONE(){ return []; }
    public function MANY_TO_MANY(){ return []; }

    /*
    ONE_TO_ONE = [
        ['', '', ''],
        ['', '', '']
    ];
    ONE_TO_MANY = [
        ['', '', ''],
        ['', '', '']
    ];
    MANY_TO_ONE = [
        ['', '', ''],
        ['', '', '']
    ];
    MANY_TO_MANY = [
        ['', '', ''],
        ['', '', '']
    ];
    */

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
}
