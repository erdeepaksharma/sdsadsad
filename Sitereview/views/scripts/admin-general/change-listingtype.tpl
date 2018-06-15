<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: change-listingtype.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript">
  function check_submit()
  { 
    if(document.getElementById('listingtype_id').value == 0 ) 
    {
      return false;
    }
    else 
    {
      return true;
    }
  }
</script>

<div class="sitereview_admin_popup">
  <div>
    <?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>
  </div>
</div>
