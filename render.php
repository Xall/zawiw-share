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
        echo "<div class='entry'>$zawiw_share_message</div>";
    }
?>
    <div class="upload">
        <form action="" method="post" enctype="multipart/form-data">
            <!-- Form protection -->
            <?php wp_nonce_field( 'zawiw_share_upload' ); ?>
            <input id="zawiw_share_picker" type="file" name="file" id="file">
            <input id="zawiw_share_upload" type="submit" name="submit" value="Hochladen">
            <input type="hidden" name="zawiw_share" value="upload" />
        </form>
    </div>

    <!-- container for total size bar -->
    <div id="totalSize"><div class='bar'></div><div class='text'></div></div>
    <?php

    // Database query to get all files
    global $wpdb;
    $zawiw_share_query = 'SELECT * FROM ';
    $zawiw_share_query .= $wpdb->get_blog_prefix() . 'zawiw_share_data ';
    $zawiw_share_query .= 'ORDER by time DESC';
    $zawiw_share_items = $wpdb->get_results( $wpdb->prepare( $zawiw_share_query, null ), ARRAY_A );

    // echo "<pre>";
    // print_r($zawiw_share_items);
    // echo "</pre>";

    // Iterate over all files in array and print them in html
    // also calculates total file size

    foreach ( $zawiw_share_items as $file ) {
?>
        <div class="file one-third">
            <?php if ( wp_get_current_user()->ID==$file['owner'] ): ?>
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
                <div class="owner"><i class="fa fa-user"></i><?php echo get_userdata( $file['owner'] )?get_userdata( $file['owner'] )->user_login:"Unbekannt" ?> </div>
                <div class="time"><i class="fa fa-calendar"></i><?php echo $file['time'] ?></div>
                <div class="size"><i class="fa fa-floppy-o"></i><?php echo round( $file['size']/1024/1024, 2 )?> MB</div>
        </div>
        <?php
        $zawiw_share_total_size += $file['size'];
    }
    echo "<script type='text/javascript'>updateFilesize($zawiw_share_total_size);</script>"   ;

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
