<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: claim-listing.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereviewprofile.css');
?>
<?php if (!$this->status && $this->userclaim && $this->claimoption): ?>
  <div class="sitereview_tellafriend_popup">
    <?php echo $this->form->render($this); ?>
  </div>
<?php endif; ?>