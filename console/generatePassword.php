<?php
require_once '../vendor/autoload.php';

use src\GeneratePhp\GeneratePhp;

echo '-------------------------------
';
echo 'Entrez le mot de passe : ';
$password = trim(fgets(STDIN));

echo 'Voici le mot de passe a mettre dans le fichier config (getSuperAdminPassword) :
';
echo hash('sha256', $password).'

-------------------------------
';
