<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    https://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php 
$this->headTranslate(array('Disconnect from Facebook', 'Your Facebook status could not be updated. Please try again.', 'What\'s on your mind?', 'Write a comment...', 'Unlike', 'Like this item', 'Like', 'You need to be logged into Facebook to see your Facebook News Feed.'));
$first_fbid = 0;
?>
<?php if ($this->session_id) { ?>

<?php if (!empty($this->paging['previous'])&& !empty($this->changefirstid)) : 
          $first_fbid = explode("&since=",$this->paging['previous']);
          $first_fbid = explode("&",$first_fbid[1]); 
 
  endif;?>
<?php } ?>

<?php if (empty($this->isajax) && empty($this->checkUpdate)) : ?>
<?php if (empty($this->tabaction)){ ?>
<div id="showadvfeed-fbfeed">
<?php } ?>  
  <?php if ($this->session_id) { ?>
  
  
  
  

    <!--THIS DIV SHOWS ALL RECENT POSTS.-->

    <div id="feed-update-fb">
    </div>
    <script type='text/javascript'>
      action_logout_taken_fb = 0;

    </script>

  <?php } else { ?>
      <?php if (!empty($this->loginUrl)) {
        echo '<div class="clr fblogin-btn"><a class="t_l" data-icon="facebook-sign"  data-role="button" href="javascript:void(0);" onclick= "sm4.socialactivity.socialFeedLogin(\'' . $this->loginUrl . '\',\'widget/index/mod/advancedactivity/name/advancedactivityfacebook-userfeed\', \'fbfeed\')" >' . $this->translate('Sign in to Facebook') . '</a></div>'; ?>
        <script type='text/javascript'>
          action_logout_taken_fb = 1;
      						
        </script>
      <?php } ?>
      			</div>
    <?php return; } ?>
  
  

    <?php 
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer && $viewer->getIdentity() ) {
			include APPLICATION_PATH.'/application/modules/Sitemobile/modules/Advancedactivity/views/scripts/_composerFacebook.tpl';
    
    } ?>
  <ul id="activity-feed-fbfeed" class="feeds">
  <?php endif; ?>

  
  
 <?php if (empty($this->getUpdate) && empty($this->checkUpdate)):?>
<script type="text/javascript"> 
  //update_freq_fb = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.update.frequency', 120000); ?>; 
  sm4.core.runonce.add(function() { 
  
    sm4.activity.makeFeedOptions('fbfeed', $.extend(<?php echo json_encode($this->allParams);?>, {'firstID' : '<?php echo $first_fbid[0];?>'}), <?php echo json_encode($this->attachmentsURL);?>); 
  }); 
</script>
  <?php elseif (!empty($this->getUpdate)) :?>
  <script type="text/javascript"> 
    sm4.core.runonce.add(function() { 
      sm4.socialactivity.setUpdateData('<?php echo $first_fbid[0];?>', 'fbfeed');
    });
  </script>  
   <?php endif;?>

  <?php
  $execute_script = 1;
