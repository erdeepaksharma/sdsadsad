<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: view.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');?>

<?php 
  include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/Adintegration.tpl';
?>

<div class="sr_view_top">
	<?php echo $this->htmlLink($this->sitereview->getHref(array('profile_link' => 1)), $this->itemPhoto($this->sitereview, 'thumb.icon', '', array('align' => 'left'))) ?>
	<h2>
	  <?php echo $this->sitereview->__toString() ?>
	  <?php echo $this->translate('&raquo; ');?>
	  <?php echo $this->htmlLink($this->sitereview->getHref(array('tab'=> $this->tab_selected_id)), $this->translate('Discussions')) ?>
	  <?php echo $this->translate('&raquo; ');?>
	  <?php echo $this->topic->getTitle() ?>
	</h2>	
</div>

<!--RIGHT AD START HERE-->
<?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.communityads', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.addiscussionview', 3) && $review_communityad_integration):?>
	<div class="layout_right" id="communityad_topicview">
		<?php echo $this->content()->renderWidget("communityad.ads", array( "itemCount"=>Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.addiscussionview', 3),"loaded_by_ajax"=>1,'widgetId'=>'review_topicview'))?>
	</div>
<?php endif;?>
<!--RIGHT AD END HERE-->

<div class="sr_topic_view o_hidden">
	
	<?php $this->placeholder('sitereviewtopicnavi')->captureStart(); ?>
	
	<div class="sr_discussion_thread_options">
	  <?php echo $this->htmlLink(array('route' => "sitereview_extended_listtype_$this->listingtype_id", 'controller' => 'topic', 'action' => 'index', 'listing_id' => $this->sitereview->getIdentity(), 'tab'=> $this->tab_selected_id), $this->translate('Back to Topics'), array(
	    'class' => 'buttonlink icon_back'
	  )) ?>
	
	  <?php if( $this->canPost ): ?>
	    <?php echo $this->htmlLink($this->url(array()) . '#reply', $this->translate('Post Reply'), array(
	      'class' => 'buttonlink icon_sitereview_post_reply'
	    )) ?>
	  <?php endif; ?>
	
	  <?php if( $this->viewer->getIdentity() ): ?>
	    <?php if( !$this->isWatching ): ?>
	      <?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '1')), $this->translate('Watch Topic'), array(
	        'class' => 'buttonlink icon_sitereview_topic_watch'
	      )) ?>
	    <?php else: ?>
	      <?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '0')), $this->translate('Stop Watching Topic'), array(
	        'class' => 'buttonlink icon_sitereview_topic_unwatch'
	      )) ?>
	    <?php endif; ?>
	  <?php endif; ?>
	
	  <?php if( $this->sitereview->isOwner($this->viewer()) ): ?>
	    <?php if( !$this->topic->sticky ): ?>
	      <?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '1', 'reset' => false), $this->translate('Make Sticky'), array(
	        'class' => 'buttonlink icon_sitereview_post_stick'
	      )) ?>
	    <?php else: ?>
	      <?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '0', 'reset' => false), $this->translate('Remove Sticky'), array(
	        'class' => 'buttonlink icon_sitereview_post_unstick'
	      )) ?>
	    <?php endif; ?>
	
	    <?php if( !$this->topic->closed ): ?>
	      <?php echo $this->htmlLink(array('action' => 'close', 'close' => '1', 'reset' => false), $this->translate('Close'), array(
	        'class' => 'buttonlink icon_sitereview_post_close'
	      )) ?>
	    <?php else: ?>
	      <?php echo $this->htmlLink(array('action' => 'close', 'close' => '0', 'reset' => false), $this->translate('Open'), array(
	        'class' => 'buttonlink icon_sitereview_post_open'
	      )) ?>
	    <?php endif; ?>
	
	    <?php echo $this->htmlLink(array('action' => 'rename', 'reset' => false), $this->translate('Rename'), array(
	      'class' => 'buttonlink smoothbox seaocore_icon_edit'
	    )) ?>
	    <?php echo $this->htmlLink(array('action' => 'delete', 'reset' => false), $this->translate('Delete'), array(
	      'class' => 'buttonlink smoothbox seaocore_icon_delete'
	    )) ?>
	  <?php elseif( $this->sitereview->isOwner($this->viewer()) == false): ?>
	    <?php if( $this->topic->closed ): ?>
	      <div class="sr_discussion_thread_options_closed seaocore_txt_light">
	        <?php echo $this->translate('This topic has been closed.');?>
	      </div>
	    <?php endif; ?>
	  <?php endif; ?>
	</div>
	<?php $this->placeholder('sitereviewtopicnavi')->captureEnd(); ?>
	
	<?php echo $this->placeholder('sitereviewtopicnavi') ?>
	<?php echo $this->paginationControl(null, null, null, array(
	  'params' => array(
	    'post_id' => null
	  )
	)) ?>
	
	<script type="text/javascript">
	  var quotePost = function(user, href, body) {
	    if( $type(body) == 'element' ) {
	      body = $(body).getParent('li').getElement('.sr_discussion_thread_body_raw').get('html').trim();
	    }
	    var tinyMCEEditor = '<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.tinymceditor', 1);?>';
	    if(tinyMCEEditor == 1) {
				tinyMCE.activeEditor.setContent('[blockquote]' + '[b][url=' + href + ']' + user + '[/url] said:[/b]\n' + htmlspecialchars_decode(body) + '[/blockquote]\n\n'); 
	    } else {
	       $('body').value = '[blockquote]' + '[b][url=' + href + ']' + user + '[/url] said:[/b]\n' + htmlspecialchars_decode(body) + '[/blockquote]\n\n';
	    }
	    $("body").focus();
	    $("body").scrollTo(0, $("body").getScrollSize().y);
	  }
	</script>
	
	<ul class='sr_discussion_thread'>
	  <?php foreach( $this->paginator as $post ): ?>
			<li class="b_medium <?php echo $this->cycle(array("odd", "even")) ->next()?>">
				<div class="sr_discussion_thread_photo">
					<?php
						$user = $this->item('user', $post->user_id);
						echo $this->htmlLink($user->getHref(), $user->getTitle());
						echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon'));
					?>
				</div>
				<div class="sr_discussion_thread_info">
					<div class="sr_discussion_thread_details b_medium">
						<div class="sr_discussion_thread_details_options">
							<?php if( $this->form ): ?>
								<?php echo $this->htmlLink('javascript:void(0);', $this->translate('Quote'), array(
									'class' => 'buttonlink icon_sitereview_post_quote',
									'onclick' => 'quotePost("'.$this->escape($user->getTitle()).'", "'.$this->escape($user->getHref()).'", this);',
								)) ?>
							<?php endif; ?>
							<?php if( $post->user_id == $this->viewer()->getIdentity() || $this->sitereview->getOwner()->getIdentity() == $this->viewer()->getIdentity() ): ?>
								<?php echo $this->htmlLink(array('route' => "sitereview_extended_listtype_$this->listingtype_id", 'controller' => 'post', 'action' => 'edit', 'post_id' => $post->getIdentity(), 'format' => 'smoothbox'), $this->translate('Edit'), array(
									'class' => 'buttonlink smoothbox seaocore_icon_edit'
								)) ?>
								<?php echo $this->htmlLink(array('route' => "sitereview_extended_listtype_$this->listingtype_id", 'controller' => 'post', 'action' => 'delete', 'post_id' => $post->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete'), array(
									'class' => 'buttonlink smoothbox seaocore_icon_delete'
								)) ?>
							<?php endif; ?>
						</div>
						<div class="sr_discussion_thread_details_date seaocore_txt_light">
							<?php echo $this->translate('Posted');?> <?php echo $this->timestamp(strtotime($post->creation_date)) ?>
						</div>
					</div>
					<div class="sr_discussion_thread_body">
						<?php echo nl2br($this->BBCode($post->body)) ?>
					</div>
					<span class="sr_discussion_thread_body_raw" style="display: none;">
						<?php echo $post->body; ?>
					</span>
				</div>
			</li>
	  <?php endforeach; ?>
	</ul>
	
	<?php if($this->paginator->getCurrentItemCount() > 4): ?>
	  <?php echo $this->paginationControl(null, null, null, array(
	    'params' => array(
	      'post_id' => null
	    )
	  )) ?>
	  <br />
	  <?php echo $this->placeholder('sitereviewtopicnavi') ?>
	<?php endif; ?>
	
	<br />
	
	<?php if($this->form): ?>
	  <a name="reply" />
			<?php echo $this->form->setAttrib('id', 'sitereview_topic_reply')->render($this) ?>
		</a>
	<?php endif; ?>
</div>	