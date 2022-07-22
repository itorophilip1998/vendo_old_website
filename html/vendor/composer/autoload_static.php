<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd07537331e4d242275a68c8afdb91019
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd07537331e4d242275a68c8afdb91019::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd07537331e4d242275a68c8afdb91019::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}