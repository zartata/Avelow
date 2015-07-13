<?php
namespace src\Config;

use src\Bdd\AwField as Field;
use src\Entity\BasicUser;

class ConfigAwFile extends ConfigBaseEntity{

    public function init(){
        $field_type = array(
            'name' => 'type',
            'id_field' => 'type',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_STRING,
            'null' => false,
            'default' => null,
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false
        );

        $field_url = array(
            'name' => 'url',
            'id_field' => 'url',
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

        $field_extension = array(
            'name' => 'extension',
            'id_field' => 'extension',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_STRING,
            'null' => false,
            'default' => null,
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false
        );

        $field_size = array(
            'name' => 'size',
            'id_field' => 'size',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_BIGINT,
            'null' => false,
            'default' => null,
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false
        );

        $field_owner_id = array(
            'name' => 'owner_id',
            'id_field' => 'owner_id',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_INT,
            'null' => false,
            'default' => null,
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => true
        );

        $this->add(new Field($field_type))
             ->add(new Field($field_url))
             ->add(new Field($field_name))
             ->add(new Field($field_extension))
             ->add(new Field($field_size))
             ->add(new Field($field_owner_id));
    }

    public function ABLE_TO_GET(){ return BasicUser::ROLE_USER; }
    public function ABLE_TO_ADD(){ return BasicUser::ROLE_ANONYME; }
    public function ABLE_TO_UPDATE(){ return BasicUser::ROLE_USER; }
    public function ABLE_TO_DELETE(){ return BasicUser::ROLE_USER; }

    public function CONFIG_NAME(){ return 'src\Config\ConfigFile'; }
    public function CLASS_NAME(){ return 'src\Entity\AwFile'; }
    public function ENTITY_NAME(){ return 'AwFile'; }

    public function ONE_TO_ONE(){ return []; }
    public function ONE_TO_MANY(){ return []; }
    public function MANY_TO_ONE(){ return [
        ['owner', 'id', 'src\Entity\BasicUser']
    ]; }
    public function MANY_TO_MANY(){ return []; }

    public function verifyConditionsToAdd($entity, $user){ return true; }

    public function verifyConditionsToUpdate($entity, $user, $data){ return false; }
    public function verifyConditionsToDelete($entity, $user){ return false; }
    public function verifyConditionsToGet($entity, $user){ return true; }

    public function beforeAdd($entity, $moreData){
        move_uploaded_file($moreData['file']['file']['tmp_name'], '../../www'.$moreData['url']);
    }
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
