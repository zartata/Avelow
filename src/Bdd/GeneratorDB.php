<?php
namespace src\Bdd;

use src\BDD\ConnexionPDO as coPDO;
use src\BDD\GeneratorSQL;
use src\Config\Config;
use src\Console\Colors;
use src\Manager\BaseManager;
use src\Entity\AwTable;

class GeneratorDB{

    private $db;

    private $oldFields;

    private $entities;
    private $fields;

    private $genSql;
    private $sqls;

    public function __construct(){
        $this->db = coPDO::get_db();

        $this->sqls = array();
        $this->oldFields = array();
        $this->fields = array();

        $this->entities = Config::getEntitiesInDB();

        $this->genSql = new GeneratorSQL();
    }

    private function initIsDone(){
        $q = $this->db->query('SHOW TABLES FROM '.Config::getDbName());
        $tables = $q->fetch(\PDO::FETCH_ASSOC);

        return is_array($tables) && in_array('awfield', $tables);
    }

    private function loadOldFields(){
        if (empty($this->oldFields)){
            // Récupère en base de données les anciens
            if ($this->initIsDone()){

                $q = $this->db->query($this->genSql->getFields());

                while ($donnees = $q->fetch(\PDO::FETCH_ASSOC))
                {
                    if (!empty($donnees['unique_id'])){
                        $donnees['table'] = $donnees['forTable'];
                        $donnees['unique'] = $donnees['isUnique'];
                        $donnees['default'] = $donnees['default_value'];
                        $donnees['null'] = $donnees['isNull'];

                        $this->oldFields[] = new AwField($donnees);
                    }
                }

            }
        }
    }

    private function getOldFields(){
        $this->loadOldFields();

        return $this->oldFields;
    }

    private function getOldFieldsByTable($name){
        $name = strtolower($name);

        $this->loadOldFields();

        // On renvoie que les fields de la classe
        $fields = array();

        foreach ($this->oldFields as $f){
            if ($f->tableIs($name)){
                $fields[] = $f;
            }
        }
        return $fields;
    }

    private function getFieldsByTable($name){

        $fields = Config::getConfig($name)->getFields();

        $this->fields = array_merge($this->fields, $fields);
        return $fields;

    }

    public function updateAwTable(){

        // On supprime le contenu de la table
        $q = $this->db->prepare($this->genSql->truncateTable('awtable'));
        $q->execute();

        // On ajoute chaque nom de table
        if (!empty($this->entities)){
            // On récup le manager
            $manager = new BaseManager(coPDO::get_db(), 'AwTable');

            foreach ($this->entities as $entity){

                $config = Config::getConfig($entity);
                $fields = $config->getFieldsName();

                $manager->add(new AwTable(['name' => strtolower($entity),'entity' => $entity, 'fields' => $fields]));
            }
        }
    }


    public function getAllSQL(){

        if (!$this->initIsDone()){
            $this->sqls[] = $this->genSql->initDB();
        }

        if (!empty($this->entities)){
            if (empty($this->getOldFields())){

                // On créé pour chaque entité
                foreach ($this->entities as $entity) {

                    $this->sqls[] = $this->genSql->createTable(strtolower($entity), $this->getFieldsByTable($entity));
                }

            }else{
                // Pour chaque entité
                foreach ($this->entities as $entity){

                    // On vérifie si la table existe ou pas
                    if (empty($this->getOldFieldsByTable($entity))){
                        // Si elle n'existe pas déjà, on créé
                        $this->sqls[] = $this->genSql->createTable(strtolower($entity), $this->getFieldsByTable($entity));
                    }else{
                        // On fait Add Modif Rename Drop
                        $this->sqls[] = $this->genSql->alterAdd(strtolower($entity), $this->getOldFieldsByTable($entity), $this->getFieldsByTable($entity));
                        $this->sqls[] = $this->genSql->alterModify(strtolower($entity), $this->getOldFieldsByTable($entity), $this->getFieldsByTable($entity));
                        $sqlsRename = $this->genSql->alterRename(strtolower($entity), $this->getOldFieldsByTable($entity), $this->getFieldsByTable($entity));
                        if (!empty($sqlsRename)) {foreach ($sqlsRename as $sqlRename) {$this->sqls[] = $sqlRename;}}
                        $this->sqls[] = $this->genSql->alterDrop(strtolower($entity), $this->getOldFieldsByTable($entity), $this->getFieldsByTable($entity));
                    }
                }
            }
        }
    }

    public function generate(){

        // On génère le sql
        $this->getAllSQL();

        // Récupèration du nombre de requêtes
        $sizeSqls = 0;
        if (sizeof($this->sqls) > 0){
            foreach ($this->sqls as $sql) {
                if ($sql !== null)
                    $sizeSqls++;
            }
        }

        echo '--------------------------------------------------


'.$sizeSqls.' requetes a executer.


-------------------------
';

        if (!empty($this->sqls)){
            // On execute chaque requete sql une à une
            foreach ($this->sqls as $sql){
                if ($sql !== null){

                    echo 'Code SQL a executer :
';

                    echo $sql.'
';

                    $q = $this->db->prepare($sql);
                    if ($q->execute()){
                        echo 'Requete executee avec succes.
-----
';
                    }else{
                        echo 'Echec de la requete.
-----
';
                    }
                }
            }
        }
        // Mise à jour de la table awfield
        // On la vide puis on la rempli
        $q = $this->db->prepare($this->genSql->truncateTable('awfield'));
        $q->execute();
        // puis on ajoute tous les champs
        $req = 'INSERT INTO awfield SET unique_id = :unique_id, name = :name, id_field = :id_field, forTable = :forTable, type = :type, isNull = :isNull, default_value = :default_value, isUnique = :isUnique, array = :array, critic = :critic, const = :const, linked = :linked';

        foreach($this->fields as $f){
            $q = $this->db->prepare($req);

            $q->bindValue(':unique_id',$f->getUnique_id());
            $q->bindValue(':name',$f->getName());
            $q->bindValue(':id_field',$f->getId_field());
            $q->bindValue(':forTable',$f->getTable());
            $q->bindValue(':type',$f->getType());
            $q->bindValue(':isNull',$f->isNull());
            $q->bindValue(':default_value',$f->getDefault());
            $q->bindValue(':isUnique',$f->isUnique());
            $q->bindValue(':array',$f->isArray());
            $q->bindValue(':critic',$f->isCritic());
            $q->bindValue(':const',$f->isConst());
            $q->bindValue(':linked',$f->isLinked());

            $q->execute();
        }

        echo 'Mise à jour de AwTable.
------------
';
        $this->updateAwTable();
        echo 'Mise à jour terminee.
------------
';
    }
}
