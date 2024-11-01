<?php if ( ! defined( 'ABSPATH' ) ) exit;  ?>
<div class="row">
<div class="col-md-4"> <img style="width: 350px;" class="logo" src="<?php echo DemoBuddy6_PLUGIN_URL.'/assets/demobuddy.jpg'; ?>"  /></div>
<div class="col-md-8" style="text-align: center;padding-top: 20px;display: none;"><a href="https://affordableplugins.com/wp-demo-buddy-free-to-pro/" target="_blank"><img style="width: 640px;" class="logo" src="<?php echo DemoBuddy6_PLUGIN_URL.'/assets/upgradebanner-720.jpg'; ?>"  /></a></div>
</div><br />
<div class="row">
      <ul class="nav nav-pills">
            <li><a class="<?php echo ($_GET['page'] == 'DemoBuddy_Products')? 'active':'';  ?>" href="<?php echo admin_url().'admin.php?page=DemoBuddy_Products' ?>"><?php _e('Products', 'Demobuddy'); ?></a></li>
            <li><a class="<?php echo ($_GET['page'] == 'DemoBuddy_Settings')? 'active':'';  ?>" href="<?php echo admin_url().'admin.php?page=DemoBuddy_Settings' ?>"><?php _e('Settings', 'DemoBuddy'); ?></a></li>   
            <li><a target="_blank"  href="https://affordableplugins.com"><?php _e('Free Update to Pro', 'DemoBuddy'); ?></a></li>  
      </ul>
      </div>
<br />



