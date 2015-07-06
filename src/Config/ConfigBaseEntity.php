<?php
namespace src\Config;

use src\Bdd\AwField as Field;
use src\Manager\BaseManager;
use src\Bdd\connexionPDO as coPDO;

abstract class ConfigBaseEntity{

    protected $fields;

    public function __construct() {
        $this->fields = array();

        // On ajoute les champs de BaseEntity
        $field_id = array(
            'name' => 'id',
            'id_field' => 'id',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_INT,
            'null' => false,
            'default' => null,
            'unique' => true,
            'array' => false,
            'critic' => false,
            'const' => true,
            'linked' => false,
            'alreadyManaged' => true
        );
        $field_deleted = array(
            'name' => 'deleted',
            'id_field' => 'deleted',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_BOOLEAN,
            'null' => false,
            'default' => 'false',
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false,
            'alreadyManaged' => true
        );
        $field_deleted_at = array(
            'name' => 'deleted_at',
            'id_field' => 'deleted_at',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_DATETIME,
            'null' => true,
            'default' => null,
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false,
            'alreadyManaged' => true
        );
        $field_created_at = array(
            'name' => 'created_at',
            'id_field' => 'created_at',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_DATETIME,
            'null' => false,
            'default' => 'now',
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false,
            'alreadyManaged' => true
        );
        $field_updated_at = array(
            'name' => 'updated_at',
            'id_field' => 'updated_at',
            'table' => $this->TABLE(),
            'type' => FIELD::TYPE_DATETIME,
            'null' => true,
            'default' => null,
            'unique' => false,
            'array' => false,
            'critic' => false,
            'const' => false,
            'linked' => false,
            'alreadyManaged' => true
        );

        $this->add(new Field($field_id))
             ->add(new Field($field_deleted))
             ->add(new Field($field_deleted_at))
             ->add(new Field($field_created_at))
             ->add(new Field($field_updated_at));


        // On récupère les champs de la Config
        $this->init();
    }

    // Toutes les fonctions à réécrire
    abstract public function init();

    abstract public function ABLE_TO_GET();
    abstract public function ABLE_TO_ADD();
    abstract public function ABLE_TO_UPDATE();
    abstract public function ABLE_TO_DELETE();

    abstract public function CONFIG_NAME();
    abstract public function CLASS_NAME();
    abstract public function ENTITY_NAME();

    abstract public function ONE_TO_ONE();
    abstract public function ONE_TO_MANY();
    abstract public function MANY_TO_ONE();
    abstract public function MANY_TO_MANY();

    /*
    const ONE_TO_ONE = [
        ['', '', ''];
    ];
    const ONE_TO_MANY = [
        ['', '', ''];
    ];
    const MANY_TO_ONE = [
        ['', '', '']
    ];
    const MANY_TO_MANY = [
        ['', '', ''];
    ];
    */

    abstract public function verifyConditionsToAdd($entity, $user);
    abstract public function verifyConditionsToUpdate($entity, $user, $data);
    abstract public function verifyConditionsToDelete($entity, $user);
    abstract public function verifyConditionsToGet($entity, $user);

    abstract public function beforeAdd($entity, $moreData);
    public function afterAdd($entity, $moreData){
        // mise à jour de AwTable
        $manager = new BaseManager(coPDO::get_db(), 'AwTable');
        $table = $manager->get('name', $this->table());
        $table->setLastUpdate(date('Y-m-d H:i:s'));
        $manager->update($table);
    }
    abstract public function beforeUpdate($entity, $moreData);
    public function afterUpdate($entity, $moreData){
        // mise à jour de AwTable
        $manager = new BaseManager(coPDO::get_db(), 'AwTable');
        $table = $manager->get('name', $this->table());
        $table->setLastUpdate(date('Y-m-d H:i:s'));
        $manager->update($table);
    }
    abstract public function beforeDelete($entity);
    public function afterDelete($entity){
        // mise à jour de AwTable
        $manager = new BaseManager(coPDO::get_db(), 'AwTable');
        $table = $manager->get('name', $this->table());
        $table->setLastUpdate(date('Y-m-d H:i:s'));
        $manager->update($table);
    }

    public function TABLE(){
        return substr(strtolower((new \ReflectionClass($this))->getShortName()),6);
    }

    public function isValid($entity){
        $allIsOk = true;
        foreach ($this->fields as $f) {
            if (!$f->isNull() && $f->getDefault() == null && !$f->isAlreadyManaged()){
                $getter = 'get'.ucfirst($f->getName());
                if ($entity->$getter() == null){
                    $allIsOk = false;
                }
            }
        }
        return $allIsOk;
    }

