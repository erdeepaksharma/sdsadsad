<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php
$this->headTranslate(array('Disconnect from Twitter', 'Your Tweet was over 140 characters. You\'ll have to be more clever.', 'Tweet', 'Shared on Twitter as reply to', 'We were unable to process your request. Wait a few moments and try again.', 'Updating...', 'Are you sure that you want to delete this tweet? This action cannot be undone.', 'Delete', 'cancel', 'Close', 'You need to be logged into Twitter to see your Twitter tweets.', 'Click here'));
?>


<?php if (empty($this->isajax) && empty($this->checkUpdate) && empty ($this->getUpdate)) : ?>
<?php if (empty($this->tabaction)){ ?>
<div id="showadvfeed-tweetfeed">
  <?php }?>
<?php if ($this->session_id) {?>
	
	<!--THIS DIV SHOWS ALL RECENT POSTS.-->
	
	<div id="feed-update-tweet">
	
	</div>
<script type='text/javascript'>
  action_logout_taken_tweet = 0;

</script>
<?php } else { ?>
       
          <?php if (!empty($this->TwitterLoginURL )) {  
            
             echo '<div class="clr twitterlogin-btn"><a class="t_l" data-icon="twitter-sign"  data-role="button" href="javascript:void(0);" onclick= "sm4.socialactivity.socialFeedLogin(\'' . $this->TwitterLoginURL . '\',\'widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed\', \'tweetfeed\')" >' . $this->translate('Sign in to Twitter') . '</a></div>';
						 ?>
						<script type='text/javascript'>
						action_logout_taken_tweet = 1;
						
						</script>
					
					<?php } 
									
							?>
		
</div>
<?php return; } ?>

    <?php 
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer && $viewer->getIdentity() ) {
    include APPLICATION_PATH.'/application/modules/Sitemobile/modules/Advancedactivity/views/scripts/_composerTwitter.tpl';
    
    } ?>
  <ul id="activity-feed-tweetfeed" class="feeds">
  <?php endif; ?>
    
 <?php if (empty($this->checkUpdate) && empty($this->getUpdate)) : ?>
    <script type="text/javascript"> 
  
  sm4.core.runonce.add(function() {  
    sm4.activity.makeFeedOptions('tweetfeed', <?php echo json_encode($this->allParams);?>, <?php echo json_encode($this->attachmentsURL);?>); 
  }); 
  </script>
  <?php endif;?>

  <?php
  $execute_script = 1;
  $current_tweet_statusid = 0;
  ?>
  <?php if (count($this->logged_TwitterUserfeed)) :

    foreach ($this->logged_TwitterUserfeed as $key => $Twitter) : ?>
      <?php if (!empty($Twitter->retweeted_status)) : ?>
        <?php
        if ($key == 0) :
          $current_tweet_statusid = $Twitter->retweeted_status->id_str;
        endif;
        ?>
      <?php $Screen_Name = $Twitter->retweeted_status->user->screen_name; ?>
      <?php $Tweet_description = Engine_Api::_()->advancedactivity()->getTwitterDescription($Twitter->retweeted_status->text); ?>
        <li id="activity-item-<?php echo $Twitter->retweeted_status->id_str; ?>">

          <div id="main-feed-<?php echo $Twitter->retweeted_status->id_str; ?>">
            <div class="feed_item_header">
      <?php if (($this->id_CurrentLoggedTweetUser == $Twitter->retweeted_status->user->id_str)) : ?>
                <div class="feed_items_options_btn">        
                  <a href="javascript:void(0);" onclick="sm4.activity.showOptions('<?php echo $Twitter->retweeted_status->id_str; ?>')" data-role="button" data-icon="cog" data-iconpos="notext" data-theme="c" data-inline="true"></a>
                </div>
      <?php endif; ?>

              <div class='feed_item_photo'>
                <a href= "https://twitter.com/<?php echo $Twitter->retweeted_status->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->retweeted_status->user->name; ?>">
                  <img src="<?php echo str_replace("http://", "https://", $Twitter->retweeted_status->user->profile_image_url); ?>" alt="" /> 
                </a>  
              </div>
              <div class="feed_item_status">
                <div class="feed_item_generated"> 
                  <a href= "https://twitter.com/<?php echo $Twitter->retweeted_status->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->retweeted_status->user->name; ?>" class="feed_item_username">  
                  <?php echo $Twitter->retweeted_status->user->screen_name; ?>
                  </a>
      <?php echo $Twitter->retweeted_status->user->name; ?>
                </div>
              </div>

            </div>

            <div class='feed_item_body'>

              <?php
              $Tweet_description = Engine_Api::_()->advancedactivity()->getURLString($Tweet_description);
              echo $Tweet_description;
              ?>

            </div>

            <div class="feed_item_btm">
              <span class="feed_item_date">
                <?php echo $this->timestamp(strtotime($Twitter->retweeted_status->created_at)); ?>        	
              </span>
              <span class="sep">-</span>
              <span>
      <?php echo $this->translate('Retweeted by'); ?>
              </span>
              <a href= "https://twitter.com/<?php echo $Twitter->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->user->name; ?>">

                <span><?php echo $Twitter->user->screen_name; ?></span>
              </a>           

            </div>


            <div class="feed_item_option">

              <div data-role="navbar" data-inset="false">
                <ul>

      <?php if (!empty($Twitter->favorited)) : ?>
                    <li>

                      <a href="javascript:void(0);" onclick="sm4.socialactivity.twitter.favourite_Tweet('<?php echo $Twitter->retweeted_status->id_str; ?>', '0');">                       
                        <i class="ui-icon ui-icon-fastar"></i>
                        <span><?php echo $this->translate('Favorited') ?></span></a>          

                    </li>
      <?php else: ?>
                    <li>

                      <a href="javascript:void(0);" onclick="sm4.socialactivity.twitter.favourite_Tweet('<?php echo $Twitter->retweeted_status->id_str; ?>', '1');">                       
                        <i class="ui-icon ui-icon-star-empty"></i>
                        <span><?php echo $this->translate('Favorite') ?></span></a>          

                    </li>
      <?php endif; ?>
      <?php if (($this->id_CurrentLoggedTweetUser != $Twitter->retweeted_status->user->id_str) && !in_array($Twitter->retweeted_status->id_str, $this->retweets_by_me)) : ?>
                    <li id="retweet_tweet_<?php echo $Twitter->retweeted_status->id_str; ?>">
                      <a href="javascript:void(0);" class="aaf_tweet_icon_retweet aaf_tweet_icon" onclick="sm4.socialactivity.twitter.reTweet('<?php echo $Twitter->retweeted_status->id_str; ?>');">
                        <i class="ui-icon ui-icon-retweet"></i>
                        <span><?php echo $this->translate('Retweet'); ?></span>
                      </a>
                    </li>
      <?php endif; ?>

                  <li>
                    <a href="javascript:void(0);" class="feed_likes" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'reply-twitter', 'tweet_id' => $Twitter->retweeted_status->id_str, 'screen_name' => $Twitter->user->screen_name), 'default', 'true'); ?>", "feedsharepopup")'> 
                      <i class="ui-icon ui-icon-comment"></i>
                      <span><?php echo $this->translate('Reply'); ?></span>
                    </a>
                  </li>                 
                </ul>
              </div>           
            </div> 
          </div>  
          <div id="feed-options-<?php echo $Twitter->retweeted_status->id_str; ?>" class="feed_item_option_box" style="display:none">
            <?php if (($this->id_CurrentLoggedTweetUser == $Twitter->retweeted_status->user->id_str)) :
              $front = Zend_Controller_Front::getInstance();
              ?>
              <a href="javascript:void(0);" onclick="javascript:sm4.activity.activityremove(this);" title="Delete" class="ui-btn-default ui-btn-danger" data-message="0-<?php echo  $Twitter->retweeted_status->id_str ?>" data-url="<?php echo (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $front->getRequest()->getBaseUrl();?>/widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed?format=json&is_ajax=6&tweetstatus_id=<?php echo  $Twitter->id_str ?>" >
        <?php echo $this->translate('Delete Feed') ?>
              </a>
        <?php endif; ?>
            <a href="#" class="ui-btn-default"onclick="sm4.activity.hideOptions('<?php echo $Twitter->retweeted_status->id_str ?>');">
    <?php echo $this->translate("Cancel"); ?>
             </a>
          </div>     
        </li>

      <?php else : ?>

        <?php
        if ($key == 0) :
          $current_tweet_statusid = $Twitter->id_str;
        endif;
        ?>     
      <?php $Screen_Name = @$Twitter->retweeted_status->user->screen_name; ?>
      <?php $Tweet_description = Engine_Api::_()->advancedactivity()->getTwitterDescription(@$Twitter->text); ?>
        <li id="activity-item-<?php echo @$Twitter->id_str; ?>">


          <div id="main-feed-<?php echo @$Twitter->id_str; ?>">

            <div class="feed_item_header">
      <?php if (($this->id_CurrentLoggedTweetUser == @$Twitter->user->id_str)) : ?>
                <div class="feed_items_options_btn">        
                  <a href="javascript:void(0);" onclick="sm4.activity.showOptions('<?php echo $Twitter->id_str; ?>')" data-role="button" data-icon="cog" data-iconpos="notext" data-theme="c" data-inline="true"></a>
                </div>
      <?php endif; ?>

              <div class='feed_item_photo'>
                <a href= "https://twitter.com/<?php echo $Twitter->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->user->name; ?>">
                  <img src="<?php echo str_replace("http://", "https://", $Twitter->user->profile_image_url); ?>" alt="" /> 
                </a>
              </div>
              <div class="feed_item_status">
                <div class="feed_item_generated"> 
                  <a href= "https://twitter.com/<?php echo $Twitter->user->screen_name; ?>" target="_blank" title="<?php echo $Twitter->user->name; ?>" class="feed_item_username">  
      <?php echo $Twitter->user->screen_name; ?>
                  </a>
      <?php echo $Twitter->user->name; ?>
                </div>
              </div>

            </div>

            <div class='feed_item_body'>

      <?php
      $Tweet_description = Engine_Api::_()->advancedactivity()->getURLString($Tweet_description);
      echo $Tweet_description;
      ?>

            </div>

            <div class="feed_item_btm">
              <span class="feed_item_date">
      <?php echo $this->timestamp(strtotime($Twitter->created_at)); ?>        	
              </span>         

            </div>


            <div class="feed_item_option">

              <div data-role="navbar" data-inset="false">
                <ul>

      <?php if (!empty($Twitter->favorited)) : ?>
                    <li id="favourite_tweet_<?php echo $Twitter->id_str; ?>">

                      <a href="javascript:void(0);" onclick="sm4.socialactivity.twitter.favourite_Tweet('<?php echo $Twitter->id_str; ?>', '0');" title="Unfavorite" class="aaf_tweet_icon aaf_tweet_icon_unfav">                       
                         <i class="ui-icon ui-icon-fastar"></i>
                        <span><?php echo $this->translate('Favorited') ?></span></a>          

                    </li>
      <?php else: ?>
                    <li id="favourite_tweet_<?php echo $Twitter->id_str; ?>">

                      <a href="javascript:void(0);" onclick="sm4.socialactivity.twitter.favourite_Tweet('<?php echo $Twitter->id_str; ?>', '1');" title="Unfavorite" class="aaf_tweet_icon aaf_tweet_icon_unfav">                       
                        <i class="ui-icon ui-icon-star-empty"></i>
                        <span><?php echo $this->translate('Favorite') ?></span></a>          

                    </li>
      <?php endif; ?>
                  <?php if (($this->id_CurrentLoggedTweetUser != $Twitter->user->id_str) && !in_array($Twitter->id_str, $this->retweets_by_me)) : ?>
                    <li id="retweet_tweet_<?php echo $Twitter->id_str; ?>">
                      <a href="javascript:void(0);" class="aaf_tweet_icon_retweet aaf_tweet_icon" onclick="sm4.socialactivity.twitter.reTweet('<?php echo $Twitter->id_str; ?>');">
                        <i class="ui-icon ui-icon-retweet"></i>
                        <span><?php echo $this->translate('Retweet'); ?></span>
                      </a>
                    </li>
      <?php endif; ?>

                   <li>
                    <a href="javascript:void(0);" class="feed_likes" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'reply-twitter', 'tweet_id' => $Twitter->id_str, 'screen_name' => $Twitter->user->screen_name), 'default', 'true'); ?>", "feedsharepopup")'> 
                      <i class="ui-icon ui-icon-comment"></i>
                      <span><?php echo $this->translate('Reply'); ?></span>
                    </a>
                  </li>                 
                </ul>
              </div>           
            </div> 
          </div>  
          <div id="feed-options-<?php echo $Twitter->id_str; ?>" class="feed_item_option_box" style="display:none">
      <?php if (($this->id_CurrentLoggedTweetUser == $Twitter->user->id_str)) :
          $front = Zend_Controller_Front::getInstance();
        
        ?>
              <a href="javascript:void(0);" onclick="javascript:sm4.activity.activityremove(this);" title="Delete" class="ui-btn-default ui-btn-danger" data-message="0-<?php echo  $Twitter->id_str ?>" data-url="<?php echo (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $front->getRequest()->getBaseUrl();?>/widget/index/mod/advancedactivity/name/advancedactivitytwitter-userfeed?format=json&is_ajax=6&tweetstatus_id=<?php echo  $Twitter->id_str ?>">
        <?php echo $this->translate('Delete Feed') ?>
              </a>
        <?php endif; ?>
             <a href="#" class="ui-btn-default"onclick="sm4.activity.hideOptions('<?php echo $Twitter->id_str ?>');">
    <?php echo $this->translate("Cancel"); ?>
             </a>
          </div>
        </li>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if (empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate)) : ?>
    </ul>
  <?php endif; ?>     
