function updateFilesize (size) {
    jQuery('#zawiw_share_meter .text').html(Math.round(size/1024/1024)+ " MB von 100 MB");
    jQuery('#zawiw_share_meter .bar').css("width",size/(1024*1024)+"%");

}
jQuery( document ).ready(function() {

    jQuery("#zawiw_share_upload").hide();
    jQuery("#zawiw_share_picker").change(function(){
        jQuery("#zawiw_share_upload").show();
    });

    // Clearing form data
    // var form = jQuery("#zawiw_share_picker form");
    // form.get(0).reset();

});

