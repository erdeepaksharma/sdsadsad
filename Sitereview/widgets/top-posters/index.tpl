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

<ul class="seaocore_sidebar_list">
  <?php foreach( $this->posters as $user ):?>

    <li>
      <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon'), array('class' => 'popularmembers_thumb', 'title' => $user->getTitle()), array('title' => $user->getTitle())) ?>      

      <div class='seaocore_sidebar_list_info'>
        <div class='seaocore_sidebar_list_title'>
          <?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('title' =>  $user->getTitle())) ?>
        </div>
        <div class='seaocore_sidebar_list_details'>
          <?php if($this->listingtype_id > 0): ?>
            <?php echo $this->translate(array('%s '.strtolower($this->listingtypeArray->title_singular).' entry', '%s '.strtolower($this->listingtypeArray->title_plural.' entries'), $user->listing_count),$this->locale()->toNumber($user->listing_count)) ?>
          <?php else: ?>
            <?php echo $this->translate(array('%s entry', '%s entries', $user->listing_count),$this->locale()->toNumber($user->listing_count)) ?>          
          <?php endif; ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>