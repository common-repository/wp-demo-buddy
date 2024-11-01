<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
global $wpdb;

////////////////////////////////////////////////////////////////////////////


$q = "SELECT * FROM ".$wpdb->prefix."demobuddy_products WHERE id = ".$a['id'];
$result = $wpdb->get_row($q);  
if($result->state != '1') return;
$gensettings = json_decode(get_option('demobuddy_gensettings'));   
// $out .= print_r($gensettings,true);    
    if(trim($_GET['id']) != '')
    {
        global $post;
        
        $out.= '<div class="wpdembudform" style="'.$contStyle.'" >';   
        $q = "SELECT id, url, username, password FROM ".$wpdb->prefix."demobuddy_instances 
                WHERE uniq = '".trim($_GET['id'])."'";
        $results = $wpdb->get_row($q);


        $styles->resulttext = "Your Login to the Demo: \r\n\r\nURL: <a target='_blank' href='[URL]'>[URL]</a>  \r\n\r\nUsername: [USERNAME] \r\n\r\nPassword: [PASSWORD]";
        
        $styles->resulttext = str_replace('[URL]',$results->url.'wp-admin', stripslashes($styles->resulttext));
        $styles->resulttext = str_replace('[USERNAME]',$results->username, $styles->resulttext);
        $styles->resulttext = str_replace('[PASSWORD]',$results->password, $styles->resulttext);
        
        $out .= '<div style="color:'.$styles->res_txtcolor.'">'.nl2br($styles->resulttext).'</div>';

        $wpdb->update($wpdb->prefix.'demobuddy_instances',
                    array('block_access' => 1),
                    array('id' => $results->id));
        return;

}

/****************************** FORM **********************************/
///////////////////////////////////////////////////////////////////////
if($result->one_click_demo == '1')
{
    $txttype = 'hidden';
    $emtype = 'hidden';
    $fname = 'John';
    $lname = 'Doe';
    $email = 'hello@afffordableplugins.com';
    $hideopt = 'hidden';
    $hidelbl = 'display:none;';
}
else
{
    $txttype = 'text';
    $emtype = 'email';
    $fname = '';
    $lname = '';
    $email = '';
    $hideopt = '';
    $hidelbl = '';
}

                            
//$out .= '<pre>'.print_r($styles,true).'</pre>';
if(trim($_GET['err'] != ''))
{
    $out .= '<style>.alert-danger {
                color: #a94442;
                background-color: #f2dede;
                border-color: #ebccd1;
                padding:3px;
            }</style>';
    $out .= '<div class="alert alert-danger">'.urldecode(trim($_GET['err'])).'</div>';
}

$out .= '<div class="wpdembudform" style="'.$contStyle.'"> ';  
$out .= '<form id="demoduckform_5320" class="demobuddyform" method="post" action="">
        <div id="li_1" style="width:48%; float:left; margin-right:10px;'.$hidelbl.'">
            <label  style="'.$lblStyle.'" class="description" for="dedu_element_1">
            '.__('First Name','DemoBuddy').'
            </label>
            <input style="'.$txtStyle.'"  id="element_1" name="fname" class="dedu_element text medium" required type="'.$txttype.'"  value="'.$fname.'"/>
      </div>
    
    <div id="li_2"  style="width:48%; float:left;'.$hidelbl.'">
      <label style="'.$lblStyle.'" class="description" for="dedu_element_2">
        '.__('Last Name','DemoBuddy').'
      </label>

        <input style="'.$txtStyle.'"  id="element_2" name="lname" class="dedu_element text medium" required type="'.$txttype.'"  value="'.$lname.'"/>

    </div>
    <div style="clear:both"></div>
    <div id="li_3" style="'.$hidelbl.'">
      <label style="'.$lblStyle.'"  class="description" for="dedu_element_3">
        '.__('Email','DemoBuddy').'
      </label>
 <div >
        <input style="'.$emStyle.'"  id="element_3" name="email" class="dedu_element text medium" type="'.$emtype.'" required  value="'.$email.'"/></div>

    </div>';

    
   $out .= '<div class="buttons" style="margin-top:10px">
        <input type="hidden" name="product_id" value="'.$a['id'].'" />
      <input type="hidden" name="demobuddy_form_id" value="demobuddy_form_5812" />
      <input style="'.$btnStyle.'"   id="saveForm" class="button_text" type="submit" name="submit" value="Create the Demo" />
      <p class="demowait" style="text-align:center; color:#101010; background-color:#fff; padding:2px; display:none;">'.__("Please wait... Building the Demo.",'DemoBuddy').'</p>
    </div>';
$out .= wp_nonce_field( 'demobuddy_createdemo_nonce_action', 'demobuddy_createdemo_nonce_field', false,true );



$out .= '</form></div><div style="clear:both"></div><script>
        jQuery("#demoduckform_5320").submit(function(){
            jQuery(".demowait").css({ "display" : "" });
    });
</script>';

