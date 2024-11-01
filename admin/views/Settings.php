<?php if ( ! defined( 'ABSPATH' ) ) exit;  

require_once(DemoBuddy6_PLUGIN_DIR. '/admin/model/admin-model.php'); 
$adminmodel=new DemoBuddy_admin_model(); 
if($_POST['formname'] == 'demobuddy')
    $adminmodel->setttings_saveform($_POST); 

$data = $adminmodel->setttings_getform(); 
//echo '<pre>'.print_r($data).'</pre>';
$showftp = $showlocal = 'hidden';
if($data->mode == 'local')
{
    $showlocal = '';
    $checklocal = 'checked';
}
    
if($data->mode == 'ftp' || $data->mode =='')
{
    $showftp = '';
    $checkftp = 'checked';
}
    
  ?>
  <div class="wrap" style="background: #fff;">
    <div class="container-fluid col-md-12" style="background: #fff;">
      <?php include( 'admin_menu.php') ?>
        <div style="clear: both;">
        </div>
        <div class="col-md-12 alert alert-info">
        <h4>Demo Installation </h4>
        <div class="form-group">
       
         <div class="col-sm-3">
              <input <?php echo $checkftp; ?>  title="<?php _e('Will create the Demo in the FTP Location','DemoBuddy'); ?>" data-toggle="tooltip" type="radio" name="mode" class="modeselect" value="ftpmode" /> FTP ((Remote Location)
            </div> 
            <div class="col-sm-3">
              <input <?php echo $checklocal; ?> title="<?php _e('Will create the Demo installation in the current domain. ','DemoBuddy');  ?> " data-toggle="tooltip"   type="radio" name="mode" class="modeselect" value="localmode" /> Local
            </div>
            </div>
        </div>
        <hr /><br />
        <?php ///////////////////////////////////////////////////////////////////////////// ?>        
<?php //////////////////////////////////localmode///////////////////////////////////////// ?>          
<?php ///////////////////////////////////////////////////////////////////////////// ?> 
 <div class="col-md-12 <?php echo $showlocal; ?> modediv" id="localmode" >
        <form action="" method="post" id="My_Form" class="form-horizontal">
          <h4>Outgoing Email</h4>
          <div class="col-sm-12">
                    
          <div class="form-group">
            <div class="col-sm-7">
              <label for="adminname">
                 <?php _e('Do not send email', 'DemoBuddy'); ?> <input type="checkbox" name="noutgoingemail" value="1" <?php echo ($data->noutgoingemail == '1')? 'checked':'' ?> class="form-control" id="noutgoingemail"/>
              </label>
            </div>
          </div>            
          <div class="form-group">
            <div class="col-sm-3">
              <label for="adminname">
                <?php _e('From Name', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input  required="required" type="text" name="admin_name" value="<?php echo $data->admin_name; ?>" class="form-control" id="adminname"/>
            </div>
          </div>
            <div class="form-group">
            <div class="col-sm-3">
              <label for="adminemail">
                <?php _e('From Email', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input  required="required"  type="text" name="admin_email" value="<?php echo $data->admin_email; ?>" class="form-control" id="adminemail"/>
            </div>
          </div>
          
        <?php
        $subject = 'Your Demo Login for [PRODUCT NAME]';
        $data->fromsubject = (trim($data->fromsubject) == '')? $subject: $data->fromsubject;
        
        $message = "Hi [FIRSTNAME], \r\n\r\nHere is your login details to [PRODUCT NAME] \r\n\r\nURL: [URL] \r\nUsername:[USERNAME] \r\nPassword:[PASSWORD] \r\n\r\nRegards.\r\n-Admin";
        $data->frommessage = (trim($data->frommessage) == '')? $message: $data->frommessage;
        
        ?>
        
          
        <div class="form-group">
            <div class="col-sm-3">
              <label for="fromsubject">
                <?php _e('Subject', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input  required="required" type="text" name="fromsubject" value="<?php echo stripslashes($data->fromsubject); ?>" class="form-control" id="fromsubject"/>
            </div>
          </div>
          
          
        <div class="form-group">
            <div class="col-sm-3">
              <label for="frommessage">
                <?php _e('Message', 'DemoBuddy'); ?>:<br /> 
              </label><div class="help-block">SHORTCODES:<br />[PRODUCT NAME]<br />[FIRSTNAME]<br />[LASTNAME]<br />[URL]<br />[USERNAME]<br />[PASSWORD]</div>  
            </div>
            <div class="col-sm-4">
              <textarea  required="required" rows="8" name="frommessage" value="<?php echo $data->frommessage; ?>" class="form-control" id="frommessage"><?php echo stripslashes($data->frommessage); ?></textarea>
              
            </div>
           
          </div>
          
          
          </div>

           <div class="col-sm-12">         
          <div class="alert alert-warning col-md-12 ">
          <h4><?php echo __('CRON SETTINGS :', 'DemoBuddy'); ?></h4> 
          <div class="form-group col-md-12">
              <label for="CronCommand">
                <?php _e('Cron Command', 'DemoBuddy'); ?>:<br /> 
              </label>

            <input id="CronCommand" class="form-control" value="<?php echo 'wget '. trailingslashit(home_url()).'/?dEMoBuDdYCron=dEMoBuDdYCron' ?>" />
          <p><?php echo __('Once per Hour ', 'DemoBuddy'); ?></p> 
          </div>
            
            
            </div>       
          <div class="col-sm-3">
            <input type="hidden" name="formname" value="demobuddy" />
            <input type="submit" class="btn btn-primary" value="Submit" />
            </div>
          <div class="col-sm-4">&nbsp;</div>
<?php wp_nonce_field( 'demobuddy_settings_nonce_action', 'demobuddy_settings_nonce_field' ); ?>
<input type="hidden" name="mode" value="local" /> </div> 

        </form>
        
        
        

        
    </div>
    
                  
<?php ///////////////////////////////////////////////////////////////////////////// ?>        
<?php //////////////////////////////////ftp mode///////////////////////////////////////// ?>          
<?php ///////////////////////////////////////////////////////////////////////////// ?>        
        <div class="col-md-12 <?php echo $showftp; ?> modediv" id="ftpmode" >
        <form action="" method="post" id="My_Form" class="form-horizontal">
        <h4><?php _e('FTP Info', 'DemoBuddy'); ?></h4>
        <div class="col-sm-12">
          <div class="form-group">
            <div class="col-sm-3">
              <label for="ftpserver">
                <?php _e('FTP Host', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input required="required" type="text" name="ftpserver" value="<?php echo $data->ftpserver; ?>" class="form-control" id="ftpserver"/>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <label for="ftpuser">
                <?php _e('FTP User', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input required="required" type="text" name="ftpuser" value="<?php echo $data->ftpuser; ?>" class="form-control" id="ftpuser"/>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <label for="ftppass">
                <?php _e('FTP Password', 'DemoBuddy'); ?>: <span class="pwtog"   data-rel="ftppass">&#x1f441;</span>
              </label>
            </div>
            <div class="col-sm-4">
              <input required="required"   type="password" name="ftppass" value="<?php echo $data->ftppass; ?>" class="form-control" id="ftppass"/>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <label for="ftpurl">
                <?php _e('URL', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input required="required"  type="text" name="ftpurl" value="<?php echo $data->ftpurl; ?>" class="form-control" id="ftpurl"/>
              <span class="help-block alert alert-info"><?php _e('URL to this FTP location that  begins with http://.', 'DemoBuddy'); ?></span>  
            </div>
          </div>
          </div>
          
          <h4>MySQL DB Info</h4>
          <div class="col-sm-12">
          
          <div class="form-group">
            <div class="col-sm-3">
              <label for="dbserver">
                <?php _e('DB Host', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input required="required"  type="text" name="dbserver" value="<?php echo (trim($data->dbserver) == '')? 'localhost':$data->dbserver; ?>" class="form-control" id="dbserver"/>
            </div>
          </div>           
          
           <div class="form-group">
            <div class="col-sm-3">
              <label for="dbname">
                <?php _e('DB Name', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input  required="required" type="text" name="dbname" value="<?php echo $data->dbname; ?>" class="form-control" id="dbname"/>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <label for="dbuser">
                <?php _e('DB User', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input  required="required" type="text" name="dbuser" value="<?php echo $data->dbuser; ?>" class="form-control" id="dbuser"/>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <label for="dbpass">
                <?php _e('DB Password', 'DemoBuddy'); ?>: <span class="pwtog"   data-rel="dbpass">&#x1f441;</span>
              </label>
            </div>
            <div class="col-sm-4">
              <input required="required"  type="password" name="dbpass" value="<?php echo $data->dbpass; ?>" class="form-control" id="dbpass"/>
            </div>
          </div>
         
          </div>
          
          <h4>Outgoing Email</h4>
          <div class="col-sm-12">
          
                    
          <div class="form-group">
            <div class="col-sm-7">
              <label for="adminname">
                <?php _e('Do not send email', 'DemoBuddy'); ?> <input type="checkbox" name="noutgoingemail" value="1" <?php echo ($data->noutgoingemail == '1')? 'checked':'' ?> class="form-control" id="noutgoingemail"/> 
              </label>
            </div>
          </div>  
          
          <div class="form-group">
            <div class="col-sm-3">
              <label for="adminname">
                <?php _e('From Name', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input  required="required" type="text" name="admin_name" value="<?php echo $data->admin_name; ?>" class="form-control" id="adminname"/>
            </div>
          </div>
            <div class="form-group">
            <div class="col-sm-3">
              <label for="adminemail">
                <?php _e('From Email', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input  required="required"  type="text" name="admin_email" value="<?php echo $data->admin_email; ?>" class="form-control" id="adminemail"/>
            </div>
          </div>
          
        <?php
        $subject = 'Your Demo Login for [PRODUCT NAME]';
        $data->fromsubject = (trim($data->fromsubject) == '')? $subject: $data->fromsubject;
        
        $message = "Hi [FIRSTNAME], \r\n\r\nHere is your login details to [PRODUCT NAME] \r\n\r\nURL: [URL] \r\nUsername:[USERNAME] \r\nPassword:[PASSWORD] \r\n\r\nRegards.\r\n-Admin";
        $data->frommessage = (trim($data->frommessage) == '')? $message: $data->frommessage;
        
        ?>
        
          
        <div class="form-group">
            <div class="col-sm-3">
              <label for="fromsubject">
                <?php _e('Subject', 'DemoBuddy'); ?>:
              </label>
            </div>
            <div class="col-sm-4">
              <input  required="required" type="text" name="fromsubject" value="<?php echo stripslashes($data->fromsubject); ?>" class="form-control" id="fromsubject"/>
            </div>
          </div>
          
          
        <div class="form-group">
            <div class="col-sm-3">
              <label for="frommessage">
                <?php _e('Message', 'DemoBuddy'); ?>:<br /> 
              </label><div class="help-block">SHORTCODES:<br />[PRODUCT NAME]<br />[FIRSTNAME]<br />[LASTNAME]<br />[URL]<br />[USERNAME]<br />[PASSWORD]</div>  
            </div>
            <div class="col-sm-4">
              <textarea  required="required" rows="8" name="frommessage" value="<?php echo $data->frommessage; ?>" class="form-control" id="frommessage"><?php echo stripslashes($data->frommessage); ?></textarea>
              
            </div>
           
          </div>
          
          
          
          </div>

          <div class="alert alert-warning col-md-12 ">
          <h4><?php echo __('CRON SETTINGS :', 'DemoBuddy'); ?></h4> 
          <div class="form-group col-md-12">
              <label for="CronCommand">
                <?php _e('Cron Command', 'DemoBuddy'); ?>:<br /> 
              </label>

            <input id="CronCommand" class="form-control" value="<?php echo 'wget '. trailingslashit(home_url()).'/?dEMoBuDdYCron=dEMoBuDdYCron' ?>" />
          <p><?php echo __('Once per Hour ', 'DemoBuddy'); ?></p> 
          </div>
            
            
            </div>       
          <div class="col-sm-3">
            <input type="hidden" name="formname" value="demobuddy" />
            <input type="submit" class="btn btn-primary" value="Submit" />
            </div>
          <div class="col-sm-4">&nbsp;</div>
<?php wp_nonce_field( 'demobuddy_settings_nonce_action', 'demobuddy_settings_nonce_field' ); ?>
<input type="hidden" name="mode" value="ftp" />
        </form>
        </div>
    <div style="clear: both;">
    </div>
  </div> </div>