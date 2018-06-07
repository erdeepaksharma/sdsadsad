<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: action.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php
	$action =  $this->subject;
  if (!empty($action)) {

    // if (empty($action)) return;
    try { // prevents a bad feed item from destroying the entire business
      // Moved to controller, but the items are kept in memory, so it shouldn't hurt to double-check
//       if( !$action->getTypeInfo()->enabled ) continue;
//       if( !$action->getSubject() || !$action->getSubject()->getIdentity() ) continue;
//       if( !$action->getObject() || !$action->getObject()->getIdentity() ) continue;
      ob_start();
    ?>
    <?php if( !$this->noList ): ?>
    	<ul class="feed" style="border-bottom-width:1px;">
    		<li id="activity-item-<?php echo $action->action_id ?>">
    <?php endif; ?>
    <?php // User's profile photo ?>
    <div class='feed_item_photo'>
      <?php echo $this->htmlLink($action->getSubject()->getHref(),
        $this->itemPhoto($action->getSubject(), 'thumb.icon', $action->getSubject()->getTitle()), array('target' => '_blank')) ?>
    </div>
    <div class='feed_item_body'>
      <?php // Main Content ?>
      <span class="<?php echo ( empty($action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated' ) ?>">
        <?php echo str_replace('<a ','<a target="_blank" ',$action->getContent()); ?>
      </span>

        <?php // Attachments ?>
        <?php if( $action->getTypeInfo()->attachable && $action->attachment_count > 0 ): // Attachments ?>
        <div class='feed_item_attachments'>
          <?php if( $action->attachment_count > 0 && count($action->getAttachments()) > 0 ): ?>
            <?php if( count($action->getAttachments()) == 1 &&
                    null != ( $richContent = current($action->getAttachments())->item->getRichContent()) ): ?>
              <?php echo $richContent; ?>
            <?php else: ?>
              <?php foreach( $action->getAttachments() as $attachment ): ?>
                <span class='feed_attachment_<?php echo $attachment->meta->type ?>'>
                <?php if( $attachment->meta->mode == 0 ): // Silence ?>
                <?php elseif( $attachment->meta->mode == 1 ): // Thumb/text/title type actions ?>
                  <div>
                    <?php if( $attachment->item->getPhotoUrl() ): ?>
                      <?php
                        if ($attachment->item->getType() == "core_link") {
                          $attribs = Array('target'=>'_blank');
                        } else {
                          $attribs = Array();
                        }
                      ?>
                      <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, 'thumb.normal', $attachment->item->getTitle(),array('target' => '_blank')),
                      $attribs) ?>
                    <?php endif; ?>
                    <div>
                      <div class='feed_item_link_title'>
                        <?php
                          if ($attachment->item->getType() == "core_link") {
                            $attribs = Array('target'=>'_blank');
                          } else {
                            $attribs = Array();
                          }
                          echo $this->htmlLink($attachment->item->getHref(), $attachment->item->getTitle() ? $attachment->item->getTitle() : '', $attribs );
                        ?>
                      </div>
                      <div class='feed_item_link_desc'>
                        <?php echo $this->viewMore($attachment->item->getDescription()) ?>
                      </div>
                    </div>
                  </div>
                <?php elseif( $attachment->meta->mode == 2 ): // Thumb only type actions ?>
                <div class="feed_attachment_photo">
                  <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, 'thumb.normal', $attachment->item->getTitle()), array('class' =>
                    'feed_item_thumb','target'=>'_blank'))    ?>
                </div>
                <?php //elseif( $attachment->meta->mode == 3 ): // Description only type actions ?>
                  <?php //echo $this->viewMore($attachment->item->getDescription()); ?>
                <?php //elseif( $attachment->meta->mode == 4 ): // Multi collectible thingy (@todo) ?>
                <?php endif; ?>
                </span>
              <?php endforeach; ?>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    	</div>  
    <?php if( !$this->noList ): ?>
    		</li>
    	</ul>
    <?php endif; ?>  
    <?php } catch (Exception $e) {
        ob_end_clean();
        if( APPLICATION_ENV === 'development' ) {
          echo $e->__toString();
        }
      } ?>
<?php } else { ?>
	<div class="tip">
	 <span><?php echo $this->translate("There are currently no activity feed.") ?></span>
	</div>
<?php } ?>

	<?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>

<?php if( @$this->closeSmoothbox ): ?>
  <script type="text/javascript">
    parent.Smoothbox.close();
  </script>
<?php endif; ?>
<style type="text/css">
#global_content_simple{padding:10px;}
p.form-description{margin-bottom:10px;}
.form-wrapper{margin-bottom:5px;}
</style>