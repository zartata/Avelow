<?php
namespace src\Entity;

use src\Bdd\ConnexionPDO as coPDO;
use src\Manager\BaseManager;

abstract class BaseEntity
{
    // L'id qui est obligatoire sur chaque entité
    // type : int
    protected $id = null;

    // Mise en place du soft deleted
    protected $deleted = false;
    protected $deleted_at = null;

    // Gestion des dates directement depuis le manager
    protected $created_at = null;
    protected $updated_at = null;

    /*
     *
     * Fonction d'utilité basique
     *
     */

    // Constructeur de BaseEntity
    // Initialise l'entité en l'hydratant
    // param : array $donnees
    public function __construct(array $donnees){
        $this->hydrate($donnees);
    }

    // Fonction qui hydrate l'entité
    // param : array $donnees
    // return : void
    protected function hydrate(array $donnees){
        foreach($donnees as $key => $value)
        {
            $method = 'set'.ucfirst($key);

            if (method_exists($this, $method))
            {
                $this->$method($value);
            }
        }
    }

    // Fonction qui dit si une entité est nouvelle ou pas
    public function isNew() { return $this->id === null; }

    // Transforme un objet en json seulement sur ses variables non static
    public function toArray(){
        $vars = get_object_vars($this);

        foreach ($vars as $key => $var)
        {
            if (is_object($var))
            {
                $vars[$key] = $var->toArray();
            }
            if (is_array($var))
            {
                foreach ($var as $k => $v)
                {
                    if (is_object($v))
                    {
                        $var[$k] = $v->toArray();
                    }
                }
                $vars[$key] = $var;
            }
        }

        return $vars;
    }

    // Met à jour l'entité avec des données
    public function MaJEntity($donnees){
        $this->hydrate($donnees);
    }

    /*
     *
     *  Fonction de reset pour les champs critiques
     *
     */

     public function reset($field){
         $this->$field = null;
     }

    /*
     *
     *  GETTERS/SETTERS
     *
     */

    public function getId() { return $this->id; }
    public function setId($id){
        $this->id = $id;
        return $this;
    }

    public function isDeleted(){ return $this->deleted; }
    public function getDeleted(){ return $this->deleted; }
    public function setDeleted($deleted){
        $this->deleted = $deleted;
    }

    public function getCreated_at(){ return $this->created_at; }
    public function getUpdated_at(){ return $this->updated_at; }
    public function getDeleted_at(){ return $this->deleted_at; }
    public function setCreated_at($value){ $this->created_at = $value; return $this; }
    public function setUpdated_at($value){ $this->updated_at = $value; return $this; }
    public function setDeleted_at($value){ $this->deleted_at = $value; return $this; }

}
