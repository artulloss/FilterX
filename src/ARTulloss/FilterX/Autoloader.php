<?php

namespace ARTulloss\FilterX;

class Autoloader {

    /**
     * This autoloader checks the lib directory
     */
    public function __construct() {
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload($class) {
        // project-specific namespace prefix
        $prefix = 'PASVL\\';

        // base directory for the namespace prefix
        $base_dir = __DIR__ . '/libs/pasvl/src/';

        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // no, move to the next registered autoloader
            return;
        }

        // get the relative class name
        $relative_class = substr($class, $len);

        // replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // if the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    }
}