<?php else: ?>
  <?php
  if (!empty($this->TwitterLoginURL) && empty($this->checkUpdate) && empty($this->getUpdate)) {
    $execute_script = 0;
    ?>
    <div class="aaf_feed_tip"><?php echo $this->translate('Twitter is currently experiencing technical issues, please try again later.'); ?></div>

  <?php } else { ?>


  <?php } endif; ?>





  <?php if (empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate)) : ?>

  <div data-role="popup" id="popupDialog" data-overlay-theme="a" data-theme="c" data-dismissible="false" style="max-width:400px;" class="ui-corner-all">
    <div data-role="header" data-theme="a" class="ui-corner-top">
      <h1><?php echo $this->translate('Delete Activity Item?'); ?></h1>
    </div>
    <div data-role="content" data-theme="d" class="ui-corner-bottom ui-content">
      <h3 class="ui-title"></h3>
      <p><?php echo $this->translate('Are you sure that you want to delete this activity item? This action cannot be undone.') ?></p>

      <a href="#" data-role="button" data-inline="true" data-rel="back" data-transition="flow" data-theme="b" onclick="javascript:sm4.activity.activityremove()"><?php echo $this->translate("Delete"); ?></a>
      <a href="#" data-role="button" data-inline="true" data-rel="back" data-theme="c"><?php echo $this->translate("Cancel"); ?></a>
    </div>
  </div>
   <div class="feed_viewmore" id="feed_viewmore-tweetfeed" style="display: none;">
<?php
echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
    'id' => 'feed_viewmore_link-tweetfeed',
    'class' => 'ui-btn-default icon_viewmore'
))
?>
  </div>

  <div class="feeds_loading" id="feed_loading-tweetfeed" style="display: none;">
    <i class="ui-icon-spinner ui-icon icon-spin"></i>
  </div>

  <div class="feeds_no_more tip" id="feed_no_more-tweetfeed" style="display: <?php echo ($this->allParams['endOfFeed']) ? 'block' : 'none' ?>;">
    <span>
<?php echo $this->translate("There are no more posts to show.") ?>
    </span>  
  </div>
    <?php if (empty($this->tabaction)){ ?>
</div>  
<?php }?>
<?php endif; ?>