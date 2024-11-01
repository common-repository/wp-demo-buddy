<?php if ( ! defined( 'ABSPATH' ) ) exit;  

require_once(DemoBuddy6_PLUGIN_DIR. '/admin/model/admin-model.php'); 
$adminmodel=new DemoBuddy_admin_model();



if($_GET['ddaction'] == 'statswitch')
    $adminmodel->switch_state($_GET);
    
if($_GET['ddaction'] == 'delproduct')
    $adminmodel->delproduct($_GET);
    
if($_POST['form_type'] == 'newdemoproduct')
    $adminmodel->save_dashboard($_POST);

if($_POST['form_type'] == 'adcode')
    $adminmodel->save_adcode($_POST);
    
$data = $adminmodel->get_dashboard();
 ?>
  <div class="wrap" style="background: #fff;">
    <div class="container-fluid col-md-12" style="background: #fff;">
      <?php include( 'admin_menu.php') ?>
      <div style="clear: both;"></div>
       <?php   $sett = get_option('demobuddy_gensettings');
            if(!$sett)
            {
               echo '<div class="alert alert-danger">'.__('Settings are Not Completed yet.  Before Creating a Product, please conplete the Settings.','DemoBuddy').'</div>' ;
            } 
            else
            { ?>
              <p><button data-action="New_Demo_Product"  data-form="Edit Product" class="btn btn-primary demoModal" data-keyboard="false" data-backdrop="static" id="newproduct"><?php _e('New Demo Product', 'DemoBuddy'); ?></button></p><br />   
           <?php }?>
     
        
        <table class="table table-condensed table-striped">
        <tr>
                
            <th><?php _e('Name', 'DemoBuddy'); ?></th>
            <th class="text-center"><?php _e('Status', 'DemoBuddy'); ?></th>
            <th><?php _e('Shortcode', 'DemoBuddy'); ?></th>
            
            <th class="text-center">Action</th>        
        </tr>
        <?php 
        foreach($data as $dat) 
        { 
            $on = '<i data-toggle="tooltip" class="glyphicon glyphicon-ok active" aria-hidden="true" title="'.__('Click to Change Status', 'DemoBuddy').'">'.__('Active', 'DemoBuddy').'</i> ';
            $off = '<i data-toggle="tooltip" class="glyphicon glyphicon-remove inactive" aria-hidden="true" title="'.__('Click to Change Status', 'DemoBuddy').'">'. __('InActive', 'DemoBuddy').'</i>';
                          
            ?> 
        <tr>
            <td><?php echo $dat->name; ?></td>
            <td class="text-center">
            <a href="<?php  echo admin_url().'admin.php?page=DemoBuddy_Products&ddaction=statswitch&fp='.$dat->state.'&id='.$dat->id; ?>"  class="freepaidSwitch" ><?php echo ($dat->state == '1')? $on : $off; ?></a>
            </td>
            <td><?php echo '[DEMOBUDDY id="'.$dat->id.'"]'; ?></td>            
            <td class="text-center">
            
            <span class="iconbrdr demoModal" data-keyboard="false" data-backdrop="static" data-action="New_Demo_Product" data-form="Edit Product" data-id="<?php echo $dat->id; ?>" id="edit_product"><i data-toggle="tooltip" title="<?php _e('Edit', 'DemoBuddy'); ?>" class="glyphicon glyphicon-edit" ></i></span> 
            
            
            
            <a href="<?php  echo admin_url().'admin.php?page=DemoBuddy_Products&ddaction=delproduct&id='.$dat->id; ?>" class="iconbrdr" id="del_product" data-toggle="confirmation" 
                                        data-placement="left"  
                                        data-btn-ok-label="Yes" 
                                        data-btn-ok-icon="fa fa-check"
                                        data-btn-ok-class="btn-danger"
                                        data-btn-cancel-label="No" 
                                        data-btn-cancel-icon="fa fa-times"
                                        data-btn-cancel-class="btn-default"
                                        data-title="Are you sure?" 
                                        data-content="This Product <?php echo $dat->name; ?> will be deleted.">
                                        <i data-toggle="tooltip" title="<?php _e('Delete', 'DemoBuddy'); ?>" class="glyphicon glyphicon-trash"
                                        
                                         ></i></a> 
            
            <?php echo $dat->action; ?></td>        
        </tr>
        <?php } ?>
        </table>
    </div>
    <div style="clear: both;">
    </div>
  </div>
  
  
<!-- Modal -->
<div id="demoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" method="POST"  enctype="multipart/form-data">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body col-md-12"></div>
      <div class="modal-footer">
      <span class="saveproductcont"></span>
        <input type="submit" id="saveproduct" class="btn btn-primary" name="submit" value="<?php _e('Save', 'DemoBuddy'); ?>" />
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'DemoBuddy'); ?></button>
      </div>
    </div>
    <?php wp_nonce_field( 'demobuddy_product_nonce_action', 'demobuddy_product_nonce_field' ); ?>
    </form>
  </div>
</div>