    protected function add($field){

        // Vérification de la table
        if (!$field->tableIs($this->TABLE()))
            throw new Exception("Config Entity : add : different table");

        // Vérification de l'unique_id
        foreach ($this->fields as $f) {
            if ($field->hasSameUniqueId($f))
                throw new Exception("Config Entity : add : same unique_id");

        }

        // On ajoute s'il n'y a pas eu d'erreur
        $this->fields[$field->getUnique_id()] = $field;
        return $this;
    }

    public function remove($unique_id){
        // Si l'unique_id est dans le tableau
        if (array_key_exists($unique_id, $this->fields))
            unset($this->fields[$unique_id]);
        else
            throw new Exception("Config Entity : remove : no such unique_id");
    }

    public function getFields(){
        return $this->fields;
    }

    public function getFieldsName(){
        $fields = array();

        foreach($this->fields as $f){
            $fields[] = $f->getName();
        }
        return $fields;
    }

    public function getLinkedFields(){

        $fields = array();

        foreach ($this->fields as $f){
            if ($f->isLinked() && !$f->isAlreadyManaged())
                $fields[$f->getName()] = 'id';
        }

        return $fields;

    }

    public function getUniqueFields(){

        $fields = array();

        foreach ($this->fields as $f){
            if ($f->isUnique() && !$f->isAlreadyManaged())
                $fields[] = $f->getName();
        }

        return $fields;

    }

    public function getConstFields(){

        $fields = array();

        foreach ($this->fields as $f){
            if ($f->isConst() && !$f->isAlreadyManaged())
                $fields[] = $f->getName();
        }

        return $fields;

    }

    public function getDefaultFields(){

        $fields = array();

        foreach ($this->fields as $f){
            if (!$f->isNull() && $f->getDefault() != null && !$f->isAlreadyManaged())
                $fields[$f->getName()] = $f->getDefault();
        }

        return $fields;

    }

    public function getDefaultNameFields(){

        $fields = array();

        foreach ($this->fields as $f){
            if (!$f->isNull() && $f->getDefault() != null && !$f->isAlreadyManaged())
                $fields[] = $f->getName();
        }

        return $fields;

    }

    public function getAlreadyManagedFields(){
        $fields = array();

        foreach ($this->fields as $f){
            if ($f->isAlreadyManaged())
                $fields[] = $f->getName();
        }

        return $fields;
    }

    public function getAddFields(){
        return array_diff($this->getFieldsName(), $this->getDefaultNameFields(), $this->getAlreadyManagedFields());
    }

    public function getUpdateFields(){
        return array_diff($this->getFieldsName(), $this->getConstFields(), $this->getAlreadyManagedFields());
    }

    public function getArrayFields(){

        $fields = array();

        foreach ($this->fields as $f){
            if ($f->isArray() && !$f->isAlreadyManaged())
                $fields[] = $f->getName();
        }

        return $fields;

    }

    public function getCriticFields(){

        $fields = array();

        foreach ($this->fields as $f){
            if ($f->isCritic() && !$f->isAlreadyManaged())
                $fields[] = $f->getName();
        }

        return $fields;

    }

    public function getStringFields(){

        $fields = array();

        foreach ($this->fields as $f){
            if ($f->getType() == FIELD::TYPE_STRING || $f->getType() == FIELD::TYPE_TEXT && !$f->isAlreadyManaged())
                $fields[] = $f->getName();
        }

        return $fields;

    }

    public function getFreeFields(){
        return array_diff($this->fields, $this->getCriticFields());
    }
    /*
     *
     * Fonction de sécurité
     *
     */

    // Fonction qui vérifie les champs lié à l'user
    public function verifyLinkedField($entity, $user){
        $linkedFields = $this->getLinkedFields();

        // Si il n'y a aucun champs lié, on renvoie true
        if (count($linkedFields) == 0){
            return true;
        }

        // Les champs sont classé dans un tableau 'entity' => 'user'
        foreach($linkedFields as $entityField => $userField)
        {
            $entityGetter = 'get'.ucfirst($entityField);
            $userGetter = 'get'.ucfirst($userField);

            // Si il s'agit d'un array ou pas
            if ($user->$userGetter() == $entity->$entityGetter() || (is_array($entity->$entityGetter()) && in_array($user->$userGetter(), $entity->$entityGetter())))
                return true;
        }

        // Si tous les champs sont différent, on renvoie false
        return false;
    }

