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

<?php if ($this->paginator->getTotalItemCount() > 0): ?>
  <?php if ($this->canPost): ?>
    <div class="profile-content-top-button" data-role="controlgroup" data-type="horizontal">
        <?php echo $this->htmlLink(array(
          'route' => "sitereview_extended_listtype_$this->listingtype_id",
          'controller' => 'topic',
          'action' => 'create',
          'subject' => $this->subject()->getGuid(),
          'content_id' => $this->identity
        ), $this->translate('Post New Topic'), array(
					'class' => 'buttonlink icon_sitereview_post_new','data-role' => "button", 'data-icon' => "plus", "data-iconpos" => "left", "data-inset" => 'false', 'data-mini' => "true", 'data-corners' => "true", 'data-shadow' => "true")) ?>
    </div>
  <?php endif; ?>

  <div class="sm-content-list" id="profile_sitereviewdiscussions">
		<ul data-role="listview" data-inset="false" data-icon="false">
		  <?php foreach ($this->paginator as $topic):
          $lastpost = $topic->getLastPost();
          $lastposter = Engine_Api::_()->getItem('user', $topic->lastposter_id);
      ?>
			<li data-icon="arrow-r">
				<a href="<?php echo $topic->getHref(array( 'content_id' => $this->identity)); ?>">
					<h3><?php echo $topic->getTitle() ?></h3>
					 <p class ="ui-li-aside"><strong><?php echo $this->translate(array('%1$s reply', '%1$s replies', $topic->post_count-1), $this->locale()->toNumber($topic->post_count-1)) ?></strong></p>
					<p><?php echo $this->translate('Last Post') ?> <?php echo $this->translate('by'); ?> <strong><?php echo $lastposter->getTitle() ?></strong></p>
				</a>
			</li>
      <?php endforeach;?>
		</ul>

		<?php if ($this->paginator->count() > 1): ?>
			<?php
			echo $this->paginationAjaxControl(
							$this->paginator, $this->identity, "profile_sitereviewdiscussions");
			?>
		<?php endif; ?>
	</div>
<?php else:?>
	<div class="tip">
		<span>
			<?php echo $this->translate('No discussion topics have been posted in this '.$this->listing_singular_lc.' yet.'); ?>
			<?php
			if ($this->canPost):
				$show_link = $this->htmlLink(array('route' => "sitereview_extended_listtype_$this->listingtype_id", 'controller' => 'topic', 'action' => 'create','subject' => $this->subject()->getGuid(), 'content_id' => $this->identity),$this->translate('here'));
				$show_label = Zend_Registry::get('Zend_Translate')->_('Click %1$s to start a discussion.');
				$show_label = sprintf($show_label, $show_link);
				echo $show_label;
			endif;
			?>
		</span>
	</div>
<?php endif;?>