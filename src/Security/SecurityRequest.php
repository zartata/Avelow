<?php
namespace src\Security;

use src\Manager\BaseManager;
use src\Bdd\ConnexionPDO as coPDO;
use src\Config\Config;

class SecurityRequest
{
    public static function getRequestUser($withJoin = false)
    {
        $userEntityName = Config::getUserEntity();
        $configUser = Config::getConfig($userEntityName);
        $userClass = $configUser->CLASS_NAME();

        if (isset($_GET['signature']) && isset($_GET['tmp']) && isset($_GET['user']))
        {
            $tmp = $_GET['tmp'];
            $user_pseudo = $_GET['user'];
            $signature = $_GET['signature'];
            $url = Config::getApp()->request->getResourceUri();

            $urlRequest = $url.'?tmp='.$tmp.'&user='.$user_pseudo.'&signature='.$signature;

            // Duréé du timestamp, 60 secondes
            if (!($tmp > (time() - 60) && $tmp <= time()))
            {
                $data = null;
                $error = 'Le délai de la requête est dépassé.';
                $status = 401;

                JsonOutput::render($status, $data, $error);
            }

            if ($user_pseudo == 'admin'){
                $user = new $userClass(array());
                $user->beAdmin();
            }else{
                // Récupération du password en fonction du nom de l'user
                $manager = new BaseManager(coPDO::get_db(), $userEntityName);
                $user = $manager->getUniqueWithParam('WHERE pseudo = \''.$user_pseudo.'\'', $withJoin, true);

                if ($user == null || $user->isDeleted())
                {
                    $userToReturn = new BasicUser(array());
                    $userToReturn->beAnonyme();
                    return $userToReturn;
                }
            }

            // Création de l'url qui devrait être obtenu
            $urlToVerify = $url.'?tmp='.$tmp.'&user='.$user_pseudo;
            $hash = hash_hmac('sha256', $user_pseudo, $user->getPassword());
            $sign = hash_hmac('sha256', $urlToVerify, $hash);
            $urlToVerify = $urlToVerify.'&signature='.$sign;


            // On compare les deux URLs
            // Si elles sont identiques, on renvoie le user
            // Sinon on renvoie l'anonyme
            if ($urlToVerify == $urlRequest)
            {
                return $user;
            }
            else
            {
                $userToReturn = new $userClass(array());
                $userToReturn->beAnonyme();
                return $userToReturn;
            }


        }else{
            $userToReturn = new $userClass(array());
            $userToReturn->beAnonyme();
            return $userToReturn;
        }



    }
}
