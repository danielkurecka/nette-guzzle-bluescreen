<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

$configurator = new Nette\Configurator;
$configurator->setDebugMode(true);
$configurator->enableDebugger();
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->addConfig(__DIR__ . '/app/config.neon');
$container = $configurator->createContainer();
$container->getByType(Nette\Application\Application::class)->run();
