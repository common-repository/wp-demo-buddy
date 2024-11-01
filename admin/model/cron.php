<?php
class demobuddy_demo_delexp
{
     function __construct()
     {
        $this->date = date_i18n('Y-m-d H:i:s', current_time('timestamp'));

        $this->error = array(); 
        
        $data = get_option('demobuddy_gensettings');
        $dat = json_decode($data);
        $this->mode = $dat->mode;
        $this->ftpurl = $dat->ftpurl;
        
        $this->dbname = $dat->dbname;
        $this->dbuser = $dat->dbuser;
        $this->dbpass = $dat->dbpass;
        $this->dbserver = $dat->dbserver;
        
        $this->conn = ftp_connect($dat->ftpserver);
        $this->login = ftp_login($this->conn, $dat->ftpuser, $dat->ftppass);
        if (!$this->conn || !$this->login) $this->error[] = 'Connection attempt failed!';
     }  
     function check_expiry()
     {
        if($this->mode == 'local')
            $this->del_expiry_local();
        else
            $this->del_expiry_ftp();
        return;
     }
     
     function del_expiry_local()
     {
        global $wpdb;
        $curdate = date_i18n('Y-m-d H:i:s', current_time('timestamp'));
        $q1 = "SELECT id, del_after FROM ".$wpdb->prefix."demobuddy_products 
                WHERE del_after != '0' 
                    OR del_after IS NOT NULL";
        $results = $wpdb->get_results($q1);
        //print_r($results);
        if(empty($results)) return;
        
        foreach($results as $res)
        {
           $q2 = "SELECT uniq FROM ".$wpdb->prefix."demobuddy_instances 
                    WHERE  '".$curdate."' > DATE_ADD(start_date, INTERVAL ".$res->del_after." HOUR) 
                        AND product_id = ".$res->id." 
                        AND deleted != '1'";
            $results2 = $wpdb->get_col($q2);
           // print_r($results2);
            if(empty($results2)) continue;
            
            foreach($results2 as $id)
            {
                if($id == '') continue;
                //$dir = 'dd'.$id;
                $remote_file6 = ABSPATH.'del-expired'.$id.'.php';
                $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/del-expired.txt';
                //$filedir = DemoBuddy6_PLUGIN_DIR.'/front/model/uploads/del-expired-'.$id.'.php';
                
                $delexpired = file_get_contents($fileurl);
               
                $delexpired = str_replace('[dir]', $id, $delexpired);
                $delexpired = str_replace('[host]', $this->dbserver, $delexpired);
                $delexpired = str_replace('[dbname]', $this->dbname, $delexpired);
                $delexpired = str_replace('[user]', $this->dbuser, $delexpired);
                $delexpired = str_replace('[pass]', $this->dbpass, $delexpired);
        
                if (!file_put_contents($remote_file6, $delexpired)) 
                     $this->error[] = "There was a problem while uploading del-expired.php (".$id.")\n";

                $url = trailingslashit(home_url()).'del-expired'.$id.'.php';
                
                $ret = file_get_contents($url);
                
                if(!$ret)
                    $this->error[] = "There was a problem while executing del-expired.php (".$id.")\n";
                else
                   $this->error[] = $ret; 
 
                unlink($remote_file6);
                $errors = implode("\n\r",$this->error);
                file_put_contents(DemoBuddy6_LOG_DIR.'/ftp_err_log'.$id.'.txt' , $errors."\r\n----\r\n", FILE_APPEND);
                
                $wpdb->update($wpdb->prefix.'demobuddy_instances',
                                array('deleted' => '1',
                                        'end_date' => $curdate),
                                array('uniq' => $id),
                                array('%d','%s'));
            }  
        }
     }    
     
     
     function del_expiry_ftp()
     {
        global $wpdb;
        $curdate = date_i18n('Y-m-d H:i:s', current_time('timestamp'));
        $q1 = "SELECT id, del_after FROM ".$wpdb->prefix."demobuddy_products 
                WHERE del_after != '0' 
                    OR del_after IS NOT NULL";
        $results = $wpdb->get_results($q1);
        //print_r($results);
        if(empty($results)) return;
        
        foreach($results as $res)
        {
           $q2 = "SELECT uniq FROM ".$wpdb->prefix."demobuddy_instances 
                    WHERE  '".$curdate."' > DATE_ADD(start_date, INTERVAL ".$res->del_after." HOUR) 
                        AND product_id = ".$res->id." 
                        AND deleted != '1'";
            $results2 = $wpdb->get_col($q2);
           // print_r($results2);
            if(empty($results2)) continue;
            
            foreach($results2 as $id)
            {
                if($id == '') continue;
                //$dir = 'dd'.$id;
                $remote_file6 = 'del-expired'.$id.'.php';
                $fileurl = DemoBuddy6_PLUGIN_URL.'/front/model/uploads/del-expired.txt';
                $filedir = DemoBuddy6_PLUGIN_DIR.'/front/model/uploads/del-expired-'.$id.'.php';
                
                $delexpired = file_get_contents($fileurl);
               
                $delexpired = str_replace('[dir]', $id, $delexpired);
                $delexpired = str_replace('[host]', $this->dbserver, $delexpired);
                $delexpired = str_replace('[dbname]', $this->dbname, $delexpired);
                $delexpired = str_replace('[user]', $this->dbuser, $delexpired);
                $delexpired = str_replace('[pass]', $this->dbpass, $delexpired);
                
                file_put_contents($filedir, $delexpired);
        
                if (!ftp_put($this->conn, $remote_file6, $filedir, FTP_BINARY)) 
                     $this->error[] = "There was a problem while uploading del-expired.php (".$id.")\n";

                $url = trailingslashit($this->ftpurl).'del-expired'.$id.'.php';
                
                $ret = file_get_contents($url);
                
                if(!$ret)
                    $this->error[] = "There was a problem while executing del-expired.php (".$id.")\n";
                else
                   $this->error[] = $ret; 
            
                unlink($filedir); 
                ftp_delete($this->conn, $remote_file6);
                $errors = implode("\n\r",$this->error);
                file_put_contents(DemoBuddy6_LOG_DIR.'/ftp_err_log'.$id.'.txt' , $errors."\r\n----\r\n", FILE_APPEND);
                
                $wpdb->update($wpdb->prefix.'demobuddy_instances',
                                array('deleted' => '1',
                                        'end_date' => $curdate),
                                array('uniq' => $id),
                                array('%d','%s'));
            }  
        }
     }
}    


$delinst = new demobuddy_demo_delexp() ;
$delinst->check_expiry();
?>