<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: show-country.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<div class="global_form_popup">  
  <form>
    <?php foreach($this->values as $key => $value):?>
      <?php echo $value." => ". $key;?><br />
    <?php endforeach;?><br />
    <button onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate('Close'); ?></button>
  </form>
</div>

 
 
 
