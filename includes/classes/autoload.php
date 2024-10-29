<?php

defined( 'ABSPATH' ) or die;

$baseDir = __DIR__;

spl_autoload_register(function($class) use ($baseDir) {
    $namespace = 'Altoshift\\WordPress\\';

    $len = strlen($namespace);
    if (strncmp($namespace, $class, $len ) !== 0 ) {
        return;
    }

    $namespaceClass = substr($class, $len);

    $pathParts = explode('\\', $namespaceClass);
    $pathParts[count($pathParts) - 1] .= '.php';
    $filename = $baseDir . '/' . implode('/', $pathParts);

    if ( file_exists( $filename ) ) {
        require_once $filename;
    }
});