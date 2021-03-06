<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput as ConsoleOutput;

/*
 * Code inspired by https://github.com/Orbitale/CmsBundle/blob/master/Tests/bootstrap.php
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 */
$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies using Composer to run the test suite.');
}
$autoload = require $file;

AnnotationRegistry::registerLoader(function ($class) use ($autoload) {
    $autoload->loadClass($class);

    return class_exists($class, false);
});
// Test Setup: remove all the contents in the build/ directory
// (PHP doesn't allow to delete directories unless they are empty)
if (is_dir($buildDir = __DIR__.'/../build')) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($buildDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo) {
        $fileinfo->isDir() ? rmdir($fileinfo->getRealPath()) : unlink($fileinfo->getRealPath());
    }
}
include __DIR__.'/Fixtures/App/AppKernel.php';

$application = new Application(new AppKernel('test', true));
$application->setAutoExit(false);

// Drop database schema
$input = new ArrayInput(['command' => 'doctrine:database:drop', '--force' => true]);
$application->run($input, new ConsoleOutput());

// Create database
$input = new ArrayInput(['command' => 'doctrine:database:create']);
$application->run($input, new ConsoleOutput());

// Create database schema
$input = new ArrayInput(['command' => 'doctrine:schema:create']);
$application->run($input, new ConsoleOutput());