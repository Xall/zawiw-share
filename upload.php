<?php
// Loads file API
if ( ! function_exists( 'wp_handle_upload' ) ) require_once ABSPATH . 'wp-admin/includes/file.php';

// Early called action, to process POST and FILE data
add_action( 'template_redirect', 'zawiw_share_match_upload' );

// Starts processing from Data
function zawiw_share_match_upload( $template ) {
    if ( !empty( $_POST['zawiw_share'] ) ) {
        // If the form was used
        if ( $_POST['zawiw_share'] == 'upload' && check_admin_referer( 'zawiw_share_upload' ) ) {
            zawiw_share_process_upload();
        }
        // If delete was clicked
        if ( $_POST['zawiw_share'] == 'delete' && check_admin_referer( 'zawiw_share_delete' ) ) {
            zawiw_share_process_delete();
        }
    }
}

// Handles the file by moving it out of temp and save it to subdir
function zawiw_share_process_upload() {
    global $zawiw_share_message;
    global $wpdb;

    // Calculate total filesize in Database
    $query = "SELECT * FROM ".$wpdb->get_blog_prefix() . 'zawiw_share_data';
    $files = $wpdb->get_results( $wpdb->prepare( $query, null ), ARRAY_A );
    $totalsize = 0;
    foreach ($files as $file) {
        $totalsize += $file['size'];
    }

    // Get fileinfos from env
    $uploadedfile = $_FILES['file'];


    // Error when spacelimit exceeded
    if ($totalsize + $uploadedfile['size'] > (100*1024*1024)) {
        $zawiw_share_message = "Uploadvolumen erreicht. Bitte geben Sie zuerst Spericherplatz frei, indem Sie alte Dateien löschen.";
        return;
    }

    // Enable reaction on uploads
    add_filter( 'upload_dir', 'zawiw_share_change_upload_dir' );

    // Move uploaded file from temp to subdir
    $upload_overrides = array( 'test_form' => false );
    $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );


    // Disable reaction on uploads
    remove_filter( 'upload_dir', 'zawiw_share_change_upload_dir' );

    // Check if file actually exists
    if ( isset( $movefile['error'] ) ) {
        $zawiw_share_message = $movefile['error'];
        return;
    }

    //Check if its an image
    $filetype = wp_check_filetype( $movefile['file'] );
    if( strpos($filetype['type'],'image' )!== false){
        // Create editor instance to resize the image
        $image = wp_get_image_editor( $movefile['file'] );
        if ( ! is_wp_error( $image ) ) {
            $image->resize( 300, 300, true );
            $pathinfo = pathinfo($movefile['file']);
            // save the new image as x_thumb.y
            $image->save( $pathinfo['dirname'].'/'.$pathinfo['filename'].'_thumb'.'.'.$pathinfo['extension'] );
        }else{
            // TODO
        }
    }

    $zawiw_share_message = "Datei erfolgreich hochgeladen.";

    // Prepare an array to store in db
    $file_data = array();
    $current_user = wp_get_current_user();
    // Displayname
    if (strlen($_POST['displayname'])) {
        $file_data['name'] = $_POST['displayname'];
    }else{
        $file_data['name'] = isset( $uploadedfile['name'] ) ? $uploadedfile['name'] : '';
    }
    $file_data['owner'] = $current_user->ID;
    $file_data['size'] = isset( $uploadedfile['size'] ) ? $uploadedfile['size'] : '';
    $file_data['url'] = isset( $movefile['url'] ) ? $movefile['url'] : '';
    $file_data['file'] = isset( $movefile['file'] ) ? $movefile['file'] : '';
    $file_data['time'] = date( 'Y-m-d H:i:s' );

    // Update the db
    global $wpdb;
    $wpdb->insert( $wpdb->get_blog_prefix() . 'zawiw_share_data', $file_data );
}

// Temporary change to the upload directory
function zawiw_share_change_upload_dir( $upload ) {
    $upload['subdir'] = '/zawiw-share';
    $upload['path'] = $upload['basedir'] . $upload['subdir'];
    $upload['url'] = $upload['baseurl'] . $upload['subdir'];
    return $upload;
}

// Deletes an upload
function zawiw_share_process_delete() {
    global $zawiw_share_message;
    global $wpdb;
    // Save post data
    $fileID = $_POST['zawiw_share_delete_id'];
    // Prepare query "select * from db where id = #"
    $query = "SELECT * FROM " . $wpdb->get_blog_prefix() . 'zawiw_share_data WHERE id='.$fileID;
    try {
        // run query
        $file = $wpdb->get_results( $wpdb->prepare( $query, null ), ARRAY_A );
        $file = $file[0];
        // delete file
        if ( file_exists($file['file'] ) and !unlink( $file['file'] ) ) {
            throw new Exception( "Error unlinking file", 1 );
        }
        $pathinfo = pathinfo($file['file']);

        $thumb_path = $pathinfo['dirname'].'/'.$pathinfo['filename'].'_thumb'.'.'.$pathinfo['extension'] ;
        if ( file_exists($thumb_path ) and !unlink( $thumb_path ) ){
           throw new Exception( "Error unlinking file", 1 );
       }

        // Delete db entry
       $wpdb->delete( $wpdb->get_blog_prefix() . 'zawiw_share_data', array( 'ID' => $fileID ) );
       $zawiw_share_message = "Datei erfolgreich gelöscht.";
   } catch ( Exception $e ) {
    $zawiw_share_message = "Beim Löschen der Datei ist ein Fehler aufgetreten.";
}
}

?>
