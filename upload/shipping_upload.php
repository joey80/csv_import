<?php

if ( isset( $_FILES['daily_shipping'] ) )
{
    $old_shipping_file = '/shipping/shipping_update.csv';
    $fileName = $_FILES['daily_shipping']['name'];

    if ( file_exists( $_SERVER [ 'DOCUMENT_ROOT' ] . $old_shipping_file ))
    {
        // delete the old file
        unlink( $_SERVER['DOCUMENT_ROOT'] . '/shipping/shipping_update.csv' );
    }

    // save the new file
    move_uploaded_file( $_FILES['daily_shipping']['tmp_name'], $_SERVER [ 'DOCUMENT_ROOT' ] . '/shipping/' . $fileName );

}
