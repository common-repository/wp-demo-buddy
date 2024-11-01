<?php if ( ! defined( 'ABSPATH' ) ) exit;  

class DemoBuddy_db_model
{
    function  create_table()
    {
        global $wpdb;

                
         $sql[] = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."demobuddy_products (
                  id int(11) NOT NULL AUTO_INCREMENT,
                  name varchar(255) NOT NULL,
                  lang varchar(8) DEFAULT NULL,
                  state int(1) NOT NULL DEFAULT '0',
                  plugin_url text,
                  plugin_url_o text,
                  theme_url text,
                  theme_url_o text,
                  del_after int(11) DEFAULT NULL,
                  ar_code text,
                  form_style text,
                  ad_code text,
                  one_click_demo int(1) DEFAULT '0', 
                  dis_menus text,
                  dis_access varchar(255) DEFAULT NULL,
                  date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  PRIMARY KEY (id)
                ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ";     
        
        $sql[] = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."demobuddy_instances (
                  id int(11) NOT NULL AUTO_INCREMENT,
                  uniq varchar(255) DEFAULT NULL,
                  user_id int(11) DEFAULT NULL,
                  product_id int(11) NOT NULL,
                  url text,
                  username varchar(255) DEFAULT NULL,
                  password varchar(255) DEFAULT NULL,
                  optin int(1) DEFAULT '1',
                  block_access INT(1) NULL DEFAULT  '0',
                  start_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  end_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  deleted int(1) DEFAULT '0',
                  PRIMARY KEY (id)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";  
                
        $sql[]  = "ALTER TABLE  ".$wpdb->prefix."demobuddy_instances 
                    ADD first_name VARCHAR(255) NULL AFTER password, 
                    ADD last_name VARCHAR(255) NULL AFTER first_name;";
        $sql[]  = "ALTER TABLE   ".$wpdb->prefix."demobuddy_instances 
                    CHANGE  start_date  start_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ;" ;  
        $sql[]  = "ALTER TABLE  ".$wpdb->prefix."demobuddy_products 
                    ADD  one_click_demo INT( 1 ) NULL DEFAULT  '0' AFTER  ad_code ;";
        foreach($sql as $q)
        {
          $r  = $wpdb->query($q);
        }
    }
}
?>
