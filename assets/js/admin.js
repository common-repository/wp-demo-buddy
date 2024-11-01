jQuery(document).ready(function($)
{
    var ajaxload = '<img src="'+Ob10n.DemoBuddy6_url+'/assets/ajload.gif" />'
     $('[data-toggle="tooltip"]').tooltip();
     
    $(".demoModal").click(function(){
        var title = $(this).attr('data-form');
        var action = $(this).attr('data-action');
        $('.modal-title').text(title);
        $('.modal-body').html(ajaxload);
        var id = $(this).attr('data-id');
        //alert(title+action);
         $("#demoModal").modal({
      backdrop: 'static',
      keyboard: false
    },'show');
        var dat = {
                'action':action,
                'id' : id
                };
        $.post( ajaxurl, dat, function( res ) {
            //alert(res);
            $('.modal-body').html(res);
        });
        
    });
    $('.ddclickselect').click(function() {
        $(this).select(); 
    });
    jQuery('[data-toggle="confirmation"]').confirmation({
        rootSelector: '[data-toggle=confirmation]'
    });
    jQuery('.pwtog').click(function(){
        var el = $('#'+$(this).attr('data-rel'));
        if(el.attr('type') == 'text')
            el.attr('type','password')
        else
            el.attr('type','text')
 
    })
    $('.modeselect').click(function()
    {
        $('.modediv').addClass('hidden');
        var elid = $(this).val();
        $('#'+elid).removeClass('hidden');
    })
    jQuery('#saveproduct').click(function(){
        jQuery('.saveproductcont').html(ajaxload);
    })
    
}(jQuery));