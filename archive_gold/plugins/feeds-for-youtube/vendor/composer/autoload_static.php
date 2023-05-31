<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit16190c9716d5de710cb9f2d881ca53b6
{
    public static $files = array (
        'sb_ytf_bbf73f3db644d3dced353b837903e74c' => __DIR__ . '/..' . '/php-di/php-di/src/DI/functions.php',
        'sb_ytf_b1eb330aa001ae4915f07005b4e993c2' => __DIR__ . '/..' . '/smashballoon/framework/Utilities/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Smashballoon\\Stubs\\' => 19,
            'Smashballoon\\Customizer\\' => 24,
            'SmashBalloon\\YoutubeFeed\\Vendor\\Smashballoon\\Framework\\' => 55,
            'SmashBalloon\\YoutubeFeed\\Vendor\\Psr\\Container\\' => 46,
            'SmashBalloon\\YoutubeFeed\\Vendor\\PhpDocReader\\' => 45,
            'SmashBalloon\\YoutubeFeed\\Vendor\\Invoker\\' => 40,
            'SmashBalloon\\YoutubeFeed\\Vendor\\Interop\\Container\\' => 50,
            'SmashBalloon\\YoutubeFeed\\Vendor\\DI\\' => 35,
            'SmashBalloon\\YouTubeFeed\\' => 25,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Smashballoon\\Stubs\\' => 
        array (
            0 => __DIR__ . '/..' . '/smashballoon/stubs/src',
        ),
        'Smashballoon\\Customizer\\' => 
        array (
            0 => __DIR__ . '/..' . '/smashballoon/customizer/app',
        ),
        'SmashBalloon\\YoutubeFeed\\Vendor\\Smashballoon\\Framework\\' => 
        array (
            0 => __DIR__ . '/..' . '/smashballoon/framework',
        ),
        'SmashBalloon\\YoutubeFeed\\Vendor\\Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'SmashBalloon\\YoutubeFeed\\Vendor\\PhpDocReader\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-di/phpdoc-reader/src/PhpDocReader',
        ),
        'SmashBalloon\\YoutubeFeed\\Vendor\\Invoker\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-di/invoker/src',
        ),
        'SmashBalloon\\YoutubeFeed\\Vendor\\Interop\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/container-interop/container-interop/src/Interop/Container',
        ),
        'SmashBalloon\\YoutubeFeed\\Vendor\\DI\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-di/php-di/src/DI',
        ),
        'SmashBalloon\\YouTubeFeed\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit16190c9716d5de710cb9f2d881ca53b6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit16190c9716d5de710cb9f2d881ca53b6::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit16190c9716d5de710cb9f2d881ca53b6::$classMap;

        }, null, ClassLoader::class);
    }
}
