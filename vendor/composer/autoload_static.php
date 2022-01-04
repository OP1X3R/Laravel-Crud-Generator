<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf84b371d54bb52feb42bf24f4e0bcf2a
{
    public static $prefixLengthsPsr4 = array (
        'J' => 
        array (
            'jlcrud\\crud-generator\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'jlcrud\\crud-generator\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf84b371d54bb52feb42bf24f4e0bcf2a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf84b371d54bb52feb42bf24f4e0bcf2a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf84b371d54bb52feb42bf24f4e0bcf2a::$classMap;

        }, null, ClassLoader::class);
    }
}
