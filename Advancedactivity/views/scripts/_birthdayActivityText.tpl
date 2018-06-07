<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _birthdayActivityText.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>
<?php $sharesTable=Engine_Api::_()->getDbtable('shares', 'advancedactivity');?>
<?php $item = $this->poster[0]; ?>
<li>
	<div class='feed_item_photo'>
  	<?php
	    echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.icon',$item->getTitle()),
	      array('class'=>'notranslate sea_add_tooltip_link', 'rel'=>$item->getType().' '.$item->getIdentity())
	    )
    ?>
  </div> 
  <div class='feed_item_body'>
    <span class="feed_item_generated">
	    <?php if($this->countPoster ==1):
	    	echo $this->translate('%1$s  wrote on %2$s\'s Wall for birthday.',$this->htmlLink($item->getHref(), $item->getTitle(),array('class'=>'sea_add_tooltip_link notranslate feed_item_username', 'rel'=>$item->getType().' '.$item->getIdentity())),$this->htmlLink($this->mainAction->getObject()->getHref(),$this->mainAction->getObject()->getTitle(),array('class'=>'sea_add_tooltip_link', 'rel'=>$this->mainAction->getObject()->getType().' '.$this->mainAction->getObject()->getIdentity())));
	    endif; ?>
      <?php if($this->countPoster ==2):
      	echo $this->translate('%1$s  and %2$s also wrote on %3$s\'s Wall for birthday.',$this->htmlLink($item->getHref(), $item->getTitle(),array('class'=>'sea_add_tooltip_link notranslate feed_item_username', 'rel'=>$item->getType().' '.$item->getIdentity())),$this->htmlLink($this->poster[1]->getHref(),$this->poster[1]->getTitle(),array('class'=>'sea_add_tooltip_link', 'rel'=>$this->poster[1]->getType().' '.$this->poster[1]->getIdentity())),$this->htmlLink($this->mainAction->getObject()->getHref(),$this->mainAction->getObject()->getTitle(),array('class'=>'sea_add_tooltip_link', 'rel'=>$this->mainAction->getObject()->getType().' '.$this->mainAction->getObject()->getIdentity())));
      endif; ?>
    	<?php if($this->countPoster > 2):
		    $URL = $this->url(array('module' => 'advancedactivity', 'controller' => 'feed', 'action' =>
		    'get-other-post', 'id'=>  $this->mainAction->getObject()->getIdentity()), 'default', true);
    
      	$otherFriends='<span class="aaf_feed_show_tooltip_wrapper"><a href='.$URL.'class="smoothbox">'.$this->translate('%s other friends',($this->countPoster-1)).'</a>
											<span class="aaf_feed_show_tooltip" style="margin-left:-8px;">
												<img src="'. $this->layout()->staticBaseUrl  . 'application/modules/Advancedactivity/externals/images/tooltip_arrow.png" />';
												for($i=1;$i<count($this->poster);$i++):
                        $otherFriends.= $this->poster[$i]->getTitle()."<br />";
                        endfor;
							$otherFriends.='</span>
            				</span>';
     echo $this->translate('%1$s  and %2$s also wrote on %3$s\'s Wall for birthday.',
             $this->htmlLink($item->getHref(), $item->getTitle(),array('class'=>'sea_add_tooltip_link notranslate feed_item_username', 'rel'=>$item->getType().' '.$item->getIdentity())),
             $otherFriends,
             $this->htmlLink($this->mainAction->getObject()->getHref(),$this->mainAction->getObject()->getTitle()),array('class'=>'sea_add_tooltip_link', 'rel'=>$this->mainAction->getObject()->getType().' '.$this->mainAction->getObject()->getIdentity()));

      endif; ?>
      </span>
    <?php $object=$this->mainAction->getObject();
    $remove_patern=' &rarr; '.$object->toString(array('class' => 'feed_item_username notranslate sea_add_tooltip_link','rel'=>$object->getType().' '.$object->getIdentity()))?>
      <div class="feed_item_attachments">
      	<span class="feed_attachment_birthday_link">
      		<div>
        		<?php echo $this->htmlLink($this->mainAction->getObject()->getHref(), $this->itemPhoto($this->mainAction->getObject(), 'thumb.profile',$this->mainAction->getObject()->getTitle()) )  ?>
        		<div>
         			<div class="feed_item_link_title"> 
         				<?php echo $this->htmlLink($this->mainAction->getObject()->getHref(), $this->mainAction->getObject()->getTitle(),array('class'=>'sea_add_tooltip_link', 'rel'=>$this->mainAction->getObject()->getType().' '.$this->mainAction->getObject()->getIdentity())); ?>
        			</div>    
        			<div class="feed_item_link_desc">  
   							<?php  echo $this->translate("Birthday:") ?>&nbsp;<?php  echo $this->birthdate; ?>
        			</div>
				    	<?php if($this->isAbletoWish): ?>
		          	<div class="feed_item_link_desc">
		            	<a href="javascript:void(0)" onclick="$('new_post_bd_<?php echo $this->mainAction->action_id?>').style.display=''; $('activity-bd-write-body-<?php echo $this->mainAction->action_id ?>').focus();"> <?php echo $this->translate("Write on %s's Wall",$this->mainAction->getObject()->getTitle() ) ?>  </a>
		          	</div>
				    	<?php endif; ?>
    				</div>
    			</div>
        </span>
      </div>
   
    <ul id="new_post_bd_<?php echo $this->mainAction->action_id?>" class="birthday_activity_feed" style="display:none;">
     <li class="clr">
      <div class='feed_item_photo'>
      	<?php echo $this->htmlLink($this->viewer()->getHref(), $this->itemPhoto($this->viewer(), 'thumb.icon', $this->viewer()->getTitle())) ?>
      </div>
      <div class="feed_item_body">
        <span id="add_post">
         <input class="aaf_birthday_wish_input" type="text" id="activity-bd-write-body-<?php echo $this->mainAction->action_id ?>" alt='<?php echo $this->string()->escapeJavascript($this->translate("Write on %s's Wall...", $this->mainAction->getObject()->getTitle())); ?>' onkeyup="postWishFeed(event, '<?php echo $this->mainAction->object_id ?>', '<?php echo $this->mainAction->action_id ?>')" />
        </span>
        <span></span>
      </div>
    </li>
  </ul>
 <?php $advancedactivityCoreApi= Engine_Api::_()->advancedactivity();?>
  <ul id="birthdate_feeds_<?php echo $this->mainAction->action_id ?>" class="birthday_activity_feed">
    <?php $count=0;?>
    <?php foreach ($this->birthdayActions as $action): ?>
     <?php $item=$action->getSubject();  ?>
    <li class="clr View_More_Birthday_Feed_<?php echo $this->mainAction->action_id ?> <?php echo 'Hide_'.$item->getType()."_".$item->getIdentity() ?>" id="activity-item-<?php echo $action->action_id ?>" style="display:<?php echo $count >1?'none':''?>;">
        <?php $count++;?>
        <?php $this->commentForm->setActionIdentity($action->action_id) ?>
    <script type="text/javascript">
      (function(){
        var action_id = '<?php echo $action->action_id ?>';
        en4.core.runonce.add(function(){
          $('activity-comment-body-' + action_id).autogrow();
          en4.advancedactivity.attachComment($('activity-comment-form-' + action_id));
        });
      })();
    </script>
      <div class='feed_item_photo'>    
      	<?php echo $this->htmlLink($action->getSubject()->getHref(), $this->itemPhoto($action->getSubject(), 'thumb.icon', $action->getSubject()->getTitle()),  array('class'=>'sea_add_tooltip_link', 'rel'=>$item->getType().' '.$item->getIdentity())
         ) ?>
      </div> 
      <?php if( $this->viewer()->getIdentity() && (
            $this->activity_moderate || (
              $this->allow_delete && (
                ('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                ('user' == $action->object_type && $this->viewer()->getIdentity()  == $action->object_id)
              )
            )
        ) ): ?>
        <div class="feed_item_option_delete aaf_birthday_feed_delete_btn">             
          <?php echo $this->htmlLink(array(
            'route' => 'default',
            'module' => 'advancedactivity',
            'controller' => 'index',
            'action' => 'delete',
            'action_id' => $action->action_id
         ), $this->translate('X'), array('class' => 'smoothbox','title'=>$this->translate('Delete Post'))) ?>
           
        </div>
      <?php endif; ?>  
    	<div class="feed_item_body">
	     	<?php // Main Content ?>
	       <span class="<?php echo ( empty($action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated' ) ?>">
	         <?php //echo $action->getContent() ?>
	        <?php echo str_replace($remove_patern,"",$this->getContent($action)); ?>
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
	                        if ($attachment->item->getType() == "core_link")
	                        {
	                          $attribs = Array('target'=>'_blank');
	                        }
	                        else
	                        {
	                          $attribs = Array();
	                        }
	                      ?>                                    
                          <?php if(SEA_ACTIVITYFEED_LIGHTBOX && strpos($attachment->meta->type, '_photo')):?>
                            <?php $attribs=@array_merge($attribs, array('onclick'=>'openSeaocoreLightBox("'.$attachment->item->getHref().'");return false;'));?>
                         <?php endif;?>
                         <?php if(strpos($attachment->meta->type, '_photo')): ?>
                           <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, 'thumb.medium', $attachment->item->getTitle(),array('class'=>'aaf-feed-photo-1')), $attribs) ?>
                          <?php else: ?>
                           <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, 'thumb.normal', $attachment->item->getTitle()), $attribs) ?>
                         <?php endif;?>
	                    <?php endif; ?>
	                    <div>
	                      <div class='feed_item_link_title'>
	                        <?php
	                          if ($attachment->item->getType() == "core_link")
	                          {
	                            $attribs = Array('target'=>'_blank');
	                          }
	                          else
	                          {
	                             $attribs = array('class'=>'sea_add_tooltip_link', 'rel'=>$attachment->item->getType().' '.$attachment->item->getIdentity());
	                          }
	                          echo $this->htmlLink($attachment->item->getHref(), $attachment->item->getTitle() ? $attachment->item->getTitle() : '', $attribs);
	                        ?>
	                      </div>
	                      <div class='feed_item_link_desc'>
	                       <?php echo $this->viewMore($attachment->item->getDescription());  ?>
	                      </div>
	                    </div>
	                  </div>
	                <?php endif; ?>
	                </span>
	              <?php endforeach; ?>
	              <?php endif; ?>
	          <?php endif; ?>
	        </div>
	      <?php endif; ?>
	      
	      <?php // Icon, time since, action links ?>
	      <?php  $canComment = ( $action->getTypeInfo()->commentable &&
	            $this->viewer()->getIdentity() &&
	            Engine_Api::_()->authorization()->isAllowed($action->getCommentObject(), null, 'comment') &&
	            !empty($this->commentForm) );
	      ?>
              <div id='comment-likes-activity-item-<?php echo $action->action_id ?>' class='comment-likes-activity-item'>
	      <div class='feed_item_date feed_item_icon <?php echo 'activity_icon_'.$action->type ?>'>
	         <ul>          
	           <?php if( $canComment ): ?>
	            <?php if( $action->likes()->isLike($this->viewer()) ): ?>
	              <li class="feed_item_option_unlike">              	
	                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Unlike'), array('onclick' => 'javascript:en4.advancedactivity.unlike(this,' . $action->action_id . ');', 'action-title' => $this->translate('Like'))) ?>
	                <span>&#183;</span>
	              </li>
	            <?php else: ?>
	              <li class="feed_item_option_like">              
	                <?php
                      echo $this->htmlLink('javascript:void(0);', $this->translate('Like'), array('onclick' => 'javascript:en4.advancedactivity.like(this,' . $action->action_id . ');', 'title' =>
                          $this->translate('Like this item'), 'action-title' => $this->translate('Unlike')))
                      ?>
	                <span>&#183;</span>
	              </li>
	            <?php endif; ?>
	            <?php if( Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ): // Comments - likes ?>
	              <li class="feed_item_option_comment">               
	                <?php echo $this->htmlLink(array('route'=>'default','module'=>'activity','controller'=>'index','action'=>'viewcomment','action_id'=>$action->getIdentity(),'format'=>'smoothbox'), $this->translate('Comment'), array('class'=>'smoothbox', 'title'=> $this->translate('Leave a comment'))) ?>
	                <span>&#183;</span>
	              </li>
	            <?php else: ?>
	              <li class="feed_item_option_comment">               
	                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Comment'), array('onclick'=>'document.getElementById("'.$this->commentForm->getAttrib('id').'").style.display = ""; document.getElementById("'.$this->commentForm->submit->getAttrib('id').'").style.display = "block"; document.getElementById("'.$this->commentForm->body->getAttrib('id').'").focus();', 'title'=> $this->translate('Leave a comment'))) ?>
	                <span>&#183;</span>
	              </li>
	            <?php endif; ?>
	          <?php endif; ?>
              <?php if( $this->viewer()->getIdentity() && (
                    'user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) &&                  $advancedactivityCoreApi->hasFeedTag($action)                  
                  ): ?>
            <li class="feed_item_option_add_tag">             
              <?php echo $this->htmlLink(array(
                'route' => 'default',
                'module' => 'advancedactivity',
                'controller' => 'feed',
                'action' => 'tag-friend',
                'id' => $action->action_id
              ), $this->translate('Tag Friends'), array('class' => 'smoothbox', 'title' =>
                 $this->translate('Tag more friends'))) ?>
               <span>&#183;</span>
            </li>
          <?php elseif($this->viewer()->getIdentity() && $advancedactivityCoreApi->hasMemberTagged($action, $this->viewer())): ?>  
             <li class="feed_item_option_remove_tag">             
              <?php echo $this->htmlLink(array(
                'route' => 'default',
                'module' => 'advancedactivity',
                'controller' => 'feed',
                'action' => 'remove-tag',
                'id' => $action->action_id
              ), $this->translate('Remove Tag'), array('class' => 'smoothbox')) ?>
               <span>&#183;</span>
            </li>
          <?php endif; ?> 
	            <?php // Share ?>
	          <?php if( $action->getTypeInfo()->shareable && $this->viewer()->getIdentity() ): ?>
	            <?php if( $action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment()) ): ?>
	              <li class="feed_item_option_share">              
	                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'seaocore', 'controller' => 'activity', 'action' => 'share', 'type' => $attachment->item->getType(), 'id' => $attachment->item->getIdentity(),'action_id'=>$action->getIdentity(), 'format' => 'smoothbox',"not_parent_refresh"=>1), $this->translate('Share'), array('class' => 'smoothbox', 'title' => $this->translate('Share this by re-posting it with your own message.'))) ?>
	                <span>&#183;</span>
	              </li>
	            <?php elseif( $action->getTypeInfo()->shareable == 2 ): ?>
	              <li class="feed_item_option_share">                	
	                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'seaocore', 'controller' => 'activity', 'action' => 'share', 'type' => $subject->getType(), 'id' => $subject->getIdentity(),'action_id'=>$action->getIdentity(), 'format' => 'smoothbox','not_parent_refresh' => 1), $this->translate('Share'), array('class' => 'smoothbox', 'title' => $this->translate('Share this by re-posting it with your own message.'))) ?>
	                <span>&#183;</span>
	              </li>
	            <?php elseif( $action->getTypeInfo()->shareable == 3 ): ?>
	              <li class="feed_item_option_share">               
	                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'seaocore', 'controller' => 'activity', 'action' => 'share', 'type' => $object->getType(), 'id' => $object->getIdentity(),'action_id'=>$action->getIdentity(), 'format' => 'smoothbox','not_parent_refresh'=>1), $this->translate('Share'), array('class' => 'smoothbox', 'title' => $this->translate('Share this by re-posting it with your own message.'))) ?>
	                <span>&#183;</span>
	              </li>
	            <?php elseif( $action->getTypeInfo()->shareable == 4 ): ?>
	              <li class="feed_item_option_share">              
	                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'seacore', 'controller' => 'activity', 'action' => 'share', 'type' => $action->getType(), 'id' => $action->getIdentity(),'action_id'=>$action->getIdentity(), 'format' => 'smoothbox','not_parent_refresh' =>1), $this->translate('Share'), array('class' => 'smoothbox', 'title' => $this->translate('Share this by re-posting it with your own message.'))) ?>
	                <span>&#183;</span>
	              </li>
	            <?php endif; ?>
	          <?php endif; ?> 
	           <li>
	            <?php echo $this->timestamp($action->getTimeValue()) ?>
	          </li>   
	        </ul>
	      </div>
	      <?php // Share Count ?>
	      <?php if ( $action->getTypeInfo()->shareable  &&  ($share = $sharesTable->countShareOfItem(array ('parent_action_id' => $action->getIdentity())))>0):?>
	      	<div class="comments">
            <ul>
              <li>
                <div></div>
                <div>
                  <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'advancedactivity', 'controller' => 'index', 'action' => 'share-item', 'action_id' => $action->getIdentity(), 'format' => 'smoothbox'), $this->translate(array('%s share', '%s shares', $share), $this->locale()->toNumber($share) ), array('class' => 'smoothbox buttonlink seaocore_icon_share ')) ?>
                </div>
              </li>
            </ul>
          </div>
        <?php endif; ?>
       	<?php if( $action->getTypeInfo()->commentable ): // Comments - likes ?>
	        <div class='comments'>
	          <ul>
	            <?php if( $action->likes()->getLikeCount() > 0 && (count($action->likes()->getAllLikesUsers())>0) ): ?>
	              <li>
	                <div></div>
	                <div class="comments_likes">
	                  <?php if( $action->likes()->getLikeCount() <= 3 || $this->viewAllLikes ): ?>
	                    <?php echo $this->translate(array('%s likes this.', '%s like this.', $action->likes()->getLikeCount()), $this->aafFluentList($action->likes()->getAllLikesUsers()) )?>
	
	                  <?php else: ?>
	                    <?php echo $this->htmlLink($action->getSubject()->getHref(array('action_id' => $action->action_id, 'show_likes' => true)),
	                      $this->translate(array('%s person likes this', '%s people like this', $action->likes()->getLikeCount()), $this->locale()->toNumber($action->likes()->getLikeCount()) )
	                    ) ?>
	                  <?php endif; ?>
	                </div>
	              </li>
	            <?php endif; ?>              
	            <?php if( $action->comments()->getCommentCount() > 0 ): ?>
	              <?php if( $action->comments()->getCommentCount() > 5 && !$this->viewAllComments): ?>
	                <li>
	                  <div></div>
	                  <div class="comments_viewall">
	                    <?php if( $action->comments()->getCommentCount() > 2): ?>
	                      <?php echo $this->htmlLink($action->getSubject()->getHref(array('action_id' => $action->action_id, 'show_comments' => true)),
	                          $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()),
	                          $this->locale()->toNumber($action->comments()->getCommentCount()))) ?>
	                    <?php else: ?>
	                      <?php echo $this->htmlLink('javascript:void(0);',
	                          $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()),
	                          $this->locale()->toNumber($action->comments()->getCommentCount())),
	                          array('onclick'=>'en4.advancedactivity.viewComments('.$action->action_id.');')) ?>
	                    <?php endif; ?>
	                  </div>
	                </li>
	              <?php endif; ?>
	              <?php foreach( $action->getComments($this->viewAllComments) as $comment ): ?>
	                <li id="comment-<?php echo $comment->comment_id ?>">
	                   <div class="comments_author_photo">
	                      <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(),
	                        $this->itemPhoto($this->item($comment->poster_type, $comment->poster_id), 'thumb.icon', $action->getSubject()->getTitle()),
	                              array('class'=>'sea_add_tooltip_link', 'rel'=>$this->item($comment->poster_type, $comment->poster_id)->getType().' '.$this->item($comment->poster_type, $comment->poster_id)->getIdentity())
	                      ) ?>
	                   </div>
	                   <div class="comments_info">
	                     <span class='comments_author'>
	                       <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->item($comment->poster_type, $comment->poster_id)->getTitle(),
	                              array('class'=>'sea_add_tooltip_link', 'rel'=>$this->item($comment->poster_type, $comment->poster_id)->getType().' '.$this->item($comment->poster_type, $comment->poster_id)->getIdentity())
	                               
	                               ); ?>
	                     </span>
                       <span class="comments_body">
	                    <?php 
                                include APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/_commentBody.tpl';
                            ?>
                       </span> 
	                     <ul class="comments_date">
	                       <li class="comments_timestamp">
	                         <?php echo $this->timestamp($comment->creation_date); ?>
	                       </li>
	                       <?php if ( $this->viewer()->getIdentity() && (('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) || ($this->viewer()->getIdentity() == $comment->poster_id) || $this->activity_moderate)): ?>
	                       <li class="comments_delete">
	                         - <?php echo $this->htmlLink(array(
	                              'route'=>'default',
	                              'module'    => 'activity',
	                              'controller'=> 'index',
	                              'action'    => 'delete',
	                              'action_id' => $action->action_id,
	                              'comment_id'=> $comment->comment_id,
	                              ), $this->translate('delete'), array('class' => 'smoothbox')) ?>
	                       </li>
	                        <?php endif; ?>
	                        <?php if( $canComment ):
	                          $isLiked = $comment->likes()->isLike($this->viewer());
	                        ?>
	                          <li class="comments_like">
	                            -
	                            <?php if( !$isLiked ): ?>
	                              <a href="javascript:void(0)" onclick="en4.advancedactivity.like(this, <?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>);" action-title="<?php echo $this->translate('unlike') ?>" >
	                                <?php echo $this->translate('like') ?>
	                              </a>
	                            <?php else: ?>
	                              <a href="javascript:void(0)" onclick="en4.advancedactivity.unlike(this, <?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>);" action-title="<?php echo $this->translate('like') ?>">
	                                <?php echo $this->translate('unlike') ?>
	                              </a>
	                            <?php endif ?>
	                          </li>
	                        <?php endif ?>
	                        <?php if( $comment->likes()->getLikeCount() > 0 ): ?>
	                          <li class="comments_likes_total">
	                            -
	                            <a href="javascript:void(0);" id="comments_comment_likes_<?php echo $comment->comment_id ?>" class="comments_comment_likes" title="<?php echo $this->translate('Loading...') ?>">
	                              <?php echo $this->translate(array('%s likes this', '%s like this', $comment->likes()->getLikeCount()), $this->locale()->toNumber($comment->likes()->getLikeCount())) ?>
	                            </a>
	                          </li>
	                        <?php endif ?>
	                     </ul>
	                   </div>
	                </li>
	              <?php endforeach; ?>
	            <?php endif; ?>
	          </ul>
          <?php if( $canComment ) echo $this->commentForm->render() /*
          <form>
            <textarea rows='1'>Add a comment...</textarea>
            <button type='submit'>Post</button>
          </form>
          */ ?>
         </div> 
        </div>
              </div>
      <?php endif; ?>
    </li>
<?php endforeach; ?>
    <?php if($this->totalFeed>2):?>
    <li id="see_more_feed_bd_<?php echo $this->mainAction->action_id ?>" onclick="seeAllBDFeed($(this),<?php echo $this->mainAction->action_id ?>)">
      <a href="javascript:void(0)">
      	<i class="aaf_feed_more_arrow fleft"></i>
        <?php echo  $this->translate(array('See %1$s more feed', 'See %1$s more feeds', ($this->totalFeed -2)), $this->locale()->toNumber(($this->totalFeed -2)) ) ?>
      </a>
    </li>
    <?php endif;?>
  </ul>
  </div>
</li>
<?php if($this->isAbletoWish): ?>
<script type="text/javascript">
 en4.core.runonce.add(function() {
 if($('activity-bd-write-body-<?php echo $this->mainAction->action_id ?>')){ 
    new OverText($('activity-bd-write-body-<?php echo $this->mainAction->action_id ?>'), {
      poll: true,     
      pollInterval: 500,
      positionOptions: {
        position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
        edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
        offset: {
          x: ( en4.orientation == 'rtl' ? -4 : 4 ),
          y: 2
        }
      }
    });
} 
});
var birthdayPostREQActive=false;
function postWishFeed(e, users_id, action_id) {
  if(birthdayPostREQActive)
    return;
    var keycode=null;
    var url = '';
    
    if (e!=null){ 
      if (window.event!=undefined){
      if (window.event.keyCode) keycode = window.event.keyCode;
        else if (window.event.charCode) keycode = window.event.charCode;
      } else{
        keycode = e.keyCode;
      }
    }
    
    
    if( keycode == 13) {
       var  text =$('activity-bd-write-body-' + action_id).value;
        if( text == '' ) {
          return;
        }
        
        url = en4.core.baseUrl + 'birthday/index/statusubmit';
        birthdayPostREQActive=true;
        en4.core.request.send(new Request.HTML({
          url : url,
          data : {
            format : 'html',
            object_id : users_id,
            body :text
          },
          'onSuccess' : function(responseTree, responseElements, responseHTML, responseJavaScript)
          {
              $('new_post_bd_' + action_id).style.display='none';
              $('activity-bd-write-body-' + action_id).value='';
              birthdayPostREQActive=false;
          }
        })
       , {
      'element' : $('birthdate_feeds_'+action_id),
      'updateHtmlMode' : 'prepend'
       }
        )
      }
  }

</script>
<?php endif; ?>
<script type="text/javascript">
function seeAllBDFeed(elm, id){
  elm.style.display='none'; 
    var className= '.View_More_Birthday_Feed_'+id;
    var myElements = $$(className);                
    for(var i=0;i< myElements.length;i++){
      myElements[i].style.display=''; 
    }                
}
</script>
