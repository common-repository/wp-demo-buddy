<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
class DemoBuddy_admin_model
{
    function __construct()
    {
    
    }
        
    function setttings_saveform($data)
    {
        if (! isset( $_POST['demobuddy_settings_nonce_field'] )  || 
           ! wp_verify_nonce( $_POST['demobuddy_settings_nonce_field'], 'demobuddy_settings_nonce_action' ) ) 
        {
        
           print __('Sorry, your nonce did not verify.','DemoBuddy');
           exit;
        
        }
        $subject = 'Your Demo Login for [PRODUCT NAME]';
        $data['fromsubject'] = (trim($data['fromsubject']) == '')? $subject: $data['fromsubject'];
        
        $message = "Hi [FIRSTNAME],\r\n\r\nHere is your login details to [PRODUCT NAME]\r\n\r\nURL: [URL]\r\nUsername:[USERNAME]\r\nPassword:[PASSWORD]\r\n\r\nRegards.\r\n-Admin";
        $data['frommessage'] = (trim($data['frommessage']) == '')? $message: $data['frommessage'];
        
        $dat_json = json_encode($data);
        update_option('demobuddy_gensettings', $dat_json);
        return;
    }

    function setttings_getform()
    {
        $data = get_option('demobuddy_gensettings');
        $dat = json_decode($data);
        return $dat ;
    }
    
    function get_dashboard()
    {
        global $wpdb;
        
        $q = 'SELECT * FROM '.$wpdb->prefix.'demobuddy_products';
        $results = $wpdb->get_results($q);
        
        return $results;
    }
    
