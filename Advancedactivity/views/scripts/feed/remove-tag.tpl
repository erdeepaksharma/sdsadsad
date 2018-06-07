<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: remove-tag.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<form method="post" class="global_form_popup">
  <div>
    <h3><?php echo $this->translate('Remove Tag?'); ?></h3>
    <p>
      <?php echo $this->translate('Are you sure that you want to remove this tag?'); ?>
    </p>
    <br />
    <p>     
      <button type='submit'><?php echo $this->translate('Continue'); ?></button>
      or <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate('cancel'); ?></a>
    </p>
  </div>
</form>
<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>