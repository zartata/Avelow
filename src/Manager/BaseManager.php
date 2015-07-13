<?php
namespace src\Manager;

use src\Config\Config;

class BaseManager
{
    // Nom de la classe et donc de la table sur laquelle on agit
    protected $classe;
    protected $table;
    protected $entityName;
    protected $entityClass;
    protected $db;

    public function __construct($db, $classe)
    {
        $this->setDb($db);
        $this->classe = $classe;
        $this->config = Config::getConfig($classe);


        $this->table = $this->config->TABLE();
        $this->entityName = $this->config->ENTITY_NAME();
        $this->entityClass = $this->config->CLASS_NAME();
    }

    public function setDb($db) { $this->db = $db; return $this;}


    // Fonction qui ajoute une nouvelle entité en bdd
    // Sans ajouter les contacts et les dettes
    // Car l'utilisateur n'est pas lié à ses dettes mais c'est l'inverse
    public function add($e){
        if (!$this->config->isValid($e))
            throw new \Exception('Manager : add : entity is not valid.');

        $req = 'INSERT INTO '.$this->table.' SET created_at = NOW(),';

        // On récupère les champs add
        $fieldsToAdd = $this->config->getAddFields();
        $fieldsDefault = $this->config->getDefaultFields();

        // On ajoute chaque champs dans la requete
        foreach ($fieldsToAdd as $field){
            $req = $req.' '.$field.' = :'.$field.',';
        }
        foreach ($fieldsDefault as $field => $value){
            $req = $req.' '.$field.' = :'.$field.',';
        }

        // On enlève la dernière virgule de trop dans la requete
        $req = substr($req,0,-1);

        // On prépare la requette avant de lui donner les valeurs
        $q = $this->db->prepare($req);

        // On récup les array
        $serializedFields = $this->config->getArrayFields();

        // On ajoute attribut chaque valeur à chaque champs
        foreach ($fieldsToAdd as $field){
            $getter = 'get'.ucfirst($field);
            if (in_array($field, $serializedFields)){
                $q->bindValue(':'.$field, serialize($e->$getter()));
            }else{
                $q->bindValue(':'.$field, $e->$getter());
            }
        }
        foreach ($fieldsDefault as $field => $value){
            if (in_array($field, $serializedFields)){
                $q->bindValue(':'.$field, serialize($value));
            }else{
                $q->bindValue(':'.$field, $value);
            }
        }

        // On execute la requete
        $q->execute();
        // Et on attribut l'id attribué à la nouvelle entité
        $e->setId($this->db->lastInsertId());
    }

    // Fonction très proche de add
    // Met à jour une entité déjà présente en bdd
    // Si ce n'est pas le cas, une exception est lancée
    // Renvoie vrai si c'est bon, false si erreur
    public function update($e){

        if (!$this->config->isValid($e))
            throw new \Exception('Manager : update : entity is not valid.');


        $id = (int) $e->getId();
        if (!is_int($id))
            throw new \Exception('Il faut un id pour mettre à jour un '.$this->entityName);


        $req = 'UPDATE '.$this->table.' SET updated_at = NOW(),';

        // On récupère les champs présents dans la base de données
        $fieldsToUpdate = $this->config->getUpdateFields();

        // On ajoute chaque champs dans la requete
        foreach ($fieldsToUpdate as $field)
        {
            $req = $req.' '.$field.' = :'.$field.',';
        }

        // On enlève la dernière virgule de trop dans la requete
        $req = substr($req,0,-1);
        $req = $req.' WHERE id = :id';
        // On prépare la requette avant de lui donner les valeurs
        $q = $this->db->prepare($req);

        // On attribut forcement l'id vu qu'il s'agit d'une mise à jour
        $q->bindValue(':id', $e->getId(), \PDO::PARAM_INT);

        // On récup les array
        $serializedFields = $this->config->getArrayFields();

        // On ajoute attribut chaque valeur à chaque champs
        foreach ($fieldsToUpdate as $field)
        {
            $getter = 'get'.ucfirst($field);
            if (in_array($field, $serializedFields)){
                $q->bindValue(':'.$field, serialize($e->$getter()));
            }else{
                $q->bindValue(':'.$field, $e->$getter());
            }
        }


        return $q->execute();
    }


    // Fonction qui "supprime" une entité
    public function delete($id){
        // soft deleted
        // On récupère la liste des champs à mettre en deleted
        $req = 'UPDATE '.$this->table.' SET deleted = true, deleted_at = NOW(),';

        // On prépare la requette avant de lui donner les valeurs
        $q = $this->db->prepare($req);

        $q->execute();
    }

