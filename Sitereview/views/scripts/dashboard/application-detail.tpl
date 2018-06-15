<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: application-detail.tpl 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php  
$paramsString = Engine_Api::_()->sitereview()->getWidgetparams();
$params = !empty($paramsString) ? Zend_Json::decode($paramsString) : array();?>
<div class="global_form_popup sr_listing_details_view" id="application_detail">
	<h3><?php echo $this->translate('Application Details'); ?></h3><br />
	 <table class="clr">
    <?php if(in_array(1, $params['show_option'])):?>
      <tr>
        <td width="200"><b><?php echo $this->translate('Sender Name :'); ?></b></td>
        <td><?php echo $this->translate($this->applicationDetail->sender_name); ?>&nbsp;&nbsp;</td>
      </tr>
    <?php endif;?>
    <?php if(in_array(2, $params['show_option'])):?>
      <tr>
        <td><b><?php echo $this->translate('Sender Email :'); ?></b></td>
        <td><?php echo  $this->translate($this->applicationDetail->sender_email);?></td>
      </tr>
    <?php endif;?>
    <?php if(in_array(3, $params['show_option'])):?>
      <tr >
        <td><b><?php echo $this->translate('Contact :'); ?></b></td>
        <td><?php if(!empty($this->applicationDetail->contact)):?><?php echo  $this->translate($this->applicationDetail->contact);?><?php else:?>-<?php endif;?></td>
      </tr>
    <?php endif;?>
    <?php if(in_array(5, $params['show_option'])):?>
      <tr>
        <td><b><?php echo $this->translate('Message :'); ?></b></td>
        <td><?php echo  $this->translate($this->applicationDetail->body);?></td>
      </tr>
    <?php endif;?>
    <tr>
      <td><b><?php echo $this->translate('Applying Date :'); ?></b></td>
      <td>
        <?php echo $this->translate(gmdate('M d,Y, g:i A',strtotime($this->applicationDetail->creation_date))); ?>
      </td>
    </tr>
		</table>
	 <br />
	 <button  onclick='javascript:parent.Smoothbox.close()' ><?php echo $this->translate('Close')  ?></button>
</div>

<style type="text/css"> 
  #application_detail table tr td {
    vertical-align:top;
  }
</style>


<?php if (@$this->closeSmoothbox): ?>
	<script type="text/javascript">
		TB_close();
	</script>
<?php endif; ?>