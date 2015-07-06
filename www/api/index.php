<?php
ini_set('display_errors',1);
require_once '../../vendor/autoload.php';
// Chargement de l'autoload avant de faire quoi que ce soit

use src\Config\Config;

use src\Security\SecurityRequest;

use src\Controller\Controller;

use src\Entity\BasicUser;

use src\Output\Error404;
use src\Output\ErrorInternal;
use src\Output\ErrorNotAllowed;
use src\Output\Success200;


Config::generalConfig();

// On lance l'app Slim
$app = Config::getApp();

// Configuration pour renvoyer du json / du mode debug
$app->response->headers->set('Content-Type', 'application/json');
$app->config('debug', Config::isDebugMode());

$app->notFound(function (){
    $response = new Error404('Cette page n\'existe pas.');
    $response->render();
});

$app->error(function(\Exception $e){
    $response = new ErrorInternal($e->getMessage());
    $response->render();
});

// On récupère l'user courant
$user = SecurityRequest::getRequestUser();

$app->get('/connection', function() use ($user){
    if ($user->isGranted(BasicUser::ROLE_ANONYME)){
        $response = new ErrorNotAllowed('Cet utilisateur n\'existe pas.');
        $response->render();
    }else{
        $response = new Success200($user);
        $response->render();
    }
});

$app->get('/awTable/:table', function($table) use ($user){
    Controller::getAwTable($table);
});

$app->post('/file', function() use ($user){
    Controller::addFile($user, $_FILES);
});

$app->get('/:entityClass/:id', function($entityClass, $id) use ($user){

    if ($user->isAdmin() && Controller::getBool('admin_rights')){
        Controller::getAdmin($entityClass, $id);
    }else{
        Controller::get($user, $entityClass, $id);
    }
});

$app->get('/:entityClass', function($entityClass) use ($user){
    Controller::getList($user, $entityClass);
});

// AJout d'une entité en bdd (si y'a déjà les id c'est un update)
$app->post('/:entityClass', function($entityClass) use ($user){
    // On récupère les datas (POST) et la config
    $dataUrl = Controller::getDataUrl();

    if ($user->isAdmin() && Controller::getBool('admin_rights')){
        if (isset($dataUrl['id'])){
            Controller::updateAdmin($entityClass, $id, $dataUrl);
        }else{
            Controller::addAdmin($entityClass, $dataUrl);
        }
    }else{
        if (isset($dataUrl['id'])){
            Controller::update($user, $entityClass, $id, $dataUrl);
        }else{
            Controller::add($user, $entityClass, $dataUrl);
        }
    }
});

$app->post('/:entityClass/:id', function($entityClass, $id) use ($user){

    if ($user->isAdmin() && Controller::getBool('admin_rights')){
        Controller::updateAdmin($entityClass, $id, Controller::getDataUrl());
    }else{
        Controller::update($user, $entityClass, $id, Controller::getDataUrl());
    }
});

$app->delete('/:entityClass/:id', function($entityClass, $id) use ($user){
    if ($user->isAdmin() && Controller::getBool('admin_rights')){
        Controller::deleteAdmin($entityClass, $id);
    }else{
        Controller::delete($user, $entityClass, $id);
    }
});

$app->run();