    // get avec type et valeur du type
    public function get($type, $value, $withJoin = false, $withJoinLevel = 1, $withCritic = false){
        // On créé le parametre
        if (is_string($value)){
            $param = 'WHERE '.$type.' = "'.$value.'"';
        }else{
            $param = 'WHERE '.$type.' = '.$value;
        }
        // On récupère l'entité et on la retourne
        $liste = $this->getWithParam($param, $withJoin, $withJoinLevel, $withCritic);
        return $liste[0];
    }

    public function getWithoutParam(){
        return $this->getWithParam(null, $withJoin = false, $withJoinLevel = 1, $withCritic = false, $free = false);
    }

    // Renvoie une entité correspondant à l'id
    public function getWithParam($param, $withJoin = false, $withJoinLevel = 1, $withCritic = false, $free = false){

        // On récupère les différents champs
        $FIELD_in_db = $this->config->getFieldsName();
        $FIELD_critic = $this->config->getCriticFields();
        $FIELD_free = $this->config->getFreeFields();
        // Champs à récupérer
        $FIELD_to_get = array();

        if ($free){
            $FIELD_to_get = $FIELD_free;
        }else{
            if ($withCritic){
                $FIELD_to_get = $FIELD_in_db;
            }else{
                $FIELD_to_get = array_diff($FIELD_in_db, $FIELD_critic);
            }
        }

        if ($FIELD_to_get == []){
            return array();
        }

        // On créé le début de la requete avec les champs à récupérer
        $req = 'SELECT';

        // On ajoute chaque champs dans la requete
        foreach ($FIELD_to_get as $field)
        {
            $req = $req.' '.$field.',';
        }

        // On enlève la dernière virgule de trop dans la requete
        $req = substr($req,0,-1);
        // On ajoute la table
        $req = $req.' FROM '.$this->table;

        // on ajoute les params ils sont différents de null
        if ($param != null)
            $req = $req.' '.$param;

        // On récupère les champs correspondants à l'entité voulue dans la table (plusieurs entités possibles)
        $q = $this->db->query($req);

        $listeResults = array();
        while ($donnees = $q->fetch(\PDO::FETCH_ASSOC))
        {
            // On deserialize les tableaux
            // On récupère la liste des tableaux de la classe
            $serializedFields = $this->config->getArrayFields();
            // Si le champs est dans les données, on le deserialize
            foreach ($serializedFields as $field){
                // Si le champs est présent dans les données, on le deserialize
                if (isset($donnees[$field]))
                    $donnees[$field] = unserialize($donnees[$field]);
            }

            // On créé l'entité
            $listeResults[] = new $this->entityClass($donnees);
        }



        if ($withJoin && $withJoinLevel > 0){
            // On diminue le niveau de jointure de 1
            $withJoinLevel--;

            // On a la liste de tous les résultats :
            //      - liste vide si aucun résultat
            //      - liste avec une seul entré si c'est un résultat unique
            //      - liste avec plusieurs résultats

            // On ajoute les relations possibles si withJoin = true
            // La liste de tous les éléments à récup en bdd
            $toGet = array();

            // Cas ONE_TO_ONE
            // On commence par ONE_TO_ONE

            // On récup le contenu de ONE_TO_ONE
            $OneToOnes = $this->config->ONE_TO_ONE();
            // On créé deux listes de getters et setetrs pour One To One
            $otoGetSets = array();

            // On utilise un itérateur :
            $i = 0;

            foreach($OneToOnes as $OneToOne){
                // On stocke le getter, le setter et la classe
                $otoGetSets[$i]['getter'] = 'get'.ucfirst($OneToOne[0].'_'.$OneToOne[1]);
                $otoGetSets[$i]['setter'] = 'set'.ucfirst($OneToOne[0]);
                $otoGetSets[$i]['classe'] = $OneToOne[2];
                $i++;
            }

            // One To Many
            $OneToManys = $this->config->ONE_TO_MANY();
            // On créé deux listes de getters et setetrs pour One To Many
            $otmGetSets = array();
            $i = 0;
            foreach($OneToManys as $OneToMany){
                // On stocke le getter, le setter et la classe
                $otmGetSets[$i]['getter'] = 'get'.ucfirst($OneToMany[0].'_'.$OneToMany[1]);
                $otmGetSets[$i]['setter'] = 'set'.ucfirst($OneToMany[0]);
                $otmGetSets[$i]['classe'] = $OneToMany[2];
                $i++;
            }

            // On récup le contenu de MANY_TO_ONE
            $ManyToOnes = $this->config->MANY_TO_ONE();
            // Fonctionne comme le one to one, donc on l'ajoute dans la même liste pour gagner du temps
            $i = 0;
            foreach($ManyToOnes as $ManyToOne){
                // On stocke le getter, le setter et la classe
                $otoGetSets[$i]['getter'] = 'get'.ucfirst($ManyToOne[0].'_'.$ManyToOne[1]);
                $otoGetSets[$i]['setter'] = 'set'.ucfirst($ManyToOne[0]);
                $otoGetSets[$i]['classe'] = $ManyToOne[2];
                $i++;
            }

            // Many To Many
            // Fonctionne comme le one to many
            $ManyToManys = $this->config->MANY_TO_MANY();
            // On créé deux listes de getters et setetrs pour One To Many
            $i = 0;
            foreach($ManyToManys as $ManyToMany){
                // On stocke le getter, le setter et la classe
                $otmGetSets[$i]['getter'] = 'get'.ucfirst($ManyToMany[0].'_'.$ManyToMany[1]);
                $otmGetSets[$i]['setter'] = 'set'.ucfirst($ManyToMany[0]);
                $otmGetSets[$i]['classe'] = $ManyToMany[2];
                $i++;
            }


            foreach ($listeResults as $entity){
                // On ajotue l'id à la liste qu'il faut récup en fonction de sa classe
                // Pour chaque getter
                // One To One
                foreach ($otoGetSets as $otoGetSet){
                    if (!array_key_exists($otoGetSet['classe'], $toGet)){
                        $toGet[$otoGetSet['classe']] = null;
                    }
                    $toGet[$otoGetSet['classe']][] = $entity->$otoGetSet['getter']();
                }
                // Many To One
                foreach ($otmGetSets as $otmGetSet){
                    if (!array_key_exists($otmGetSet['classe'], $toGet)){
                        $toGet[$otmGetSet['classe']] = array();
                    }
                    if (is_array($entity->$otmGetSet['getter']())){
                        $toGet[$otmGetSet['classe']] = array_merge($toGet[$otmGetSet['classe']], $entity->$otmGetSet['getter']());
                    }
                }

                ///////
                //  Ajouter les autres id à charger suivant les différentes relations possibles
                ///////
            }

            ////////////////////////////////////////////////////////////////
            // On récupère toutes les entités liées aux id stockés
            // Tableau des différentes managers
            $managers = array();
            $listeRelative = array();
            foreach ($toGet as $classe => $ids){
                $listeRelative[$classe] = [];
                if (!empty($ids))
                {
                    $managers[$classe] = new BaseManager($this->db, $classe);

                    // Parametre pour récupérer toutes les entités liées aux id
                    $param = 'WHERE id IN (';
                    foreach ($ids as $id){
                        if ($id != null){
                            $param = $param.' '.$id.',';
                        }
                    }
                    // On enlève la dernière virgule de trop dans la requete
                    $param = substr($param,0,-1);
                    $param = $param.')';

                    // On récup toutes les entités que l'on stockes dans un tableau
                    $listeRelative[$classe] = $managers[$classe]->getWithParam($param, $withJoin, $withJoinLevel);
                }
            }

            ////////
            // Recup depuis toGet
            ////////////////////////////////////////////////////////////////////////



            // On attribut ensuite ces entités aux entités qui lui sont liées
            foreach ($listeResults as $entity){
                // ONE TO ONE
                foreach ($otoGetSets as $otoGetSet){

                    // On récup les id liés à cette entité
                    $id = $entity->$otoGetSet['getter']();

                    // On récup les entités ayant ces id précisement dans la liste
                    $entitySetter = null;
                    foreach ($listeRelative[$otoGetSet['classe']] as $entForSetter){
                        if ($entForSetter->getId() == $id)
                            $entitySetter = $entForSetter;
                    }
                    if ($entitySetter != null){
                        $entity->$otoGetSet['setter']($entitySetter);
                    }
                }


                // Faire ajout One To Many
                foreach ($otmGetSets as $otmGetSet){

                    // On récup les id liés à cette entité
                    $ids = $entity->$otmGetSet['getter']();

                    // On récup les entités ayant ces id précisement dans la liste
                    $entitySetter = array();
                    foreach ($listeRelative[$otmGetSet['classe']] as $entForSetter){
                        if (in_array($entForSetter->getId(), $ids))
                            $entitySetter[] = $entForSetter;
                    }
                    if (count($entitySetter) > 0){
                        $entity->$otmGetSet['setter']($entitySetter);
                    }
                }

            }
        }

        return $listeResults;
    }

    public function getUniqueWithParam($param, $withJoin = false, $withCritic = false, $free = false){

        $liste = $this->getWithParam($param, $withJoin, $withCritic, $free);

        if (empty($liste))
            return null;
        else
            return $liste[0];
    }
}
