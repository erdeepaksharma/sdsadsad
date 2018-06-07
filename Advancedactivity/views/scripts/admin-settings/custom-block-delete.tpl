<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: custom-block-delete.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo $this->translate("Delete this Custom Block?") ?></h3>
    <p>
      <?php echo $this->translate("Are you sure that you want to delete this Custom Block? It will not be recoverable after being deleted.") ?>
    </p>
    <br />
    <p>      
      <button type='submit'><?php echo $this->translate("Delete") ?></button>
      <?php echo $this->translate("or") ?> <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
      <?php echo $this->translate("cancel") ?></a>
    </p>
  </div>
</form>