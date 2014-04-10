function updateFilesize (size) {
    jQuery('#totalSize .text').html(Math.round(size/1024/1024)+ " MB von 100 MB");
    jQuery('#totalSize .bar').css("width",size/(1024*1024)+"%");

}
jQuery( document ).ready(function() {

    jQuery("#zawiw_share_upload").hide();
    jQuery("#zawiw_share_picker").change(function(){
        jQuery("#zawiw_share_upload").show();
    });
});