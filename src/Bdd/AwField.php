<?php
namespace src\Bdd;

class AwField{
    // Génère le code SQL
    private $name, $id_field, $forTable;
    private $type;

    // Gérer par le manager
    private $isNull, $default_value;
    private $isUnique, $array, $critic, $const, $linked;
    // Manager don't take care of id, created_at during add
    private $alreadyManaged;

    // Constante de type
    // Int
    const TYPE_TINYINT = 'TINYINT';
    const TYPE_SMALLINT = 'SMALLINT';
    const TYPE_INT = 'INT';
    const TYPE_BIGINT = 'BIGINT';
    // Float / Decimal
    const TYPE_DECIMAL = 'DECIMAL';
    const TYPE_FLOAT = 'FLOAT';
    // Boolean
    const TYPE_BOOLEAN = 'TINYINT(1)';
    // Text
    const TYPE_STRING = 'VARCHAR(255)';
    const TYPE_TEXT = 'TEXT';
    const TYPE_MEDIUMTEXT = 'MEDIUMTEXT';
    const TYPE_LONGTEXT = 'LONGTEXT';
    // Array
    const TYPE_ARRAY = 'TEXT';
    // Datetime / Time
    const TYPE_DATETIME = 'DATETIME';
    const TYPE_TIME = 'TIME';
    const TYPE_TIMESTAMP = 'TIMESTAMP';

    public function __construct($donnees) {
        $this->setName($donnees['name']);
        $this->setId_field($donnees['id_field']);
        $this->setForTable($donnees['table']);
        $this->setType($donnees['type']);
        $this->setIsNull($donnees['null']);
        $this->setDefault($donnees['default']);
        $this->setIsUnique($donnees['unique']);
        $this->setArray($donnees['array']);
        $this->setCritic($donnees['critic']);
        $this->setConst($donnees['const']);
        $this->setLinked($donnees['linked']);

        if (empty($donnees['alreadyManaged']))
            $donnees['alreadyManaged'] = false;

        $this->setAlreadyManaged($donnees['alreadyManaged']);
    }

    // Getters / is
    public function getName(){ return $this->name; }
    public function getId_field(){ return $this->id_field; }
    public function getTable(){ return $this->forTable; }
    public function getType(){ return $this->type; }
    public function isNull(){ return $this->isNull; }
    public function getDefault(){ return $this->default_value; }
    public function isUnique(){ return $this->isUnique; }
    public function isArray(){ return $this->array; }
    public function isCritic(){ return $this->critic; }
    public function isConst(){ return $this->const; }
    public function isLinked(){ return $this->linked; }
    public function isAlreadyManaged(){ return $this->alreadyManaged; }

    public function getUnique_id(){ return $this->forTable.'_'.$this->id_field; }

    // Setter
    public function setName($value){
        if (empty($value))
            throw new \Exception("Field : name is null");

        $this->name = $value;
        return $this;
    }

    public function setId_Field($value){
        if (empty($value))
            throw new \Exception("Field : id_field is null");

        $this->id_field = $value;
        return $this;
    }

    public function setForTable($value){
        if (empty($value))
            throw new \Exception("Field : forTalbe is null");

        $this->forTable = $value;
        return $this;
    }

    public function setType($value){
        if (empty($value) && defined('self::'.$value))
            throw new \Exception("Field : type is null or is not a constant");

        $this->type = $value;
        return $this;
    }

    public function setIsNull($value){
        $this->isNull = (bool) $value;
        return $this;
    }
    public function setDefault($value){
        $this->default_value = $value;
        return $this;
    }
    public function setIsUnique($value){
        $this->isUnique = (bool) $value;
        return $this;
    }
    public function setArray($value){
        $this->array = (bool) $value;
        return $this;
    }
    public function setCritic($value){
        $this->critic = (bool) $value;
        return $this;
    }
    public function setConst($value){
        $this->const = (bool) $value;
        return $this;
    }
    public function setLinked($value){
        $this->linked = (bool) $value;
        return $this;
    }
    public function setAlreadyManaged($value){
        $this->alreadyManaged = (bool) $value;
        return $this;
    }

    // Fonctions de comparaison avec un autre Field
    public function hasSameName($f){
        return $this->name == $f->getName();
    }

    public function hasSameUniqueId($f){
        return $this->getUnique_id() == $f->getUnique_id();
    }

    public function hasSameType($f){
        return $this->type == $f->getType();
    }

    // Comparaison de la table
    public function tableIs($table){
        return $this->forTable == $table;
    }

    // Génère le SQL
    public function toSQL(){
        return $this->name.' '.$this->type;
    }

    public function toSQLWithOldName($name){
        return $name.' '.$this->type;
    }

    public function __tostring(){
        return $this->name;
    }
}
