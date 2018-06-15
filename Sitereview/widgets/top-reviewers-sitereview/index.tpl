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
  <?php foreach( $this->reviewers as $user ):?>
    <?php if($this->type == 'editor'): ?>
      <?php $href = array('route' => 'sitereview_review_editor_profile', 'username' => $user->getTitle(), 'user_id' => $user->user_id); ?>
    <?php else: ?>
      <?php $href = $user->getHref(); ?>
    <?php endif; ?>
    <li>
      <?php echo $this->htmlLink($href, $this->itemPhoto($user, 'thumb.icon'), array('class' => 'popularmembers_thumb', 'title' => $user->getTitle()), array('title' => $user->getTitle())) ?>      

      <div class='seaocore_sidebar_list_info'>
        <div class='seaocore_sidebar_list_title'>
          <?php echo $this->htmlLink($href, $user->getTitle(), array('title' =>  $user->getTitle())) ?>
        </div>
        <div class='seaocore_sidebar_list_details'>
          <?php echo $this->translate(array('%s review', '%s reviews', $user->review_count),$this->locale()->toNumber($user->review_count)) ?>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul>