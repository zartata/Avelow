<?php
namespace src\Controller;

use src\Config\Config;

use src\Manager\BaseManager;
use src\Bdd\ConnexionPDO as coPDO;

use src\Entity\BasicUser;

use src\Output\Error404;
use src\Output\ErrorNotAllowed;
use src\Output\ErrorInternal;
use src\Output\ErrorDuplicatedFields;
use src\Output\ErrorFile;
use src\Output\Success200;
use src\Output\SuccessAdd;
use src\Output\SuccessNoContent;

class Controller{

    public static function getBool($param){
        return (isset($_GET[$param]) && $_GET[$param]) ? $_GET[$param] : false;
    }

    public static function getParam($param){
        return (isset($_GET[$param]) && $_GET[$param]) ? $_GET[$param] : null;
    }

    public static function getDataUrl(){
        $request_body = file_get_contents('php://input');
        return json_decode($request_body, true);
    }

    public static function getAwTable($table){
        // On vérifie si la table corresponds à une existante
        $tableInDB = Config::getEntitiesInDBInLowerCase();

        if (in_array(strtolower($table), $tableInDB)){
            // On récupère la table
            $manager = new  BaseManager(coPDO::get_db(), 'AwTable');
            $table = $manager->get('name', '\''.strtolower($table).'\'');

            // On revoie la table
            $response = new Success200($table);
            $response->render();
        }else{
            $response = new Error404('La table '.$table.' n\'existe pas en BDD.');
            $response->render();
        }

    }

