<?php
namespace src\Entity;

class AwTable extends BaseEntity
{
    protected $name;
    protected $entity;
    protected $fields;
    protected $lastUpdate;

    // Constructeur qui initialise l'user
    public function __construct($donnees){
        parent::__construct($donnees);
    }

    public function getEntity(){ return $this->entity; }
    public function setEntity($v){
        $this->entity = $v;
        return $this;
    }

    public function getName(){ return $this->name; }
    public function setName($v){
        $this->name = $v;
        return $this;
    }

    public function getFields(){ return $this->fields; }
    public function setFields($v){
        $this->fields = $v;
        return $this;
    }

    public function getLastUpdate(){ return $this->lastUpdate; }
    public function setLastUpdate($v){
        $this->lastUpdate = $v;
        return $this;
    }


}
