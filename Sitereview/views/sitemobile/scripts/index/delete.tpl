<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: delete.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<div class='global_form'>
  <form method="post" class="global_form" action="<?php echo $this->url(array('module'=>'sitereview','controller' => 'index','action'=>'delete','listing_id' => $this->listing_id), "sitereview_specific_listtype_$this->listingtype_id", true) ?>">
    <div>
      <div>
        <h3><?php echo $this->translate("Delete $this->listing_singular_uc?"); ?></h3>
        <p>
          <?php echo $this->translate('Are you sure that you want to delete the '.$this->listing_singular_uc.' with the title "%1$s" last modified %2$s? It will not be recoverable after being deleted.', $this->sitereview->title, $this->timestamp($this->sitereview->modified_date)); ?>
        </p>
        <br />
        <p>
          <input type="hidden" name="confirm" value="true"/>
          <button type='submit' data-theme="b"><?php echo $this->translate('Delete'); ?></button>
          <div style="text-align: center"><?php echo $this->translate('or'); ?> </div>
          <a href="#" data-rel="back" data-role="button">
            <?php echo $this->translate('Cancel') ?>
          </a>
        </p>
      </div>
    </div>
  </form>
</div>