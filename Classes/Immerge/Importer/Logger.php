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
    public $path;
    public $extension;

    public function __construct($filename, $path = null, $extension = null)
    {

        if ($path == null ? $this->path = '/var/www/html/logs/' : $this->path = $path);
        if ($extension == null ? $this->extension = '.log' : $this->extension = $extension);
        $this->file = $filename . $this->extension;
        $this->deleteLog($this->file);
    }




    /**
     * write - Writes a new message into the log file
     *
     * @param string $message - The message to write
     * @return nothing
     */

    public function write($message)
    {

        file_put_contents($this->path . $this->file, trim($message).PHP_EOL, FILE_APPEND);
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




    /**
     * deleteLog - Deletes the log file if already exists
     *
     * @param none
     * @return nothing
     */

    public function deleteLog($fileName, $path = null)
    {

        if ($path == null ? $path = $this->path : $path = $path);

        if (file_exists($path . $fileName))
        {
            unlink($path . $fileName);
        }
    }




    /**
     * saveToJSON - Saves a log in JSON format
     *
     * @param none
     * @return nothing
     */

    public function saveToJSON($array, $fileName, $path = null)
    {

        if ($path == null ? $path = $this->path : $path = $path);
        $this->deleteLog($fileName, $path);
        
        $fp = fopen($path . $fileName . '.json', 'w');
        fwrite($fp, json_encode($array, JSON_PRETTY_PRINT));
        fclose($fp);
    }
}