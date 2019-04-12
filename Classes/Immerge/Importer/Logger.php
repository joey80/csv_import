<?php
namespace Immerge\Importer;

spl_autoload_register(function ($className)
{
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

require '/var/www/html/scripts/vendor/autoload.php';

/**
 * Logger - Logger For Richey Lab
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Logger
{

    public $file;

    public function __construct($filename)
    {

        $this->file = $filename;
    }




    /**
     * write - Writes a new message into the log file
     *
     * @param string $message - The message to write
     * @return nothing
     */

    public function write($message)
    {
        file_put_contents($this->file, $message);
    }




    /**
     * getLog - Gets the content of the log file
     *
     * @param none
     * @return $content - The content of the log file
     */

    public function getLog()
    {

        $content = file_get_contents($this->file);
        return $content;
    }
}