<?php
namespace src\Bdd;

class GeneratorSQL{


    /**
    * DB
    */
    public function createDB($dbName){
        return "CREATE DATABASE IF NOT EXISTS ".$dbName;
    }

    public function dropDB($dbName){
        return "DROP DATABASE IF EXISTS".$dbName;
    }

    // SQL pour la création de la table field
    public function initDB(){
        return $this->createFields();
    }

    public function createFields(){
        return "CREATE TABLE awfield (
            unique_id VARCHAR(255) NOT NULL PRIMARY KEY,
            type VARCHAR(30) NOT NULL,
            name VARCHAR(255) NOT NULL,
            id_field VARCHAR(255) NOT NULL,
            default_value VARCHAR(255) NULL,
            isNull TINYINT(1) NOT NULL,
            isUnique TINYINT(1) NOT NULL,
            critic TINYINT(1) NOT NULL,
            array TINYINT(1) NOT NULL,
            const TINYINT(1) NOT NULL,
            linked TINYINT(1) NOT NULL,
            forTable VARCHAR(255) NOT NULL
        );";
    }

    public function getFields(){
        return 'SELECT * FROM awfield';
    }

    /**
    * Table
    */
    public function truncateTable($tableName){
        return "TRUNCATE TABLE ".$tableName;
    }

    public function dropTable($tableName){
        return "DROP TABLE ".$tableName;
    }

    public function renameTable($tableName, $newName){
        return 'RENAME TABLE '.$tableName.' TO '.$newName;
    }

    public function createTable($table, $fields){

        $sql = "CREATE TABLE ".$table." (";

        foreach ($fields as $field){
            if ($field->getName() == 'id'){
                $sql = $sql." ".$field->toSQL()." PRIMARY KEY AUTO_INCREMENT,";
            }elseif ($field->getName() == 'deleted'){
                $sql = $sql." ".$field->toSQL()." DEFAULT false,";
            }else{
                $sql = $sql." ".$field->toSQL().",";
            }
        }
        $sql = substr($sql,0,-1);


        $sql = $sql." );";

        return $sql;
    }

    public function alterAdd($table, $oldFields, $newFields){
        // vérification entre ancien et nouveau
        // si le nom est pas déjà present, on ajoute

        $sql = "ALTER TABLE ".$table." ADD (";

        $oneAdded = false;

        foreach($newFields as $newField){
            $found = false;
            // Si ce unique id n'est pas dans le tableau d'ancien
            foreach ($oldFields as $oldField){
                if ($newField->hasSameUniqueId($oldField)){
                    $found = true;
                }
            }

            if (!$found){
                // On dit qu'on en a trouvé au moins 1
                $oneAdded = true;

                // On ajoute au sql
                $sql = $sql." ".$newField->toSQL().",";

            }
        }

        if (!$oneAdded){
            return null;
        }

        $sql = substr($sql,0,-1);
        $sql = $sql.");";

        return $sql;
    }

    public function alterModify($table, $oldFields, $newFields){
        // vérification entre ancien et nouveau
        // juste vérification des noms des colonnes et si le meme avec données différente, changement

        $sql = "ALTER TABLE ".$table;

        $oneModified = false;

        foreach($newFields as $newField){

            // On récupère le field qui a le même id, on compare et on récup le sql
            foreach($oldFields as $oldField){

                if ($newField->hasSameUniqueId($oldField)){

                    // Si ce n'est pas le même type
                    if (!$newField->hasSameType($oldField)){
                        // On en a trouvé un
                        $oneModified = true;
                        $sql = $sql." MODIFY ".$newField->toSQLWithOldName($oldField->getName()).",";
                    }

                }
            }
        }

        if (!$oneModified){
            return null;
        }

        $sql = substr($sql,0,-1);
        $sql = $sql.";";

        return $sql;
    }

    public function alterDrop($table, $oldFields, $newFields){
        // vérification entre ancien et nouveau
        // juste vérification des noms des colonnes et si le meme avec données différente, changement

        $sql = "ALTER TABLE ".$table;


        $oneDeleted = false;

        foreach($oldFields as $oldField){
            $found = false;
            // Si ce unique id n'est pas dans le tableau d'ancien
            foreach ($newFields as $newField){
                if ($oldField->hasSameUniqueId($newField)){
                    $found = true;
                }
            }

            if (!$found){
                // On dit qu'on en a trouvé au moins 1
                $oneDeleted = true;

                // On ajoute au sql
                $sql = $sql." DROP ".$oldField->getName().",";
            }
        }

        if (!$oneDeleted){
            return null;
        }

        $sql = substr($sql,0,-1);
        $sql = $sql.";";
        return $sql;
    }

    public function alterRename($table, $oldFields, $newFields){

        $sqls = array();

        // retourne un tableau de commande car on ne peut pas l'executer en une requete
        foreach($oldFields as $oldField){
            foreach ($newFields as $newField){
                if ($oldField->hasSameUniqueId($newField) && !$oldField->hasSameName($newField)){
                    $sqls[] = "ALTER TABLE ".$table." CHANGE ".$oldField->getName()." ".$newField->toSQL();
                }
            }
        }

        if (empty($sqls)){
            return null;
        }

        return $sqls;
    }

    public function renameColumn($table, $oldName, $newName){
        return "ALTER TABLE ".$table." RENAME COLUMN ".$oldName." TO ".$newName;
    }
}