    // Fonction qui gère les autorisations en fonctions du role et de l'action
    public function allowedUserFor($action, $user){

        // On récupère le nom de la constante à tester
        $able_action = 'ABLE_TO_'.strtoupper($action);

        // Si le role est dans la liste de roles possible, on renvoie vrai et on autorise
        if ($able_action == 'ABLE_TO_GET')
            return in_array($this->ABLE_TO_GET(), $user->getRoles());
        if ($able_action == 'ABLE_TO_ADD')
            return in_array($this->ABLE_TO_ADD(), $user->getRoles());
        if ($able_action == 'ABLE_TO_UPDATE')
            return in_array($this->ABLE_TO_UPDATE(), $user->getRoles());
        if ($able_action == 'ABLE_TO_DELETE')
            return in_array($this->ABLE_TO_DELETE(), $user->getRoles());
    }

    // Fonction qui renvoie les champs dont la valeur est déjà présente en BDD et dont cela pose problème : les champs "dupliqué"
    public function getDuplicatedField($entity, $isUpdate = false){
        // Logiquement il y a forcement le champs id qui ne peut pas être dupliqué en BDD
        // On ne vérifie pas que la liste n'est donc pas vide
        // Possibilité de l'améliorer en gérant cette possibilité

        // On récupère la liste des champs qui doivent être uniques
        $uniqueFields = $this->getUniqueFields();

        // Si il s'agit d'un update, on enleve les champs constant des champs unique
        if ($isUpdate)
        {
            $constField = $this->getConstFields();

            foreach ($uniqueFields as $k => $field)
            {
                if (in_array($field, $constField))
                    unset($uniqueFields[$k]);
            }
        }

        // Parametre de la requete servant à récupèrer les entités posant problème dans la BDD
        $param = "";

        // Définition des paramètres
        $stringFields = $this->getStringFields();
        foreach($uniqueFields as $field){

            // On ajoute le getter  à la liste
            $getter = 'get'.ucfirst($field);
            if ($entity->$getter() != null){

                if (strlen($param) == 0){
                    $param = 'WHERE';
                    if ($isUpdate){
                        $param = $param.' id <> '.$entity->getId(). ' AND (';
                    }
                }
                else{
                    $param = $param.' OR';
                }
                // Si c'est un string on met dans guillemets
                if (in_array($field, $stringFields))
                    $param = $param.' '.$field.' = \''.addslashes($entity->$getter()).'\'';
                else
                    $param = $param.' '.$field.' = '.addslashes($entity->$getter());
            }
        }

        if ($isUpdate && strlen($param) > 0){
            $param = $param .')';
        }

        // Récupération de la liste des entités provoquant une duplication des données
        $manager = new BaseManager(coPDO::get_db(), $this->ENTITY_NAME());
        $listAlreadyInDB = $manager->getWithParam($param);

        // Si il n'y a aucune entité qui pose problème, on renvoie null : liste vide
        if ($listAlreadyInDB === null)
            return null;

        // Liste contenant les champs dupliqués à retourner
        $listDupFieldToReturn = array();

        foreach ($listAlreadyInDB as $dupEnt){

            // On vérifie chaque champs pour chaque entité
            foreach ($uniqueFields as $key => $field){

                $getter = 'get'.ucfirst($field);


                //si c'est ue chaine de caractère on met tout en majuscule pour enlever la case
                if ((in_array($field, $stringFields) && strtoupper($dupEnt->$getter()) == strtoupper($entity->$getter())) || $dupEnt->$getter() == $entity->$getter()){

                    // Si il y a duplication, on ajoute le nom du champs à la liste
                    $listDupFieldToReturn[] = $field;

                    // on supprime le champs de la liste car il est déjà traité
                    unset($uniqueFields[$key]);
                }
            }
        }
        // On retourne la liste des champs dupliqués
        return $listDupFieldToReturn;
    }

    // Fonction qui vérifie que les données qui ne peuvent pas être modifiée, ne sont pas modifié
    public function verifyConstField($entity, $data, $listField = array()){

        // On récupère la liste des champs constant
        $constFields = array_merge($this->getConstFields(), $listField);

        foreach ($constFields as $field){

            // Si le champs est dans data, on vérifie qu'il n'a pas changé
            if (isset($data[$field])){

                $getter = 'get'.ucfirst($field);

                if ($data[$field] != $entity->$getter())
                    return false;
            }
        }
        // Si aucune constante n'est modifiée, on renvoie vrai
        return true;
    }
}
