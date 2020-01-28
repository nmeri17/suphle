<?php

require 'autoload.php'; // they're setting cwd to root

use Doctrine\ORM\Tools\Console\ConsoleRunner;

use Controllers\Bootstrap;

use Symfony\Bundle\MakerBundle\Command\MakerCommand;

use Symfony\Component\Console\Command\HelpCommand;

$app = new Bootstrap('');

$entityManager = $app->connection;

$helperSet = ConsoleRunner::createHelperSet($entityManager);

$maker = $app->getClass(MakerCommand::class)->setName('make:entity');
//var_dump($maker/*$app->classes*/); die();
ConsoleRunner::run($helperSet, [$maker]); // this their console runner rigs in the default commands for orm
?>