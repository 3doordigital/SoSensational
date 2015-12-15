/**
 * Created by polcode on 14/12/2015.
 */

jQuery(document).ready(
    function () {
        if (jQuery('#post_type').val() === 'custom_advertisers') {
            var contentObj = jQuery('#content');
            var content = contentObj.val();
            contentObj.on('keyup', checkLenght);
        }

        function checkLenght() {
            if (contentObj.val().length > 186) {
                if(!jQuery('.limit-exceed').length){
                    jQuery('.wrap').prepend('<div class="error limit-exceed"><h1>Limit Description Length Exceed</h1></div>');
                }
            }else {
                jQuery('.limit-exceed').remove();
            }
            contentObj.val(content.substring(0, 186));
        }
    }
);
