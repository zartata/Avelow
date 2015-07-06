<?php
require_once '../vendor/autoload.php';

use src\BDD\GeneratorDB;
use src\Config\Config;

Config::generalConfig();
$generatorDB = new GeneratorDB();

$generatorDB->generate();