    // Gestion des fichiers
    public static function addFile($user, $file){
        // Code from openclassrooms.com
        if (isset($file['file']) AND $file['file']['error'] == 0)
        {
            // Testons si le fichier n'est pas trop gros 10MO
            if ($file['file']['size'] <= 10000000)
            {
                    // Testons si l'extension est autorisée
                    $infosfichier = pathinfo($file['file']['name']);
                    $extension_upload = $infosfichier['extension'];
                    $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'pdf');
                    if (in_array($extension_upload, $extensions_autorisees))
                    {
                        if ($extension_upload == 'pdf'){
                            $type = 'pdf';
                        }else{
                            $type = 'image';
                        }
                        // On ajoute l'entity file
                        $name = ''.time().'-'.$file['file']['size'].'-'.$file['file']['name'];

                        $data = [
                            'type' => $type,
                            'url' => '/assets/img/uploads/'.$name,
                            'name' => $name,
                            'extension' => $extension_upload,
                            'size' => $file['file']['size'],
                            'owner_id' => 1,
                            'file' => $file
                        ];

                        // On ajoute
                        self::add($user, 'AwFile', $data);
                    }
                    $response = new ErrorFile("Extension non acceptée. (seulement jpg, jpeg, gif, png, pdf)");
                    $response->render();
            }
            $response = new ErrorFile("Fichier trop gros.");
            $response->render();
        }
        $response = new ErrorFile("Erreur lors de l'envoie du fichier.");
        $response->render();
    }

    public static function getAdmin($entityClass, $id){
        // On récupère la config
        $config = Config::getConfig($entityClass);

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        // Récupération de l'entity suivant l'id sans les entité lié
        $entity = $manager->get('id', $id, Controller::getBool('withJoin'), Controller::getParam('withJoinLevel'), Controller::getBool('withCritic'), Controller::getBool('free'));

        // Si il n'y a pas d'entité
        if ($entity == null){
            $response = new Error404('Il n\'y a aucun(e) '.$config::ENTITY_NAME().' pour l\'id '.$id);
            $response->render();
        }

        // Succès
        $response = new Success200($entity);
        $response->render();
    }

    public static function addAdmin($entityClass, $dataUrl){
        // On récupère la config
        $config = Config::getConfig($entityClass);
        $className = $config->CLASS_NAME();

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        $entity = new $className($dataUrl);

        // On teste la duplication des champs en BDD    $isUpdate = true
        $listDupField = $config->getDuplicatedField($entity);
        if (!empty($listDupField)){
            $response = new ErrorDuplicatedFields('Des champs uniques sont déjà présents en base de données.', $listDupField);
            $response->render();
        }

        // Tout est ok
        $config->beforeAdd($entity, $dataUrl);
        $manager->add($entity);
        $config->afterAdd($entity, $dataUrl);

        // Succès
        $response = new Success200($entity);
        $response->render();
    }

    public static function updateAdmin($entityClass, $id, $dataUrl){
        // On récupère la config
        $config = Config::getConfig($entityClass);
        $className = $config->CLASS_NAME();

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        // On récupère l'entité
        $entity = $manager->get('id', $id);

        if ($entity == null){
            $response = new Error404('Il n\'y a aucun(e) '.$config::ENTITY_NAME().' pour l\'id '.$id);
            $response->render();
        }

        // On clone l'entité avant de la mettre à jour l'entité pour le before update
        $entityBeforeUpdate = clone $entity;
        $entity->MaJEntity($dataURL);

        // On teste la duplication des champs en BDD    $isUpdate = true
        $listDupField = $config->getDuplicatedField($entity, true);
        if (!empty($listDupField)){
            $response = new ErrorDuplicatedFields('Des champs uniques sont déjà présents en base de données.', $listDupField);
            $response->render();
        }

        // Mise à jour de l'entité
        $config->beforeUpdate($entityBeforeUpdate, $dataUrl);
        $manager->update($entity);
        $config->afterUpdate($entity, $dataUrl);

        // Succès
        $response = new Success200($entity);
        $response->render();
    }

    public static function deleteAdmin($entityClass, $id){
        $config = Config::getConfig($entityClass);

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        // On récupère l'entité
        $entity = $manager->get('id', $id);

        // Si il n'y a pas d'entité
        if ($entity == null){
            $response = new Error404('Il n\'y a aucun(e) '.$config::ENTITY_NAME().' pour l\'id '.$id);
            $response->render();
        }

        $config->beforeDelete($entity);
        $manager->delete($id);
        $config->afterDelete($entity);

        $response = new SuccessNoContent();
        $response->render();
    }

    public static function getList($user, $entityClass){
        // On récupère la config
        $config = Config::getConfig($entityClass);

        // Pour récupérer une liste entière, il faut être admin
        if (!$user->isGranted(BasicUser::ROLE_ADMIN) && !(Controller::getBool('free'))){
            $response = new ErrorNotAllowed('Vous n\'êtes pas autorisé à récupérer une liste d\'entités non free.');
            $response->render();
        }

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        // Récupération de l'entity suivant l'id sans les entité lié
        $entities = $manager->getWithParam(Controller::getParam('param'), Controller::getBool('withJoin'), Controller::getParam('withJoinLevel'), Controller::getBool('withCritic'), Controller::getBool('free'));

        if (empty($entities))
        {
            $response = new Error404('Il n\'y a pas de '.$entityClass.' dans la base de données.');
            $response->render();
        }

        // Succès
        $response = new Success200($entities);
        $response->render();
    }

    public static function get($user, $entityClass, $id){
        // On récupère la config
        $config = Config::getConfig($entityClass);

        // On vérifie si le role est le bon
        if (!$config->allowedUserFor('get', $user)){
            $response = new ErrorNotAllowed('Vous n\'êtes pas autorisé à récupérer un(e) '.$config::ENTITY_NAME().'.');
            $response->render();
        }

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        // Récupération de l'entity suivant l'id sans les entité lié
        $entity = $manager->get('id', $id, Controller::getBool('withJoin'), Controller::getParam('withJoinLevel'), Controller::getBool('withCritic'), Controller::getBool('free'));

        // Si il n'y a pas d'entité
        if ($entity == null){
            $response = new Error404('Il n\'y a aucun(e) '.$config::ENTITY_NAME().' pour l\'id '.$id);
            $response->render();
        }

        // On vérifie si l'user est lié à l'entité
        if (!$config->verifyLinkedField($entity, $user) || Controller::getBool('free')){
            $response = new ErrorNotAllowed('Cette entité '.$config::ENTITY_NAME().' ne vous concerne pas, vous ne pouvez pas la récupérer.');
            $response->render();
        }

        // On vérifie les conditions spécifique
        if (!$config->verifyConditionsToGet($entity, $user) || Controller::getBool('free')){
            $response = new ErrorNotAllowed('Cette entité '.$config::ENTITY_NAME().' n\'est pas récupérable.');
            $response->render();
        }

        // Succès
        $response = new Success200($entity);
        $response->render();
    }

    public static function add($user, $entityClass, $dataUrl){
        // On récupère la config
        $config = Config::getConfig($entityClass);
        $className = $config->CLASS_NAME();

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        // On vérifie si le role est le bon
        if (!$config->allowedUserFor('add', $user)){
            $response = new ErrorNotAllowed('Vous n\'êtes pas autorisé à ajouter ce(tte) '.$config->ENTITY_NAME().'.');
            $response->render();
        }

        $entity = new $className($dataUrl);

        // On vérifie si l'user est lié à l'entité
        if (!$config->verifyLinkedField($entity, $user)){
            $response = new ErrorNotAllowed('Cette entité '.$config->ENTITY_NAME().' ne vous concerne pas, vous ne pouvez pas l\'ajouter.');
            $response->render();
        }

        // On vérifie les conditions spécifique
        if (!$config->verifyConditionsToAdd($entity, $user)){
            $response = new ErrorNotAllowed('Cette entité '.$config->ENTITY_NAME().' n\'est pas ajoutable.');
            $response->render();
        }

        // On teste la duplication des champs en BDD    $isUpdate = true
        $listDupField = $config->getDuplicatedField($entity);
        if (!empty($listDupField)){
            $response = new ErrorDuplicatedFields('Des champs uniques sont déjà présents en base de données.', $listDupField);
            $response->render();
        }

        // Tout est ok
        $config->beforeAdd($entity, $dataUrl);
        $manager->add($entity);
        $config->afterAdd($entity, $dataUrl);

        // Succès
        $response = new Success200($entity);
        $response->render();
    }

    public static function update($user, $entityClass, $id, $dataUrl){
        // On récupère la config
        $config = Config::getConfig($entityClass);
        $className = $config->CLASS_NAME();

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        // On vérifie si le role est le bon
        if (!$config->allowedUserFor('update', $user)){
            $response = new ErrorNotAllowed('Vous n\'êtes pas autorisé à mettre à jour ce(tte) '.$config::ENTITY_NAME().'.');
            $response->render();
        }

        // On récupère l'entité
        $entity = $manager->get('id', $id);

        if ($entity == null){
            $response = new Error404('Il n\'y a aucun(e) '.$config->ENTITY_NAME().' pour l\'id '.$id);
            $response->render();
        }

        // On vérifie si l'user est lié à l'entité
        if (!$config->verifyLinkedField($entity, $user)){
            $response = new ErrorNotAllowed('Cette entité '.$config->ENTITY_NAME().' ne vous concerne pas, vous ne pouvez pas la modifier.');
            $response->render();
        }

        // On vérifie les conditions spécifique
        if (!$config->verifyConditionsToUpdate($entity, $user, $dataUrl)){
            $response = new ErrorNotAllowed('Cette entité '.$config->ENTITY_NAME().' n\'est pas modifiable.');
            $response->render();
        }

        // On vérifie les champs non modifiable
        if (!$config->verifyConstField($entity, $dataURL))
        {
            $response = new ErrorNotAllowed('Cette entité '.$config->ENTITY_NAME().' n\'est pas modifiable. (des champs constants ont été modifiés)');
            $response->render();
        }

        // On clone l'entité avant de la mettre à jour l'entité pour le before update
        $entityBeforeUpdate = clone $entity;
        $entity->MaJEntity($dataURL);

        // On teste la duplication des champs en BDD    $isUpdate = true
        $listDupField = $config->getDuplicatedField($entity, true);
        if (!empty($listDupField)){
            $response = new ErrorDuplicatedFields('Des champs uniques sont déjà présents en base de données.', $listDupField);
            $response->render();
        }

        // Mise à jour de l'entité
        $config->beforeUpdate($entityBeforeUpdate, $dataUrl);
        $manager->update($entity);
        $config->afterUpdate($entity, $dataUrl);

        // Succès
        $response = new Success200($entity);
        $response->render();
    }

    public static function delete($user, $entityClass, $id){
        $config = Config::getConfig($entityClass);

        // On vérifie si le role est le bon
        // On vérifie si le role est le bon
        if (!$config->allowedUserFor('delete', $user)){
            $response = new ErrorNotAllowed('Vous n\'êtes pas autorisé à supprimer ce(tte) '.$config::ENTITY_NAME().'.');
            $response->render();
        }

        // Récupération du manager
        $manager = new BaseManager(coPDO::get_db(), $entityClass);

        // On récupère l'entité
        $entity = $manager->get('id', $id);

        // Si il n'y a pas d'entité
        if ($entity == null){
            $response = new Error404('Il n\'y a aucun(e) '.$config->ENTITY_NAME().' pour l\'id '.$id);
            $response->render();
        }

        // On vérifie si l'user est lié à l'entité
        if (!$config->verifyLinkedField($entity, $user)){
            $response = new ErrorNotAllowed('Cette entité '.$config->ENTITY_NAME().' ne vous concerne pas, vous ne pouvez pas la supprimer.');
            $response->render();
        }

        // On vérifie les conditions spécifique
        if (!$config->verifyConditionsToDelete($entity, $user)){
            $response = new ErrorNotAllowed('Cette entité '.$config->ENTITY_NAME().' n\'est pas modifiable.');
            $response->render();
        }

        $config->beforeDelete($entity);
        $manager->delete($id);
        $config->afterDelete($entity);

        $response = new SuccessNoContent();
        $response->render();
    }
}
