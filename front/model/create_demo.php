<?php if ( ! defined( 'ABSPATH' ) ) exit;  
class demobuddy_demo
{
     function __construct()
     {
         
        $this->date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));
        $this->pwd = wp_generate_password( $length=12, $include_standard_special_chars=false );

        $this->error = array(); 
        
        $data = get_option('demobuddy_gensettings');
        $dat = json_decode($data);

        $this->mode = $dat->mode;
        $this->uniqid = uniqid().random_int(100, 999);
        if($dat->mode == 'ftp')
        {   $this->ftpurl   = $dat->ftpurl;
            $this->conn     = ftp_connect($dat->ftpserver);
            $this->login    = ftp_login($this->conn, $dat->ftpuser, $dat->ftppass);
            $this->url      = trailingslashit($dat->ftpurl).$this->uniqid.'/wordpress/';
            
            if (!$this->conn || !$this->login) $this->error[] = 'FTP Connection attempt failed!';         
            
            $this->dbname = $dat->dbname;
            $this->dbuser = $dat->dbuser;
            $this->dbpass = $dat->dbpass;
            $this->dbserver = $dat->dbserver;
                      
        }
        if($dat->mode == 'local')
        {
            $this->url = trailingslashit(home_url()).$this->uniqid.'/wordpress/';
        }
        $this->arconfirm = $dat->arconfirm; 
        
     }  
     
     function create_demo($data )
     {
        $this->insert_user($data); 
        if($this->mode == 'local')
        {
            $dir = $this->create_folder_local($data);
            $this->upload_wp_local($data,$dir);   
        } 
        else 
        {
            $this->create_folder($data);
            $this->upload_wp($data);   
        }

       
         
        $this->logerror($this->uniqid);
        return  $this->uniqid;   
     } 
      
 
     function create_folder($data)
     {
        if (!ftp_mkdir($this->conn, $this->uniqid))
        {
            $this->error[] = "There was a problem while CREATING folder ".$this->uniqid; 
            exit('Something went wrong. Possible Reason: <br> Directory '.$this->uniqid.' already Exists.<br> Check the FTP settings under Settings Admin menu.');
        }    
     }
     
     function upload_wp($data )
     {
        global $wpdb;
        $upload_dir = get_option('wp_demo_buddy_temp_dir');
        $q = 'SELECT name, state, one_click_demo FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $result = $wpdb->get_row($q);
        if($result->state !='1') return;
        $name = $result->name;
        //////////////////////////////////
        //upload wordpress.zip
         //////////////////////////////////
        $remote_file1 = $this->uniqid.'/wordpress.zip';
        $lastupdate = get_option('demobuddy_latest_wp');

        if(strtotime($this->date) > ($lastupdate + 86400) || !file_exists($upload_dir.'wordpress.zip'))
        {
            $wp = file_get_contents('https://wordpress.org/latest.zip');
            file_put_contents($upload_dir.'wordpress.zip', $wp);
            update_option('demobuddy_latest_wp', strtotime($this->date));
        }
        
        
        $filedir = $upload_dir.'wordpress.zip';
        
        if (!ftp_put($this->conn, $remote_file1, $filedir, FTP_BINARY)) 
        {
             $this->error[] = "There was a problem while UPLOADING wordpress.zip";
//             return;
        } 
        
        //////////////////////////////////
        //upload & execute wordpress unzip file
         //////////////////////////////////
        $remote_file2 = $this->uniqid.'/wp_unzip.php';
        $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/wp_unzip.txt';
        $filedir = $upload_dir.'wp_unzip-'.$this->uniqid.'.php';
        
        $WPUnzip = file_get_contents($fileurl);
       
        $WPUnzip = str_replace('[FILENAME]', 'wordpress.zip', $WPUnzip);
        file_put_contents($filedir, $WPUnzip);

        if (!ftp_put($this->conn, $remote_file2, $filedir, FTP_BINARY)) 
        {
             $this->error[] = "There was a problem while UPLOADING wp_unzip.php (wordpress.zip)";
//             return;
        } 
        $url = trailingslashit($this->ftpurl).$this->uniqid.'/';
        $ret = file_get_contents($url.'wp_unzip.php');

        if(!$ret)
        {
            $this->error[] = "There was a problem while EXECUTING wp_unzip.php (wordpress.zip)";
//            return;
        }
        else
        {
           $this->error[] = $ret; 
        }
        unlink($filedir);
        ftp_delete($this->conn,$remote_file1);
        //////////////////////////////////
        //////////////////////////////////
        //upload wp_config.php unzip file
        //////////////////////////////////
        //////////////////////////////////
        $remote_file3 = $this->uniqid.'/wordpress/wp-config.php';
        $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/wp-config.txt';
        $filedir = $upload_dir.'wp-config-'.$this->uniqid.'.php';
        
        $wpconfig = file_get_contents($fileurl);        
       
        $wpconfig = str_replace('database_name_here', $this->dbname, $wpconfig);
        $wpconfig = str_replace('username_here', $this->dbuser, $wpconfig);
        $wpconfig = str_replace('password_here', $this->dbpass, $wpconfig);
        $wpconfig = str_replace('server_here', $this->dbserver, $wpconfig);
        $wpconfig = str_replace('tblprefix', $this->uniqid.'_', $wpconfig);
        
        for($i=1; $i<9; $i++)
        {
            $wpconfig = str_replace('unique'.$i, md5($this->uniqid.rand(99,999)), $wpconfig);
        }
        
        file_put_contents($filedir, $wpconfig);
                
        if (!ftp_put($this->conn, $remote_file3, $filedir, FTP_BINARY)) 
        {
             $this->error[] = "There was a problem while UPLOADING wp-config.php";
//             return;
        } 
        unlink($filedir);
        
         //////////////////////////////////
         //////////////////////////////////
        //install Wordpress
         //////////////////////////////////
          //////////////////////////////////
        
        $q = 'SELECT name FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $name = $wpdb->get_var($q);
        $wpurl = trailingslashit($this->ftpurl).$this->uniqid.'/wordpress/';
        
        $remote_file4 = $this->uniqid.'/wordpress/reminstall.php';
        $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/reminstall.txt';
        $filedir = $upload_dir.'reminstall-'.$this->uniqid.'.php';
        
        $reminstall = file_get_contents($fileurl);      
       
        $reminstall = str_replace('[weblog_title]', $name, $reminstall);
        $reminstall = str_replace('[user_login]', $data['email'], $reminstall);
        $reminstall = str_replace('[admin_email]', $data['email'], $reminstall);
        $reminstall = str_replace('[admin_password]', $this->pwd, $reminstall);
        $reminstall = str_replace('[url]', $wpurl, $reminstall);

        file_put_contents($filedir, $reminstall);
                
        if (!ftp_put($this->conn, $remote_file4, $filedir, FTP_BINARY)) 
        {
             $this->error[] = "There was a problem UPLOADING reminstall.php";
          //   return;
        } 
        
        $ret = file_get_contents($wpurl.'reminstall.php');
        if(!$ret)
        {
            $this->error[] = "There was a problem while EXECUTING reminstall.php";
          //  return;
        }
        else
        {
           $this->error[] = $ret; 
        }
        $wpdb->update($wpdb->prefix.'demobuddy_instances',
                        array(  'url' => $wpurl,
                                'password' => $this->pwd ),
                        array(  'uniq'    => $this->uniqid),
                        array(  '%s',
                                '%s'),
                        array(  '%s'));
        unlink($filedir);
        
        
       //////////////////////////////////
        //upload plugin
       //////////////////////////////////
         $upload_dir2 = get_option('wp_demo_buddy_upload_dir');
        $q = 'SELECT plugin_url, plugin_url_o FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $row = $wpdb->get_row($q);
       
        if($row->plugin_url != '')
        {
            $remote_file5 = $this->uniqid.'/wordpress/wp-content/plugins/'.$row->plugin_url_o;
          
          
            $upload = wp_upload_dir();
            $filedir =  $upload_dir2.$row->plugin_url; 
          
            if (!ftp_put($this->conn, $remote_file5, $filedir, FTP_BINARY)) 
            {
                 $this->error[] = "There was a problem while UPLOADING  ".$row->plugin_url_o;
//                 return;
            } 
            
            
            //////////////////////////////////
            //upload & execute plugin unzip file
             //////////////////////////////////
            $remote_file6 = $this->uniqid.'/wordpress/wp-content/plugins/wp_unzip.php';
            $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/wp_unzip.txt';
            $filedir =$upload_dir.'wp_unzip-'.$this->uniqid.'.php';
            
            $WPUnzip = file_get_contents($fileurl);
           
            $WPUnzip = str_replace('[FILENAME]', $row->plugin_url_o, $WPUnzip);
            
            file_put_contents($filedir, $WPUnzip);
    
            if (!ftp_put($this->conn, $remote_file6, $filedir, FTP_BINARY)) 
            {
                 $this->error[] = "There was a problem while UPLOADING wp_unzip.php (".$row->plugin_url_o.")";
//                 return;
            } 
            $url = trailingslashit($this->ftpurl).$this->uniqid.'/wordpress/wp-content/plugins/';
            $ret = file_get_contents($url.'wp_unzip.php');
            if(!$ret)
            {
                $this->error[] = "There was a problem while EXECUTING wp_unzip.php (plugin - ".$row->plugin_url_o.")";
//                return;
            }
            else
            {
               $this->error[] = $ret; 
            }
        
            unlink($filedir);
            ftp_delete($this->conn,$remote_file5);   
            ftp_delete($this->conn,$remote_file6);         
        } 


            
        
       //////////////////////////////////
       //upload theme
       //////////////////////////////////
        $q = 'SELECT theme_url, theme_url_o FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $row = $wpdb->get_row($q);
 
        if($row->theme_url != '')
        {
            $remote_file7 = $this->uniqid.'/wordpress/wp-content/themes/'.$row->theme_url_o;
          
            $upload = wp_upload_dir();
            $filedir =  $upload_dir2.$row->theme_url; 
          
            if (!ftp_put($this->conn, $remote_file7, $filedir, FTP_BINARY)) 
            {
                 $this->error[] = "There was a problem while UPLOADING Theme ".$row->theme_url_o.print_r($row,true);
//                 exit; return;
            } 
            
            
            //////////////////////////////////
            //upload & execute theme unzip file
             //////////////////////////////////
            $remote_file8 = $this->uniqid.'/wordpress/wp-content/themes/wp_unzip.php';
            $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/wp_unzip.txt';
            $filedir = $upload_dir.'wp_unzip-'.$this->uniqid.'.php';
            
            $WPUnzip = file_get_contents($fileurl);
           
            $WPUnzip = str_replace('[FILENAME]', $row->theme_url_o, $WPUnzip);
            
            file_put_contents($filedir, $WPUnzip);
    
            if (!ftp_put($this->conn, $remote_file8, $filedir, FTP_BINARY)) 
            {
                 $this->error[] = "There was a problem while UPLOADING wp_unzip.php (".$row->theme_url_o.")";
//                 return;
            } 
            $url = trailingslashit($this->ftpurl).$this->uniqid.'/wordpress/wp-content/themes/';
            $ret = file_get_contents($url.'wp_unzip.php');
            if(!$ret)
            {
                $this->error[] = "There was a problem while EXECUTING wp_unzip.php (theme - ".$row->plugin_url_o.")";
//                return;
            }
            else
            {
               $this->error[] = $ret; 
            }
         
            ftp_delete($this->conn,$remote_file7);
            ftp_delete($this->conn,$remote_file8);          
        }         
        
        
                
        /////////////////////////////////////
        /////Upload and execute Demobuddy Remote plugin
        /////////////////////////////////       
        $q = 'SELECT dis_menus, dis_access, ad_code FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $res = $wpdb->get_row($q);
        
        $remote_file9 = $this->uniqid.'/wordpress/wp-content/plugins/demobuddyremote.php';
        $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/demobuddyremote.txt';
        $filedir = $upload_dir.'demobuddyremote-'.$this->uniqid.'.php';
        
        $demobuddyremote = file_get_contents($fileurl);
        //////////////// DISABLE MENU //////////////////////////////
        $dismenus_arr = json_decode($res->dis_menus,true);
        $dismenus = "'".implode("','",$dismenus_arr)."'";       
        $demobuddyremote = str_replace('[menupages]', $dismenus, $demobuddyremote);
        
        //////////////// DISABLE ACCES TO PLUGIN / THEMES //////////////////////////////
        $disaccess_arr = json_decode($res->dis_access,true);
        if(in_array('themes.php', $disaccess_arr) )
            $dislist_th = array('edit_themes',
                                'upload_themes',
                                'delete_themes',
                                'install_themes',
                                'update_themes');
        else
            $dislist_th = array();
            
            
        if(in_array('plugins.php', $disaccess_arr))
            $dislist_pl = array('activate_plugins',
                                'update_plugins',
                                'install_plugins',
                                'upload_plugins',
                                'delete_plugins',
                                'edit_plugins');
        else
            $dislist_pl = array();
            
        $dislist_arr = array_merge($dislist_th, $dislist_pl); 
        
        $dislist = "'".implode("','", $dislist_arr). "'";
                
        $demobuddyremote = str_replace('[disaccess]', $dislist, $demobuddyremote);
        
 
        
        //////////////////////// SEND TO REMOTE///////////////////////////////
        
        file_put_contents($filedir, $demobuddyremote);

        if (!ftp_put($this->conn, $remote_file9, $filedir, FTP_BINARY)) 
        {
             $this->error[] = "There was a problem while uploading demobuddyremote.php ";
//             return;
        } 
        $url = trailingslashit($this->ftpurl).$this->uniqid.'/wordpress/wp-content/plugins/';
        $ret = file_get_contents($url.'demobuddyremote.php?rem=1');
        if(!$ret)
        {
            $this->error[] = "There was a problem while EXECUTING demobuddyremote.php ";
//            return;
        }
        else
        {
           $this->error[] = $ret; 
        }
        unlink($filedir);
        
        
        if($dat->noutgoingemail == '1')
        return;
        //////////////////////////////
        //// SEND EMAIL//////////////
        /////////////////////////////
        $gensettings = json_decode(get_option('demobuddy_gensettings'));  
        $subject =  str_replace('[PRODUCT NAME]', $name, $gensettings->fromsubject);
        
        $user = new WP_User($user_id);
        
        $message = str_replace('[PRODUCT NAME]', $name, nl2br($gensettings->frommessage));
        $message = str_replace('[URL]', $wpurl, $message);
        $message = str_replace('[USERNAME]',  $data['email'], $message);
        $message = str_replace('[PASSWORD]', $this->pwd, $message);
        $message = str_replace('[FIRSTNAME]', $user->first_name, $message);
        $message = str_replace('[LASTNAME]', $user->last_name, $message);
        
        $headers = array('From: '.$gensettings->admin_name.' <'.$gensettings->admin_email.'>');
        
    //    file_put_contents(DemoBuddy6_LOG_DIR.'/email_log'.$dir.'.txt' , $subject."\r\n".$message."\r\n".print_r($headers,true));
         
        add_filter( 'wp_mail_content_type', array($this,'set_content_type' ));     
        wp_mail( $data['email'], $subject , $message );
        remove_filter( 'wp_mail_content_type', array($this,'set_content_type' ));
                                
        ftp_close($this->conn);
        return $wpurl;
     }
    function create_folder_local($data)
     {
        $dir = ABSPATH.$this->uniqid; 
        if(!mkdir($dir, 0755))
        {
            $this->error[] = "There was a problem while CREATING folder ".$dir; 
            exit('Something went wrong creating folder.');
        } 
        return $dir;     
     }
     
     
     function upload_wp_local($data, $dir)
     {
        global $wpdb;
        $upload_dir = get_option('wp_demo_buddy_temp_dir');
        $q = 'SELECT name, state, one_click_demo  FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $result = $wpdb->get_row($q);
        if($result->state !='1') return;
        $name = $result->name;
        //////////////////////////////////
        //upload wordpress.zip
         //////////////////////////////////
       
        $lastupdate = get_option('demobuddy_latest_wp');

        if(strtotime($this->date) > ($lastupdate + 86400) || !file_exists($upload_dir.'wordpress.zip'))
        {
            $wp = file_get_contents('https://wordpress.org/latest.zip');
            file_put_contents($upload_dir.'wordpress.zip', $wp);
            update_option('demobuddy_latest_wp', strtotime($this->date));
        }
        

     $filedir = $upload_dir.'wordpress.zip';

      $remote_file1 = $dir.'/wordpress.zip';
    
        if (!copy($filedir, $remote_file1)) 
        {
             $this->error[] = "There was a problem while COPYING wordpress.zip";
//             return;
        } 

        //////////////////////////////////
        //upload & execute wordpress unzip file
        //////////////////////////////////
        $remote_file2 = $dir.'/wp_unzip.php';
        $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/wp_unzip.txt';
        
        $WPUnzip = file_get_contents($fileurl);
       
        $WPUnzip = str_replace('[FILENAME]', 'wordpress.zip', $WPUnzip);

        if (!file_put_contents($remote_file2, $WPUnzip)) 
        {
             $this->error[] = "There was a problem while UPLOADING wp_unzip.php (wordpress.zip)";
//             return;
        } 
        file_get_contents(trailingslashit(home_url()).$this->uniqid.'/wp_unzip.php');
        
        unlink($remote_file1);
        unlink($remote_file2);
        //////////////////////////////////
        //////////////////////////////////
        //upload wp_config.php unzip file
        //////////////////////////////////
        //////////////////////////////////
        $remote_file3 = $dir.'/wordpress/wp-config.php';
        $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/wp-config.txt';
        
        
        $wpconfig = file_get_contents($fileurl);        
       
        $wpconfig = str_replace('database_name_here', DB_NAME, $wpconfig);
        $wpconfig = str_replace('username_here', DB_USER, $wpconfig);
        $wpconfig = str_replace('password_here', DB_PASSWORD, $wpconfig);
        $wpconfig = str_replace('server_here', DB_HOST, $wpconfig);
        $wpconfig = str_replace('tblprefix', $this->uniqid.'_', $wpconfig);
        
        for($i=1; $i<9; $i++)
        {
            $wpconfig = str_replace('unique'.$i, md5($dir.rand(99,999)), $wpconfig);
        }
                
        if (!file_put_contents($remote_file3, $wpconfig)) 
        {
             $this->error[] = "There was a problem while UPLOADING wp-config.php";
//             return;
        } 
        
         //////////////////////////////////
         //////////////////////////////////
        //install Wordpress
         //////////////////////////////////
          //////////////////////////////////
        
        $q = 'SELECT name FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $name = $wpdb->get_var($q);
        $wpurl = trailingslashit(home_url()).$this->uniqid.'/wordpress/';
        
        $remote_file4 = $dir.'/wordpress/reminstall.php';
        $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/reminstall.txt';
        
        $reminstall = file_get_contents($fileurl);      
       
        $reminstall = str_replace('[weblog_title]', $name, $reminstall);
        $reminstall = str_replace('[user_login]', $data['email'], $reminstall);
        $reminstall = str_replace('[admin_email]', $data['email'], $reminstall);
        $reminstall = str_replace('[admin_password]', $this->pwd, $reminstall);
        $reminstall = str_replace('[url]', $wpurl, $reminstall);
                
        if (!file_put_contents($remote_file4, $reminstall))
        {
             $this->error[] = "There was a problem UPLOADING reminstall.php";
          //   return;
        } 
        
        $ret = file_get_contents($wpurl.'reminstall.php');
        if(!$ret)
            $this->error[] = "There was a problem while EXECUTING reminstall.php";
        else
           $this->error[] = $ret; 

       //////////////////////////////////
       //upload plugin
       //////////////////////////////////
        $upload_dir2 = get_option('wp_demo_buddy_upload_dir');
        $q = 'SELECT plugin_url, plugin_url_o FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $row = $wpdb->get_row($q);
       
        if($row->plugin_url != '')
        {
            $remote_file5 = $dir.'/wordpress/wp-content/plugins/'.$row->plugin_url_o;
            $filedir =  $upload_dir2.$row->plugin_url; 
          
            if (!copy($filedir,$remote_file5 )) 
            {
                 $this->error[] = "There was a problem while UPLOADING  ".$row->plugin_url_o;
//                 return;
            } 
          //  exit;
            
            //////////////////////////////////
            //upload & execute plugin unzip file
            //////////////////////////////////
            $remote_file6 = $dir.'/wordpress/wp-content/plugins/wp_unzip.php';
            $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/wp_unzip.txt';
            
            $WPUnzip = file_get_contents($fileurl);
           
            $WPUnzip = str_replace('[FILENAME]', $row->plugin_url_o, $WPUnzip);
            if (!file_put_contents($remote_file6, $WPUnzip))
            {
                 $this->error[] = "There was a problem while UPLOADING wp_unzip.php (".$row->plugin_url_o.")";
//                 return;
            } 
            $url = trailingslashit($this->url).'/wp-content/plugins/wp_unzip.php';
            file_get_contents($url);
           unlink($remote_file5); 
           unlink($remote_file6);         
        } 

       //////////////////////////////////
       //upload theme
       //////////////////////////////////
        $q = 'SELECT theme_url, theme_url_o FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $row = $wpdb->get_row($q);

        if($row->theme_url != '')
        {
            $remote_file7 = $dir.'/wordpress/wp-content/themes/'.$row->theme_url_o;           

            $upload = wp_upload_dir();
            $filedir =  $upload_dir2.$row->theme_url; 

            if (!copy($filedir,$remote_file7 ))
            {
                 $this->error[] = "There was a problem while UPLOADING Theme ".$row->theme_url_o.print_r($row,true);
//                 return;
            } 
            
            
            //////////////////////////////////
            //upload & execute themes unzip file
            //////////////////////////////////

            $remote_file8 = $dir.'/wordpress/wp-content/themes/wp_unzip.php';

            $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/wp_unzip.txt';

            $WPUnzip = file_get_contents($fileurl);
           
            $WPUnzip = str_replace('[FILENAME]', $row->theme_url_o, $WPUnzip);
            if (!file_put_contents($remote_file8, $WPUnzip))
            {
                 $this->error[] = "There was a problem while UPLOADING wp_unzip.php (".$row->theme_url_o.")";
//                 return;
            } 
            $url = trailingslashit($this->url).'/wp-content/themes/wp_unzip.php';
            file_get_contents($url);
            unlink($remote_file7); 
            unlink($remote_file8);           
        }         
 
                
        /////////////////////////////////////
        /////Upload and execute Demobuddy Remote plugin
        /////////////////////////////////       
        $q = 'SELECT dis_menus, dis_access, ad_code FROM '.$wpdb->prefix.'demobuddy_products WHERE id = '.$data['product_id'];
        $res = $wpdb->get_row($q);
        
        $remote_file9 = $dir.'/wordpress/wp-content/plugins/demobuddyremote.php';
        $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/demobuddyremote.txt';
        
        $demobuddyremote = file_get_contents($fileurl);
        //////////////// DISABLE MENU //////////////////////////////
        $dismenus_arr = json_decode($res->dis_menus,true);
        $dismenus = "'".implode("','",$dismenus_arr)."'";       
        $demobuddyremote = str_replace('[menupages]', $dismenus, $demobuddyremote);
        
        //////////////// DISABLE ACCES TO PLUGIN / THEMES //////////////////////////////
        $disaccess_arr = json_decode($res->dis_access,true);
        if(in_array('themes.php', $disaccess_arr) )
            $dislist_th = array('edit_themes',
                                'upload_themes',
                                'delete_themes',
                                'install_themes',
                                'update_themes');
        else
            $dislist_th = array();
            
            
        if(in_array('plugins.php', $disaccess_arr))
            $dislist_pl = array('activate_plugins',
                                'update_plugins',
                                'install_plugins',
                                'upload_plugins',
                                'delete_plugins',
                                'edit_plugins');
        else
            $dislist_pl = array();
            
        $dislist_arr = array_merge($dislist_th, $dislist_pl); 
        
        $dislist = "'".implode("','", $dislist_arr). "'";
                
        $demobuddyremote = str_replace('[disaccess]', $dislist, $demobuddyremote);
        
       
        //////////////////////// SEND TO REMOTE///////////////////////////////
 

        if (!file_put_contents($remote_file9, $demobuddyremote)) 
        {
             $this->error[] = "There was a problem while uploading demobuddyremote.php ";
//             return;
        } 
        $url = trailingslashit($this->url).'/wp-content/plugins/';
        $ret = file_get_contents($url.'demobuddyremote.php?rem=1');
        if(!$ret)
        {
            $this->error[] = "There was a problem while EXECUTING demobuddyremote.php ";
//            return;
        }
        else
        {
           $this->error[] = $ret; 
        }
        //unlink($filedir);
        if($dat->noutgoingemail == '1')
            return;
        
        
        //////////////////////////////
        //// SEND EMAIL//////////////
        /////////////////////////////
        $gensettings = json_decode(get_option('demobuddy_gensettings'));  
        $subject =  str_replace('[PRODUCT NAME]', $name, $gensettings->fromsubject);
        
        $user = new WP_User($user_id);
        
        $message = str_replace('[PRODUCT NAME]', $name, nl2br($gensettings->frommessage));
        $message = str_replace('[URL]', $wpurl, $message);
        $message = str_replace('[USERNAME]',  $data['email'], $message);
        $message = str_replace('[PASSWORD]', $this->pwd, $message);
        $message = str_replace('[FIRSTNAME]', $user->first_name, $message);
        $message = str_replace('[LASTNAME]', $user->last_name, $message);
        
        $headers = array('From: '.$gensettings->admin_name.' <'.$gensettings->admin_email.'>');
        
    //    file_put_contents(DemoBuddy6_LOG_DIR.'/email_log'.$dir.'.txt' , $subject."\r\n".$message."\r\n".print_r($headers,true));
         
        add_filter( 'wp_mail_content_type', array($this,'set_content_type' ));     
        wp_mail( $data['email'], $subject , $message );
        remove_filter( 'wp_mail_content_type', array($this,'set_content_type' ));
        return $wpurl;
     }

     
     function logerror()
     {
        $errors = implode("\n\r",$this->error);
        $upload_dir = get_option('wp_demo_buddy_errlog_dir');
        file_put_contents($upload_dir.'ftp_log'.$this->uniqid.'.txt' , $errors."\r\n----\r\n", FILE_APPEND);
        return;
     }
     
     function set_content_type( $content_type ) 
     {
    	return 'text/html';
     } 
     
          
     function insert_user($data)
     {  
        global $wpdb;
        $email = trim($data['email']);
        
//        $q = "SELECT ID FROM ".$wpdb->users." 
//                WHERE (user_email = '".$email."' 
//                    OR user_login = '".$email."')";
//        $user_id = $wpdb->get_var($q);
//        
//        //$user_id = username_exists( $email );
//        if ( !$user_id) 
//        {
//        	$user_id = wp_create_user( $email, $this->pwd, $email );
//            update_user_meta($user_id,'first_name',$data['fname'] );
//            update_user_meta($user_id, 'last_name',$data['lname'] );
//        }             
        
        if($this->arconfirm != '1')
            $data['optin'] = '1';
        else
            $data['optin']= ($data['optin'] == '1')? '1': '0';
            $up_dat = array(  'product_id'    => $data['product_id'],
                                'uniq'          => $this->uniqid,
                                'username'      => $email,
                                'first_name'    => $data['fname'],
                                'last_name'     => $data['lname'],
                                'user_id'       => $user_id,
                                'username'      => $email,
                                'password'      => $this->pwd,
                                'url'           => $this->url,
                                'optin'         => $data['optin']);
       // echo '<pre>'.print_r($up_dat,true).'</pre>';
                                
        $wpdb->insert($wpdb->prefix.'demobuddy_instances',
                        $up_dat,
                        array(  '%d',
                                '%s',
                                '%s',
                                '%s',
                                '%s',
                                '%d',
                                '%s',
                                '%s',
                                '%s',
                                '%d'));
                                
        return;
     }
}