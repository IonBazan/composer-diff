<?php

$finder = (new PhpCsFixer\Finder())
    ->files()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->name('*.php')
;

return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@PSR2' => true,
        '@Symfony' => true,
        'array_syntax' => array('syntax' => 'long'),
        'visibility_required' => array('elements' => array('property', 'method')),
    ))
    ->setFinder($finder);
