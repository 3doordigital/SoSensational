jQuery(document).ready(function($) {
    
    $('.searchList').keyup( function(event) {
        //console.log($(this).val());
        var search = $(this).val();
        var id = $(this).attr('rel');
        var list = $('#'+id);
        $('#'+id+' li').css('display', 'block');
        list.each(function(){
            $(this).find('li label').each(function(){
                //console.log($(this));
                //console.log($(this).text());
                var label = $(this).text();
                var re = new RegExp(search, 'gi');
                var match = label.match(re);
                if( match ) {
                    //console.log(label.match(search));
                } else {
                    $(this).parent().css('display', 'none'); 
                    //console.log(label.match(search));
                }
                console.log('Search: '+search+' || Label: '+label+' || Match: '+match);
            });
        });
    });
    
    $('.add_product_confirm').click(function(event) {
        if($(this).prop('checked')) {
           $('#add_products_submit').removeAttr('disabled'); 
        } else {
           $('#add_products_submit').attr('disabled', 'disabled');                            
        }
    });
    
});