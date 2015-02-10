<?php


// Defines the zawiw-share shortcode
add_shortcode( 'zawiw-share', 'zawiw_share_shortcode' );

// Stylesheet
add_action( 'wp_enqueue_scripts', 'zawiw_share_queue_stylesheet' );
add_action( 'wp_enqueue_scripts', 'zawiw_share_queue_script' );

// Implements the zawiw-share shortcode
// Generates a form to upload a file
function zawiw_share_shortcode( $atts ) {

    // start buffered output
    ob_start();

    global $zawiw_share_message;
    // used to calculate total size of all files
    $zawiw_share_total_size = 0;

    // check for login
    if ( !is_user_logged_in() ) {
        echo '<p>Sie müssen angemeldet sein, um diese Funktion zu nutzen</p>';
        return;
    }
    // Show global message if available
    if ( strlen( $zawiw_share_message ) ) {
        echo "<div id='zawiw_share_message' class='entry'>$zawiw_share_message</div>";
    }
?>
    <div id="zawiw_share_uploader">
        <form action="" method="post" enctype="multipart/form-data">
            <!-- Form protection -->
            <?php wp_nonce_field( 'zawiw_share_upload' ); ?>
            <fieldset>
                <input id="zawiw_share_picker" type="file" name="file">
                <input id="zawiw_share_upload" type="submit" name="submit" value="Hochladen">
            </fieldset>
            <fieldset>
                <label for="name">Anzeigename: </label>
                <input id="zawiw_share_name" type="text" name="displayname"><br>
                <label for="name">Urheber/Lizenz: </label>
                <input id="zawiw_share_copyright" type="text" name="copyright"><br>
            </fieldset>
            <input type="hidden" name="zawiw_share" value="upload" />
        </form>
    </div>

    <!-- container for total size bar -->
    <div id="zawiw_share_meter"><div class='bar'></div><div class='text'></div></div>
    <?php

    // Database query to get all files
    global $wpdb;
    $meta_field1 = 'time';
    $zawiw_share_query =
    "
    SELECT      *
    FROM        $wpdb->prefix"."zawiw_share_data
    ORDER BY    time DESC;
    ";
    $zawiw_share_items = $wpdb->get_results( $zawiw_share_query, ARRAY_A );

    // echo "<pre>";
    // print_r($zawiw_share_items);
    // echo "</pre>";

    // Iterate over all files in array and print them in html
    // also calculates total file size

    ?>
    <div id="zawiw_share_uploads">
    <?php foreach ( $zawiw_share_items as $key=>$file ) : ?>
        <?php
            $style = "";
            // find thumbnail
            $pathinfo = pathinfo($file['file']);
            $thumb_path = $pathinfo['dirname'].'/'.$pathinfo['filename'].'_thumb'.'.'.$pathinfo['extension'] ;
            if (file_exists($thumb_path )) {
                $pathinfo = pathinfo($file['url']);
                $thumb_path = $pathinfo['dirname'].'/'.$pathinfo['filename'].'_thumb'.'.'.$pathinfo['extension'] ;
            }else{
                $thumb_path ='';
            }

         ?>
        <div class="<?php echo !($key % 3) ? "first " :"" ?>file one-third" thumb="<?php echo $thumb_path ?>">
            <?php if ( wp_get_current_user()->ID==$file['owner'] || current_user_can( 'manage_options' ) ): ?>
                <div>
                    <form action="" method="post" enctype="multipart/form-data">
                    <!-- Form protection -->
                    <?php wp_nonce_field( 'zawiw_share_delete' ); ?>
                    <input type="submit" class="linkButton" value="X">
                    <input type="hidden" name="zawiw_share" value="delete" />
                    <input type="hidden" name="zawiw_share_delete_id" value="<?php echo $file['id'] ?>" />
                    </form>
                </div>
            <?php endif ?>
                <div class="name"><a href="<?php echo $file['url'] ?>"><?php echo $file['name']." " ?></a></div>
                <div class="owner"><i class="fa fa-user"></i><?php echo get_userdata( $file['owner'] )? get_userdata( $file['owner'] )->display_name:"Unbekannt" ?> </div>
                <div class="copyright">© <?php echo strlen($file['copyright']) ? $file['copyright'] : "Unbekannt" ?> </div>
                <div class="time"><i class="fa fa-calendar"></i><?php echo $file['time'] ?></div>
                <div class="size"><i class="fa fa-floppy-o"></i><?php echo round( $file['size']/1024/1024, 2 )?> MB</div>
        </div>
        <?php
        $zawiw_share_total_size += $file['size'];
    endforeach; ?>
    <script type='text/javascript'>updateFilesize(<?php echo $zawiw_share_total_size ?>);</script>
    </div>
    <?php
    // end buffered output
    $output = ob_get_contents();
    ob_end_clean();

    return $output;

}

function zawiw_share_queue_stylesheet() {
    wp_enqueue_style( 'zawiw_share_style', plugins_url( 'style.css', __FILE__ ) );
    wp_enqueue_style( 'font_awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
}

function zawiw_share_queue_script()
{
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'zawiw_share_script', plugins_url( 'helper.js', __FILE__ ) );
}

?>
