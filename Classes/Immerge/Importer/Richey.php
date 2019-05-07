<?php
namespace Immerge\Importer;

spl_autoload_register(function ($className)
{
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    include_once '/var/www/html/scripts/Classes/' . $className . '.php';
});

require '/var/www/html/scripts/vendor/autoload.php';
use Immerge\Importer\Models as Models;
use Immerge\Importer\Logger as Logger;
use DateTime;

/**
 * Richey - General Class For Richey Lab
 *
 * @author Joey Leger
 * @author Immerge 2019
 */

class Richey
{

    public $log;
    public $the_date;

    public function __construct()
    {
        static::$model = Models::getInstance();
        $this->log = new Logger('delete');
        $this->the_date = (new DateTime('America/New_York'))->format('m-d-Y H:i:s');
    }



    /**
    * delete - The delete controller.
    *
    * @return nothing
    */

    public function delete()
    {
        
        // get all entries older than old_date
        $orders = static::$model->getOrdersOlderThanSixMonths();

        foreach ($orders as $order)
        {
            // delete those entries
            static::$model->deleteOrdersOlderThanSixMonths($order);
        }
        

        //
    }
}