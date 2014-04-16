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
    jQuery("#zawiw_share_uploads .file").mouseover(function() {
        var thumb = jQuery(this).attr('thumb');
        jQuery(this).css({"background-image":"url(\""+thumb+"\")"})
    });
    jQuery("#zawiw_share_uploads .file").mouseout(function() {
        console.log(this);
        jQuery(this).css({"background-image":"none"});
    });

});