//MAKING THE FACEBOOK FEED HTML
  if (!empty($this->logged_userfeed) && count($this->logged_userfeed['data']) && empty($this->checkUpdate)) :
    if (empty($this->getFacebokStream)): return;
    endif;
    foreach ($this->data as $key => $feed) {
      $actions = explode("_", $feed['id']);
      if (empty($actions[0]) && !empty($actions[1]))
        continue;

      if (isset($feed['application']) && isset($feed['application']['name']) && $feed['type'] == 'status' && !isset($feed['actions']))
        continue;
      ?>

      <li>
        <div id="main-feed-<?php echo $feed['id'] ?>">

          <div class="feed_item_header">
            <div class='feed_item_photo'>
              <a href="https://www.facebook.com/<?php echo $feed['from']['id']; ?>" target="_blank">
                <img src="https://graph.facebook.com/<?php echo $feed['from']['id']; ?>/picture" alt="" class="thumb_icon" />
              </a>
            </div>
            <div class="feed_item_status">
                <?php // Main Content  ?>
              <div class="feed_item_generated">
                <?php
                //IF TYPE IS QUESTION TYPE THEN WE WILL NOT SHOW THE OPTION FOR LIKE AND COMMENT.
                $story = '';
                if (($feed['type'] == 'question' || @$feed['application']['name'] == 'Questions' || $feed['type'] == 'status') && !empty($feed['story'])) {
                  $story_content = explode('asked: ', $feed['story']);
                  if (isset($story_content[1]) && !empty($story_content[1])) {
                    $story .= '<a href="https://www.facebook.com/' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .
                            $feed['from']['name'] . '</a> asked: ' . '<a href="https://www.facebook.com/questions/' . $feed['object_id'] . '" target="_blank" class="feed_item_username">' . $story_content[1] . '</a>';
                  } else if ($feed['type'] == 'status') {
                    $substring_story = '<a href="https://www.facebook.com/' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .
                            $feed['from']['name'] . '</a>';
                    $story = str_replace($feed['from']['name'], $substring_story, $feed['story']);
                  }
                } else {
                  if ($feed['type'] != 'photo' || (($feed['type'] == 'photo') && empty($feed['story']))) {
                    $story .= '<a href="https://www.facebook.com/' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .
                            $feed['from']['name'] . '</a>';
                  }
                }
                if (empty($story) && !empty($feed['story']) && !empty($feed['message'])) {
                  $story = $feed['story'];
                }
                echo $story;
                ?>
                <?php
                if (empty($feed['message']) && !empty($feed['story'])) {

                  if ($feed['type'] == 'link')
                    echo $this->translate('shared') . ' <a href="' . $feed['link'] . '" target="_blank">' . $this->translate('a link') . ' . </a>';
                  else if ($feed['type'] == 'photo') {
                    $substring_story = '<a href="https://www.facebook.com/' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .
                            $feed['from']['name'] . '</a>';
                    $feed['story'] = str_replace($feed['from']['name'], $substring_story, $feed['story']);

                    echo $feed['story'];
                  }
                }
                ?>

    <?php if (!empty($feed['to']) && empty($feed['message_tags'])) : ?>
                  <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Advancedactivity/externals/images/arrow-right.png" alt="" /><?php echo ' <a href="https://www.facebook.com/' . $feed['to']['data'][0]['id'] . '" target="_blank" class="feed_item_username">' . $feed['to']['data'][0]['name'] . '</a>'; ?>
    <?php endif; ?>
              </div>  
            </div>        
          </div>

          <div class='feed_item_body'>

              <?php $id_text = 0;

              if (!empty($feed['message'])) { ?>
              <div class="aaf_feed_item_des">
                <?php
                $Message_Length = strlen($feed['message']);

                if ($Message_Length > 200) {
                  $message = substr($feed['message'], 0, 200);
                  $id_text = $actions[1] . '_' . $key;
                  $message = $message . '... <a href="javascript:void(0);" onclick="AAF_showText_More(1, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
                } else {
                  $message = $feed['message'];
                }
                ?>

                <span id="fbmessage_text_short_<?php echo $id_text; ?>">
                  <?php
                  $message = Engine_Api::_()->advancedactivity()->getURLString($message);

                  //CHECK IF A CONTENT IS TAGED IN THE MESSAGE OR NOT.

                  if (!empty($feed['message_tags']) && !empty($feed['to'])) :
                    //FIND THE FIRST OCCURANCE OF THE TAGED CONTENT
                    foreach ($feed['to']['data'] as $to) :

                      $substring = '<a href="https://www.facebook.com/' . $to['id'] . '" target="_blank" class="feed_item_username">' . $to['name'] . '</a>';
                      $message = str_replace($to['name'], $substring, $message);
                    endforeach;
                  endif;
                  if($feed['type'] == 'status')
                    echo nl2br($message);
                  ?>
                </span>
                <div id="fbmessage_text_full_<?php echo $id_text; ?>" style="display:none;">
                  <?php
                  $feed['message'] = Engine_Api::_()->advancedactivity()->getURLString($feed['message']);
                  //CHECK IF A CONTENT IS TAGED IN THE MESSAGE OR NOT.

                  if (!empty($feed['message_tags']) && !empty($feed['to'])) :
                    //FIND THE FIRST OCCURANCE OF THE TAGED CONTENT
                    foreach ($feed['to']['data'] as $to) :

                      $substring = '<a href="https://www.facebook.com/' . $to['id'] . '" target="_blank" class="feed_item_username">' . $to['name'] . '</a>';
                      $feed['message'] = str_replace($to['name'], $substring, $feed['message']);
                    endforeach;
                  endif;
                   if($feed['type'] == 'status')
                      echo nl2br($feed['message']);
                  ?>
                </div>
              </div>
                  <?php } ?>


    <?php if (!empty($feed['picture']) || !empty($feed['name']) || !empty($feed['caption']) || !empty($feed['description']) || !empty($feed['properties'])) : ?>
            <div class="feed_item_attachments_wapper">
              <div class='feed_item_attachments'>
                <span class="feed_item_attachment">
                  <div>
                        <?php if (!empty($feed['picture']) && !empty($feed['link'])) : ?>
                      <a href="<?php echo $feed['link']; ?>" target="_blank" <?php if ($feed['type'] == 'photo'): ?> class="aaf_feed_attachment_facebook_photo" <?php endif; ?> ><img src="<?php echo $feed['picture']; ?>" alt="" /></a> 
                          <?php endif; ?>
                    <div>   	     
                          <?php if (!empty($feed['name']) && !empty($feed['link'])) : ?>
                        <div class="feed_item_link_title">
                          <a href="<?php echo $feed['link']; ?>" target="_blank" class="title"><?php echo $feed['name']; ?></a> 
                        </div>  
                          <?php endif; ?>
                      <div class="feed_item_link_desc">
                          <?php if (!empty($feed['caption'])) : ?>
                          <span><?php
                    $feed['caption'] = Engine_Api::_()->advancedactivity()->getURLString($feed['caption']);
                    $caption_Length = strlen($feed['caption']);
                    if ($caption_Length > 200) {
                      $caption = substr($feed['caption'], 0, 200);
                      $id_text = 'caption_' . $actions[1] . '_' . $key;
                      $caption = $caption . '... <a href="javascript:void(0);" onclick="AAF_showText_More(2, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
                    } else {
                      $caption = $feed['caption'];
                    }
                            ?> 
                            <div id="fbdescript_text_short_<?php echo $id_text; ?>">
                          <?php
                          $caption = Engine_Api::_()->advancedactivity()->getURLString($caption);
                          echo nl2br($caption);
                          ?>
                            </div>
                            <div id="fbdescript_text_full_<?php echo $id_text; ?>" style="display:none;">
                          <?php
                          $feed['caption'] = Engine_Api::_()->advancedactivity()->getURLString($feed['caption']);
                          echo nl2br($feed['caption']);
                          ?>
                            </div>       	
                          </span> 
                          <?php endif; ?>
                          <?php
                          if (!empty($feed['description'])) :
                            $Description_Length = strlen($feed['description']);
                            if ($Description_Length > 200) {
                              $description = substr($feed['description'], 0, 200);
                              $id_text = $actions[1] . '_' . $key;
                              $description = $description . '... <a href="javascript:void(0);" onclick="AAF_showText_More(2, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") . '</a>';
                            } else {
                              $description = $feed['description'];
                            }
                            ?> 
                          <div id="fbdescript_text_short_<?php echo $id_text; ?>">
                          <?php
                          $description = Engine_Api::_()->advancedactivity()->getURLString($description);
                          echo nl2br($description);
                          ?>
                          </div>
                          <div id="fbdescript_text_full_<?php echo $id_text; ?>" style="display:none;">
        <?php
        $feed['description'] = Engine_Api::_()->advancedactivity()->getURLString($feed['description']);
        echo nl2br($feed['description']);
        ?>
                          </div>
      <?php endif; ?>
      <?php if (!empty($feed['properties']) && isset($feed['properties'][0]) && isset($feed['properties'][0]['name'])) : ?>

        <?php
        if (!empty($feed['properties'][0]['href']))
          echo '<br /> &#160 ' . $feed['properties'][0]['name'] . ' :<a href="' . $feed['properties'][0]['href'] . '" target="_blank"> ' . $feed['properties'][0]['text'] . '</a><br />';
        else
          echo '<br /> &#160 ' . $feed['properties'][0]['name'] . ' : ' . $feed['properties'][0]['text'] . '<br />';
        ?>
              <?php endif; ?>
                      </div>	
                    </div>
                  </div>
                </span>
              </div>
            </div>
            <?php endif; ?>


          </div>


          <div class="feed_item_btm">
            <span class="feed_item_date">
              <?php
               if ($feed['type'] == 'status') 
                     $post_url = "https://www.facebook.com/" . $actions[1] ;           
                   else
                    $post_url = "https://www.facebook.com/" . $actions[0] . "/posts/" . $actions[1] ; ?>
    <?php echo $this->timestamp(strtotime($feed['created_time'])) ?>
            </span>
    <?php if (isset($feed['likes']) && !empty($feed['likes']['count'])) : ?>
              <span class="sep">-</span>
              <a href="javascript:void(0);" class="feed_likes" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'get-fb-feed-likes', 'action_id' => isset($feed['object_id']) ? $feed['object_id'] :  $actions[1]), 'default', 'true'); ?>", "feedsharepopup")'>

                <span><?php echo $this->translate(array('%s like', '%s likes', $feed['likes']['count']), $this->locale()->toNumber($feed['likes']['count'])); ?></span>
              </a>	
      <?php if (isset($feed['comments'])) : echo '<span class="sep">-</span>' ?> 
                <a href="javascript:void(0);" class="feed_likes" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'get-fb-feed-comments', 'action_id' => isset($feed['object_id']) ? $feed['object_id'] :  $actions[1], 'like_count' => $feed['likes']['count']), 'default', 'true'); ?>", "feedsharepopup")'>

                  <span><?php //echo $this->translate(array('%s comment', '%s comments', $feed['comments']['count']), $this->locale()->toNumber($feed['comments']['count']));
                              echo $this->translate('Comment');
      endif; ?></span>
              </a>
           <?php elseif (isset($feed['comments']) && !empty($feed['comments']['count'])) : ?>
              <span class="sep">-</span>
              <a href="<?php echo $post_url;?>"  class="feed_comments" target="_blank">

                <span><?php echo $this->translate(array('%s comment', '%s comments', $feed['comments']['count']), $this->locale()->toNumber($feed['comments']['count'])); ?></span>
              </a>
              
              <?php elseif (!empty($feed['object_id'])) : ?>
              
              <span class="sep">-</span>
              <a href="https://www.facebook.com/questions/<?php echo $feed['object_id'] ?>"  target="_blank">

                <span><?php $this->translate('Ask Friends') ?></span>
              </a>
              
            <?php endif; ?>
          </div>
          
          
           <div class="feed_item_option">
            <?php if ($feed['type'] != 'question' && @$feed['application']['name'] != 'Questions'): ?>  
              <?php 
                    $current_user_like = 0;
                    $FB_action = 'post';
                    $class = 'ui-icon ui-icon-thumbs-up';
                    $like_unlike = $this->translate('Like');
                    if (!empty($feed['likes']['count'])) :
                      $post_count = $feed['likes']['count'];
                      if (!empty($feed['likes']['data'])) :
                        foreach ($feed['likes']['data'] as $like_uid) {
                          if ($like_uid['id'] == $this->FBuid) :
                            $current_user_like = 1;
                            $FB_action = 'delete';
                            $like_unlike = $this->translate('Unlike');
                            $class = 'ui-icon ui-icon-thumbs-down';
                          else :

                            continue;
                          endif;
                        }
                      endif;
                    else:
                      $post_count = 0;
                    endif;
                    
                    if ($feed['type'] == 'status') 
                     $post_url = "https://www.facebook.com/" . $actions[1] ;           
                   else
                    $post_url = "https://www.facebook.com/" . $actions[0] . "/posts/" . $actions[1] ;
              
              ?>
              <div data-role="navbar" data-inset="false">
                <ul>
                  <?php if ($feed['type'] == 'photo' || $feed['type'] == 'link' || $feed['type'] == 'note' || $feed['type'] == 'status' || $feed['type'] == 'music' || $feed['type'] == 'video') : ?>                    
                      <li>
                        <a href="<?php echo $post_url;?>" target="_blank">
                          <i class="<?php echo $class;?>"></i>
                          <span><?php echo $like_unlike; ?></span>
                        </a>
                      </li>
                    
                    <li>
                        <a href="<?php echo $post_url;?>" target="_blank">
                          <i class="ui-icon ui-icon-comment"></i>
                          <span><?php echo $this->translate('Comment'); ?></span>
                        </a>
                      </li>
                  <?php endif; ?>

                  <?php // Share  ?>
                  <?php if (!empty($feed['object_id']) || (isset($action[1]) && !empty($action[1]))): ?>
                     <?php $shares = (!empty($feed['object_id'])) ? $feed['object_id']: $actions[1];	?>
  
                     <li>
                        <a href="<?php echo $post_url;?>" target="_blank">
                          <i class="ui-icon ui-icon-share-alt"></i>
                          <span><?php echo $this->translate('Share'); ?></span>
                        </a>
                      </li>
                    
                  <?php endif; ?>
                
                </ul>
              </div>
            <?php endif;?>
          </div> 

        </div>

      </li>
  <?php }?>  


  <?php
  else:
    if (empty($this->next_previous) && empty($this->checkUpdate) && empty($this->getUpdate) && $this->changefirstid == 1) :
      $execute_script = 0;
      echo '<div class="aaf_feed_tip">' . $this->translate('No Feed items to display. Try') . ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_FB(\'' . $this->loginUrl . '\')" >' . $this->translate('refreshing') . '</a></div>';

    endif;
  endif;
  ?>

  <?php if (empty($this->isajax) && empty($this->checkUpdate)) : ?>
  </ul>
    
     <div class="feed_viewmore" id="feed_viewmore-fbfeed" style="display: none;">
<?php
echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
    'id' => 'feed_viewmore_link-fbfeed',
    'class' => 'ui-btn-default icon_viewmore'
))
?>
  </div>

  <div class="feeds_loading" id="feed_loading-fbfeed" style="display: none;">
    <i class="ui-icon-spinner ui-icon icon-spin"></i>
  </div>

  <div class="feeds_no_more tip" id="feed_no_more-fbfeed" style="display: <?php echo ($this->allParams['endOfFeed']) ? 'block' : 'none' ?>;">
    <span>
<?php echo $this->translate("There are no more posts to show.") ?>
    </span>  
  </div>
    <?php if (empty($this->tabaction)){ ?>
</div>
<?php } ?>
  <?php endif; ?>	

		  
