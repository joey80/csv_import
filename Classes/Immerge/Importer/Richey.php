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
    public static $model;

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
        $this->log->write('Starting The Delete Order Process ' . $this->the_date);
        $this->log->write(' ');

        // get all entries older than old_date
        $orders = static::$model->getClosedOrdersOlderThanSixMonths();
        $total_orders = count($orders);

        if ($total_orders > 0) {

            foreach ($orders as $order)
            {
                // delete those entries
                static::$model->deleteClosedOrdersOlderThanSixMonths($order);

                $this->log->write('Deleting entry_id: ' . $order);
                $this->log->write('---------------------------------');
            }

        } else {
            $this->log->write('Nothing to delete');
            $this->log->write('---------------------------------');
        }
        
        $this->log->write(' ');
        $this->log->write('The Delete Order Process Has Completed');
        $this->log->write('################################################');
        $this->saveTheSettings($this->log, 'Delete Orders', $total_orders);
    }




    public function saveTheSettings($log, $title, $rows)
    {
        $settings = array(
            'report name' => $title,
            'status' => 'completed',
            'date' => $this->the_date,
            'rows imported' => $rows
        );

        $log->saveToJSON($settings, str_replace(' ', '-', strtolower($title)));
    }
}