    function save_dashboard($data = array(), $file = array())
    {
        if (! isset( $_POST['demobuddy_product_nonce_field'] )  || 
           ! wp_verify_nonce( $_POST['demobuddy_product_nonce_field'], 'demobuddy_product_nonce_action' ) ) 
        {
        
           print __('Sorry, your nonce did not verify.','DemoBuddy');
           exit;
        
        }
        //print_r($data); exit;
        global $wpdb;
        $date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));  
        $plugin = array();  
        $theme  = array();
        if($_FILES['ddpluginupload']['tmp_name'] != '')
            $plugin = $this->file_Upload('ddpluginupload');
        if($_FILES['ddthemeupload']['tmp_name'] != '')
            $theme = $this->file_Upload('ddthemeupload');            
     //   print_r($_FILES); exit;
        
        if($data['id'] == '')
        {
            $wpdb->insert($wpdb->prefix.'demobuddy_products',
                        array(  'name' => $data['Product_Name'],
                                'lang' => $data['Demo_Language'],
                                'state' => '1',
                                'plugin_url' => $plugin[0],
                                'plugin_url_o' => $plugin[1],
                                'theme_url' => $theme[0],
                                'theme_url_o' => $theme[1],
                                'del_after' => $data['Delete_After'],
                                'one_click_demo' => $data['one_click_demo'],
                                'dis_menus' => json_encode($data['menu_pages']),
                                'dis_access' => json_encode($data['dis_access']),
                                'date' => $date
                                ), 
                    	array( 
                    		'%s', 
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%s',
                            '%s',
                            '%d',
                            '%d',
                            '%s',
                            '%s',
                            '%s' 
                    	) );
        }
        else
        {
            $values = array(  
                                'name' => $data['Product_Name'],
                                'lang' => $data['Demo_Language'],
                                'del_after' => $data['Delete_After'],
                                'dis_menus' => json_encode($data['menu_pages']),
                                'dis_access' => json_encode($data['dis_access']),
                                'one_click_demo' => $data['one_click_demo']
                            );
            $where = array( 'id' => $data['id'] );
            
            $format = array( 
                    		'%s', 
                            '%s',
                            '%d',
                            '%s',
                            '%s',
                            '%d'
                    	);
            
            if(!empty($plugin))
            {
                   $values['plugin_url'] = $plugin[0];
                   $values['plugin_url_o'] = $plugin[1];
                   array_push($format, '%s', '%s');
                    

            }
             if(!empty($theme))
            {
                   $values['theme_url'] = $theme[0];
                   $values['theme_url_o'] = $theme[1];   
                   array_push($format, '%s', '%s');              
            }
 
            $wpdb->update( $wpdb->prefix.'demobuddy_products', $values, $where, $format);
        }
        return;
     //   wp_redirect(admin_url().'admin.php?page=DemoBuddy_Products');
      //  exit;
            
    }
    
 
 
      
   function file_Upload($type)
   {
        global $wpdb;
        $upload_dir = get_option('wp_demo_buddy_upload_dir');
        $ext = pathinfo($_FILES[$type]['name'], PATHINFO_EXTENSION);
        
        if($ext !== 'zip') return;
        
        $info = pathinfo($_FILES[$type]['name']);
        
        $newname = time().rand().'_'.$info['basename'].'.php';  
        
        $target = $upload_dir.$newname;
        move_uploaded_file( $_FILES[$type]['tmp_name'], $target);
        
        $ret = array($newname,$info['basename']);
        return $ret;
        
    }   
    function switch_state($data)
    {
        global $wpdb;
        $state = ($_GET['fp'] == '0')? '1':'0';
        $wpdb->update( $wpdb->prefix.'demobuddy_products', 
                                array(  
                                    'state' => $state
                                ), 
                            	   array( 'id' => $data['id'] ),
                                   array( '%d' )
                            );
        return; 
    }
    function delproduct($data)
    {
        global $wpdb;
        $q = "DELETE FROM ".$wpdb->prefix."demobuddy_products WHERE id= ".$data['id'];
        $wpdb->query($q);
        return;
    }
    
    function get_productlog($data , $id)
    {
        global $wpdb;
        
        $start = $data['start'].' 00:00:00';
        $end = $data['end'].' 23:23:59';
        
        $q = "SELECT * FROM ".$wpdb->prefix."demobuddy_instances 
                WHERE product_id=".$id." 
                        AND (start_date BETWEEN '".$start."' AND '".$end."')
                        ORDER BY start_date DESC";
        $results = $wpdb->get_results($q);
        
        return $results;
    }  
        

    

    function demo_product_formcode($id )
    {
        global $wpdb;
        $dis_menus = array();
        $dis_access = array();
        if($id != '')
        {
            $q = "SELECT * FROM ".$wpdb->prefix."demobuddy_products WHERE id = ".$id;
            $data = $wpdb->get_row($q);
            $dis_menus = json_decode($data->dis_menus, true);
            $dis_access = json_decode($data->dis_access, true);
            $oneclickchecked = ($data->one_click_demo == '1')? 'checked':'';
        }
        $html = '
            <fieldset>
            
            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-4 control-label" for="Product_Name">'.__('Product Name','DemoBuddy').'</label>  
              <div class="col-md-8">
              <input required="required" id="Product_Name" name="Product_Name" type="text" placeholder="" class="form-control input-md" required="" 
              value="'.stripslashes($data->name).'">
                
              </div>
            </div>
            
            <div class="form-group">
              <label class="col-md-4 control-label" for="Demo_Language">'.__('Language','DemoBuddy').'<br>Wordpress Locale</label>  
              <div class="col-md-8">
              <input id="Demo_Language" name="Demo_Language" type="text" placeholder="" class="form-control input-md"  
              value="'.stripslashes($data->lang).'">
               <div class="clearfix"></div>
              <div class="alert alert-info">'.__('Leave Blank for US English.  Locale code can be found here','DemoBuddy').' <a target="_blank" href="https://wpcentral.io/internationalization/">'.__('Wordpress Locale','DemoBuddy').'</a></div> 
              </div>
              
            </div>
            
<div class="alert alert-info">'.__('You can ADD one Theme and Multiple plugins for a single Demo.  To add multiple plugins, first, UNZIP all the plugins to folders and then ZIP all the plugin folders into one zip file.','DemoBuddy').'<br> For quick access, you can download a plugin from Wordpress.<br /> <strong><a target="_blank" href="https://wordpress.org/plugins/">Plugins</a></strong>. <strong><a target="_blank" href="https://wordpress.org/themes/">'.__('Themes','DemoBuddy').'</a> </strong></div>                     
            <!-- File input-->
             <div class="form-group">
                <label class="col-md-4 control-label" for="Uploadplugin">'.__('Upload Plugin','DemoBuddy').' <br/>Zip File</label>  
                <div class="col-md-8">
                    <input type="file" id="uploaded_plugin" name="ddpluginupload">
                    <span class="help-block">'.stripslashes($data->plugin_url_o).'</span>  
                </div>
            </div>
             
            <!-- File input-->
             <div class="form-group">
                <label class="col-md-4 control-label" for="Uploadtheme">'.__('Upload Theme','DemoBuddy').' <br/>Zip File</label>  
                <div class="col-md-8">
                    <input type="file" id="uploaded_theme" name="ddthemeupload">
                    <span class="help-block">'.stripslashes($data->theme_url_o).'</span>  
                </div>
            </div>
            
            <!-- Text input-->
            <div class="form-group">
            <label class="col-md-4 control-label" for="Delete_After">'.__('Delete After (hrs.)','DemoBuddy').'</label>  
              <div class="col-md-8">
              <input id="Delete_After" name="Delete_After" type="text" placeholder="" class="form-control input-md" value="'.$data->del_after.'">
              <span class="help-block">(optional).  when left blank the demo will not be deleted.</span>  
              </div>
            </div>
 
        <!-- One Click Demo -->
            <div class="form-group">
              <label class="col-md-4 control-label" for="Auto_responder">'.__('One Click Demo','DemoBuddy').' <br />'._e('Only Button will be displayed.  <br> Wont capture leads.','DemoBuddy').'</label>
              <div class="col-md-8">                     
                <input type="checkbox" class="form-control" id="one_click_demo" name="one_click_demo" value="1" '.$oneclickchecked.'>
              </div>
            </div> 
         
            
            <div class="form-group">
                   <label class="col-md-4 control-label" for="checkboxes">'.__('Hide Admin Menu','DemoBuddy').'</label>
                  <div class="col-md-4">';
            
            $menuarr = array(   __('Dashboard','DemoBuddy') => 'index.php',
                                __('Posts','DemoBuddy') => 'edit.php?post_type=post',
                                __('Media','DemoBuddy') => 'upload.php',
                                __('Pages','DemoBuddy') => 'edit.php?post_type=page',
                                __('Comments','DemoBuddy') => 'edit-comments.php'                                
                            );
                  
            foreach($menuarr as $k => $v)
            {
                $checked = (in_array($v,$dis_menus))? 'checked':'';
                $html .= '<input type="checkbox" value="'.$v.'" name="menu_pages[]" '.$checked.'> '.$k.'<br />';
            }
            $html .= '</div><div class="col-md-4"> ';
            
            $menuarr = array(   __('Appearance (Themes)','DemoBuddy') => 'themes.php',
                                __('Plugins','DemoBuddy') => 'plugins.php',
                                __('Users','DemoBuddy') => 'users.php',
                                __('Tools','DemoBuddy') => 'tools.php',
                                __('Settings','DemoBuddy') => 'options-general.php'
                                );
                  
            foreach($menuarr as $k => $v)
            {
                $checked = (in_array($v,$dis_menus))? 'checked':'';
                $html .= '<input type="checkbox" value="'.$v.'" name="menu_pages[]" '.$checked.'> '.$k.'<br />';
            }            
                  
            $html .= '</div></div>';
            
            $menuarr = array(   __('Appearance(Themes)','DemoBuddy') => 'themes.php',
                                __('Plugins','DemoBuddy') => 'plugins.php'
                             );
            $html .= '<div class="col-md-12 alert-danger">'.__('WARNING: Demo users can download your themes / plugins if you Enable Appearance(Theme) & Plugins Access in Demo.  We strongly recommend disabling Appearance(Theme) and Plugins Access.','DemoBuddy').'</div>';            
            $html .= '<!-- Text input-->
                        <div class="form-group">
                          <label class="col-md-4 control-label" for="Disable_Access">'.__('Disable Access','DemoBuddy').' </label>  
                          <div class="col-md-8">';
                  
            foreach($menuarr as $k => $v)
            {
                $checked = (in_array($v,$dis_access))? 'checked':'';
                
                if($id == '' )
                    $checked = 'checked';
                
                $html .= '<input id="Disable_Access"   type="checkbox" value="'.$v.'" name="dis_access[]" '.$checked.'> '.$k.'<br />';
            }
            
            $html .=  '</div></div>';
             

            $html .= '<input type="hidden" name="id" value="'.$data->id.'" />
                         <input type="hidden" name="form_type" value="newdemoproduct" />
                        </fieldset>';
            
            return $html;
    }
}	
?>