<?php
require_once '../vendor/autoload.php';

use src\GeneratePhp\GeneratePhp;
use src\Config\Config;

// Vérif entiy et ajout dans la liste
$entities = Config::getEntitiesInDBInLowerCase();

echo '-------------------------------
';
$secondTime = false;
do{
if ($secondTime)
    echo $entityName.' est deja pris.
';
echo 'Entrez le nom de l\'entite a generer (commence par un majuscule) : ';
$entityName = trim(fgets(STDIN));

$secondTime = true;
}
while(in_array(strtolower($entityName), $entities));


echo '
Entrez le nombre de champs de l\'entite : ';
$numberFields = trim(fgets(STDIN));




do{
echo '
Generer le fichier de config (src\Config\Config'.$entityName.') Y/N  : ';
$withConfig = trim(fgets(STDIN));
}while($withConfig != 'Y' && $withConfig != 'N');

file_put_contents('../src/Entity/'.$entityName.'.php', GeneratePhp::getEntityCode($entityName, $numberFields));
Config::addEntityInDB($entityName);

echo 'src/Entity/'.$entityName.'.php : genere avec succes
';

if ($withConfig == 'Y'){

file_put_contents('../src/Config/Config'.$entityName.'.php', GeneratePhp::getConfigEntityCode($entityName, $numberFields));

echo 'src/Config/Config'.$entityName.'.php : genere avec succes

-------------------------------
';

}
