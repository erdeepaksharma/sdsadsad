<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: browse.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php $this->activeClass='sitereview_main_wishlist_browse';
include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/navigation_views.tpl'; ?>

<div class='layout_right'>
  <?php echo $this->form->render($this) ?>

</div>

<div class='layout_middle'>
	<?php if( count($this->paginator) > 0 ): ?>
		<ul class='seaocore_browse_list'>
			<?php foreach( $this->paginator as $wishlist ): ?>
				<li>
          <div class='seaocore_browse_list_photo'>
            <?php echo $this->htmlLink($wishlist->getOwner()->getHref(), $this->itemPhoto($wishlist->getOwner(), 'thumb.normal')) ?>
          </div>
          
					<div class="seaocore_browse_list_info">
						<div class="seaocore_browse_list_info_title">
							<h3><?php echo $this->htmlLink($wishlist->getHref(), $wishlist->title) ?></h3>
						</div>
            <div class='seaocore_browse_list_info_blurb'>
              <?php echo $wishlist->body; ?>
						</div>
						<div class="seaocore_browse_list_info_date">
							<?php echo $this->translate('Created %s by %s', $this->timestamp($wishlist->creation_date), $wishlist->getOwner()->toString()) ?>
							<br />
							<?php echo $this->translate('Total Entries: %s', $wishlist->total_item) ?>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<div>
			<?php echo $this->paginationControl($this->paginator, null, null, array('query' => $this->formValues,'pageAsQuery' => true));?>
		</div>
	<?php else: ?>
		<div class="tip">
      <span>
        <?php echo $this->translate("Nobody has created a wishlist yet. Be the first to %s one!", '<a class="smoothbox" href="'.$this->url(array('action' => 'create'), "sitereview_wishlist_general").'">create</a>');?>
      </span>
    </div>
	<?php endif; ?>
</div>