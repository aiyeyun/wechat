<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6be3c32f10bdd98a4b408bb986a474f6
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'service\\' => 8,
        ),
        'r' => 
        array (
            'redis\\' => 6,
        ),
        'm' => 
        array (
            'models\\' => 7,
        ),
        'l' => 
        array (
            'library\\' => 8,
        ),
        'c' => 
        array (
            'commands\\' => 9,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
            'Medoo\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'service\\' => 
        array (
            0 => __DIR__ . '/../..' . '/service',
        ),
        'redis\\' => 
        array (
            0 => __DIR__ . '/../..' . '/redis',
        ),
        'models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/models',
        ),
        'library\\' => 
        array (
            0 => __DIR__ . '/../..' . '/library',
        ),
        'commands\\' => 
        array (
            0 => __DIR__ . '/../..' . '/commands',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Medoo\\' => 
        array (
            0 => __DIR__ . '/..' . '/catfan/medoo/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6be3c32f10bdd98a4b408bb986a474f6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6be3c32f10bdd98a4b408bb986a474f6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}