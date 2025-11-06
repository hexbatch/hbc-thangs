<?php
namespace Hexbatch\Thangs\Traits;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
trait SearchFiles
{
    public static function extract_namespace($file) {
        $ns = NULL;
        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (str_starts_with($line, 'namespace')) {
                    $parts = explode(' ', $line);
                    $ns = rtrim(trim($parts[1]), ';');
                    break;
                }
            }
            fclose($handle);
        }
        return $ns;
    }

    public static function findClasses(string $relative_source_folder,string $fully_qualified_parent_class) : array
    {

        $directory = new RecursiveDirectoryIterator(base_path($relative_source_folder));
        $flattened = new RecursiveIteratorIterator($directory);

        $files = new RegexIterator($flattened, '#\.(?:php)$#Di');
        $classes = [];
        foreach($files as $file) {
            $namespace = static::extract_namespace($file);
            $class = basename($file, '.php');
            $full_class_name = $namespace . '\\' .$class;
            if (class_exists($full_class_name)) {
                if (is_subclass_of($full_class_name, $fully_qualified_parent_class)) {
                    $classes[] = $full_class_name;
                }
            }
        }


        return $classes;

    }
}

