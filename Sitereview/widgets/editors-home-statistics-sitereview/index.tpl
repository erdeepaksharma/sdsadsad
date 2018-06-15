<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<ul class="seaocore_sidebar_list sr_edotors_statistics">
  <li>
    <?php echo $this->translate(array('<span>%s</span> <div>Editor Review</div>', '<span>%s</span> <div>Editor Reviews</div>', $this->totalEditorReviews), $this->locale()->toNumber($this->totalEditorReviews));?>
  </li>
  
  <li>
    <?php echo $this->translate(array('<span>%s</span> <div>Editor</div>', '<span>%s</span> <div>Editors</div>', $this->totalEditors), $this->locale()->toNumber($this->totalEditors));?>
  </li>
  
  <?php if(Count($this->editorsPerListingType) > 1): ?>
    <?php foreach($this->editorsPerListingType as $listingTypeEditor): ?>
      <li>
        <?php echo '<span>'.$listingTypeEditor->total_editors.'</span>' ?> <div><?php echo $this->translate("Editors In $listingTypeEditor->title_plural");?>
      </li>
    <?php endforeach; ?>
  <?php endif; ?>
</ul>