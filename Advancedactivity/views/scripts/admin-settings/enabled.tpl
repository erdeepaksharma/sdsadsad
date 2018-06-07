<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: enabled.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<?php
    if (empty($this->enabled)) {
      $title = $this->translate("Disable this Custom Block?");
      $discription = $this->translate("Are you sure that you want to disable this block from Custom Blocks? After being disabled this will not be shown in the Welcome Tab.");
      $bouttonLink = $this->translate("Disable");
    } else {
      $title = $this->translate("Enable this Custom Block?");
      $discription = $this->translate("Are you sure that you want to enable this block in Custom Blocks? After being enabled this will be shown in the Welcome Tab.");
      $bouttonLink = $this->translate("Enable");
    }
?>



<form method="post" class="global_form_popup" action="">
  <div>
    <h3><?php echo $title; ?></h3>
    <p>
      <?php echo $discription; ?>
    </p>
    <br />
    <p>
      <button type='submit'><?php echo $bouttonLink; ?></button>
      <?php echo $this->translate("or") ?> <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'>
      <?php echo $this->translate("cancel") ?></a>
    </p>
  </div>
</form>