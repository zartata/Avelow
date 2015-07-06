<?php
namespace src\Entity;

use src\Config\Config;

class AwFile extends BaseEntity
{
    // Unique (pas oublier id)
    protected $type = null;
    protected $url = null;
    protected $name = null;
    protected $extension = null;
    protected $size = 0;
    protected $owner = null;
    protected $owner_id = null;

    public function getType(){ return $this->type; }
    public function getUrl(){ return $this->url; }
    public function getName(){ return $this->name; }
    public function getExtension(){ return $this->extension; }
    public function getSize(){ return $this->size; }
    public function getOwner(){ return $this->owner; }
    public function getOwner_id(){ return $this->owner_id; }

    public function setType($value){
        $this->type = $value;
        return $this;
    }
    public function setUrl($value){
        $this->url = $value;
        return $this;
    }
    public function setName($value){
        $this->name = $value;
        return $this;
    }
    public function setExtension($value){
        $this->extension = $value;
        return $this;
    }
    public function setSize($value){
        $this->size = $value;
        return $this;
    }
    public function setOwner($value){
        $this->owner = $value;
        return $this;
    }
    public function setOwner_id($value){
        $this->owner_id = $value;
        return $this;
    }

}
