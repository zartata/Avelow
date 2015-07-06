<?php
namespace src\Entity;

use src\Config\Config;

class BasicUser extends BaseEntity
{
    // Unique (pas oublier id)
    protected $pseudo = null;
    protected $password = null;
    protected $roles = [self::ROLE_ANONYME];

    // Différent Role de BasicUser possible
    const ROLE_ALL = 'ROLE_ALL';
    const ROLE_ANONYME = 'ROLE_ANONYME';
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    // Constructeur qui initialise l'user
    public function __construct($donnees){
        parent::__construct($donnees);
    }

    public function isGranted($role){
        return in_array($role, $this->roles) || $role == self::ROLE_ALL;
    }

    public function isAdmin(){
        return $this->isGranted(self::ROLE_ADMIN);
    }

    public function beAnonyme(){
        $this->roles = [self::ROLE_ANONYME];
    }
    public function beAdmin(){
        $this->roles = [self::ROLE_ADMIN];
        $this->pseudo = 'admin';
        $this->password = Config::getSuperAdminPassword();
    }

    public function setPseudo($value){
        if ($value == 'admin')
            throw new \Exception('BasicUser : setPseudo : admin is not aviable.');

        $this->pseudo = $value;
        return $this;
    }

    public function setPassword($value){
        $this->password = $value;
        return $this;
    }

    public function setRoles($value){

        foreach ($value as $v) {
            if (!in_array($v, [self::ROLE_ANONYME, self::ROLE_USER, self::ROLE_ADMIN]))
                throw new Exception("BasicUser : setRoles : no such role");
        }

        $this->roles = $value;
        return $this;
    }

    public function getPseudo(){ return $this->pseudo; }
    public function getPassword(){ return $this->password; }
    public function getRoles(){ return $this->roles; }
}
