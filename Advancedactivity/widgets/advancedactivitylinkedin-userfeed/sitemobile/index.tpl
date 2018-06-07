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
 $this->headTranslate(array('Disconnect from LinkedIn','We were unable to process your request. Wait a few moments and try again.','Updating...','Are you sure that you want to delete this comment? This action cannot be undone.','Delete','cancel','Close','You need to be logged into LinkedIn to see your LinkedIn Connections Feed.','Click here'));	
 
 ?>


<?php if (empty($this->isajax) && empty($this->checkUpdate) && empty ($this->getUpdate)) : ?>
<?php if (empty($this->tabaction)){ ?>   
<div id="showadvfeed-linkedinfeed">
 <?php } ?> 
<?php if ($this->Linkedin_FeedCount > 0) { ?>
	
	<!--THIS DIV SHOWS ALL RECENT POSTS.-->
	
	<div id="feed-update-linkedin">
	
	</div>
<script type='text/javascript'>
  action_logout_taken_linkedin = 0;

</script>
<?php } else { ?>
  <?php if (!empty($this->LinkedinLoginURL )) { 
    echo '<div class="clr linkedinlogin-btn"><a class="t_l" data-icon="linkedin-sign"  data-role="button" href="javascript:void(0);" onclick= "sm4.socialactivity.socialFeedLogin(\''. $this->LinkedinLoginURL. '\', \' widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed \', \'linkedinfeed\')" >' . $this->translate('Sign in to LinkedIn') . '</a></div>'; ?>
    <script type='text/javascript'>
    action_logout_taken_linkedin = 1;
    </script>
   <?php 
   
   if (empty($this->tabaction)){
     echo '</div>';
     return;
   }
   
  } ?>
		
<?php } ?>

<?php 
$viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer && $viewer->getIdentity() ) {
include APPLICATION_PATH.'/application/modules/Sitemobile/modules/Advancedactivity/views/scripts/_composerLinkedin.tpl';

} ?>
<?php if ($this->Linkedin_FeedCount> 0) :?>
<ul id="activity-feed-linkedinfeed" class="feeds">
<?php endif;?>  
<?php endif; ?>
<?php $view_moreconnection_linkedin = 0;
$last_linkedin_timestemp = '';

 if (empty($this->isajax) || !empty($this->next_previous) ) :

if ($this->Linkedin_FeedCount > 1)
	$last_linkedin_timestemp = $this->LinkedinFeeds['update'][--$this->Linkedin_FeedCount]['timestamp'];
else
	$last_linkedin_timestemp = $this->LinkedinFeeds['update']['timestamp'];
	$view_moreconnection_linkedin = 1;
	
endif;?>



<?php 
$execute_script = 1;
$current_linkedin_timestemp = 0;  


?>
<?php if ($this->Linkedin_FeedCount> 0) :
    $Api_linkedin = new Seaocore_Api_Linkedin_Api();
    $prev_timestamp = 0;
    foreach ($this->LinkedinFeeds['update'] as $key => $Linkedin) :?>
					<?php 
              $body = '';
              $status = '';
							if (!isset($this->LinkedinFeeds['update'][0])):
								$Linkedin = $this->LinkedinFeeds['update'];
						    $current_linkedin_timestemp= $Linkedin['timestamp'];?>                
						<?php endif; ?>	
        <?php if ($key == 0 && empty($this->next_previous)) :
                $current_linkedin_timestemp= $Linkedin['timestamp'];?>
               
                
         <?php endif; ?>
         <?php //REMOVE THE DUPLICACY OF FEEDS.
              
               if($prev_timestamp == $Linkedin['timestamp'])
                 continue;
               $prev_timestamp = $Linkedin['timestamp'];
          ?>
         <?php if (!isset($Linkedin['update-content']['person']) || !isset($Linkedin['update-content']['person']['site-standard-profile-request']['url']))
                continue;?>
         <?php $Screen_Name_connected = '';?>       
        <?php $Screen_Name_connecter = $Linkedin['update-content']['person']['first-name'] . ' ' . $Linkedin['update-content']['person']['last-name']; ?>
                
            <?php if (isset($Linkedin['update-content']['person']['connections']) && $Linkedin['update-content']['person']['connections']['@attributes']['total'] > 0) {
               
                     $status =  ' ' . $this->translate('has a new connection.'). ' ';?>
                <?php $userNoPhoto =  $this->layout()->staticBaseUrl. 'application/modules/User/externals/images/nophoto_user_thumb_icon.png'?>
                <?php $Screen_Name_connected = $Linkedin['update-content']['person']['connections']['person']['first-name'] . ' ' . $Linkedin['update-content']['person']['connections']['person']['last-name']; ?>
                <?php $connetionUserPhoto = isset($Linkedin['update-content']['person']['connections']['person']['picture-url']) ? $Linkedin['update-content']['person']['connections']['person']['picture-url'] : $userNoPhoto;?>
                <?php $body =  '<div class="feed_item_attachments"><span><div>
                                    <a href="'. $Linkedin['update-content']['person']['connections']['person']['site-standard-profile-request']['url']  . '" target="_blank"><img src="' . $connetionUserPhoto . '" alt="" />
                                      </a>'; 
               
                
                     $body .= '<div>              
                                  <div class="feed_item_link_title">
                                    <a href="'. $Linkedin['update-content']['person']['connections']['person']['site-standard-profile-request']['url']  . '" target="_blank">
                                    '. $Screen_Name_connected . '
                                    </a>
                                  </div>';                     
                     
                                  $body .= '<div class="feed_item_link_desc">' .  Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['connections']['person']['headline']) . '</div></div></div></span></div>';
                
                  ?>

                    <?php } else if (isset($Linkedin['update-content']['person']['current-status'])) {
                    
                            $body = '<div class="feed_item_bodytext">' . Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['current-status']) .'</div>';?>
                    
                  <?php }  else if ($Linkedin['update-type'] == 'PICU') {
                    
                            $status =   $this->translate(' has a new profile photo');?>
                    
                  <?php }  else if (isset($Linkedin['update-content']['person']['current-share'])) {
                           $content = '';
                           if(isset($Linkedin['update-content']['person']['current-share']['comment']) && !is_array($Linkedin['update-content']['person']['current-share']['comment']))
                            $content =  '<div class="feed_item_bodytext">' . Engine_Api::_()->advancedactivity()->getURLString(@$Linkedin['update-content']['person']['current-share']['comment']). '</div>';
                            
                            if (isset($Linkedin['update-content']['person']['current-share']['content'])) {
                            
													$content = $content . '<div class="feed_item_attachments"><span><div><a href="'. @$Linkedin['update-content']['person']['current-share']['content']['submitted-url'].'"><img src="'. @$Linkedin['update-content']['person']['current-share']['content']['submitted-image-url'] .'" alt="" /> </a>'. ' <div><div class="feed_item_link_title"><a href="'. @$Linkedin['update-content']['person']['current-share']['content']['submitted-url'].'">'. @$Linkedin['update-content']['person']['current-share']['content']['title'] . '</a></div><div class="feed_item_link_desc">';
													
													if (is_array($Linkedin['update-content']['person']['current-share']['content']['description']) )
													
													  $content = $content . Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['current-share']['content']['description'][0]) . '</div></div></div></span></div>';
													
													else
															 $content = $content . Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['current-share']['content']['description']) . '</div></div></div></span></div>';
                            
                            }
                            
                            $body =  $content;
                            
                  
                  
                      } else if (isset($Linkedin['update-content']['person']['member-groups'])) {
                    
                            $status =  ' ' . $this->translate('joined the group') .  ' ' ;?>
                            
                             <?php $status .= '<a href="' . $Linkedin['update-content']['person']['member-groups']['member-group']['site-group-request']['url'] . '" target="_blank" class="feed_item_username">' . $Linkedin['update-content']['person']['member-groups']['member-group']['name'] . '</a>'; ?>
                             
                 <?php  }  else if (isset($Linkedin['update-content']['person']['skills'], $Linkedin['update-content']['person']['skills']['skill']) && count ($Linkedin['update-content']['person']['skills']['skill']) > 0 ) {
                    
                            $status =   ' ' . $this->translate('has added skills:') .  ' ' ; ?>
                            
                      <?php $count = count($Linkedin['update-content']['person']['skills']['skill']);
                         if ($count == 1) :?>
                           <?php $body = '<a href="' . $Linkedin['update-content']['person']['site-standard-profile-request']['url'] . '" target="_blank" >' . $Linkedin['update-content']['person']['skills']['skill']['skill']['name'] . '</a>' ?>
                        <?php else:    
                           
                              foreach ($Linkedin['update-content']['person']['skills']['skill'] as $key => $skill) : ?>
                            
                                  <?php $body .= '<a href="' . $Linkedin['update-content']['person']['site-standard-profile-request']['url'] . '" target="_blank" >' . $skill['skill']['name'] . '</a>' ?>                                  
												
												<?php if (($count - $key) > 1 && $key < 2) { 
																	$body .=  ", ";
                             }
														 if ($key > 2){
												       $body .=  " and <a href='".  $Linkedin['update-content']['person']['site-standard-profile-request']['url'] . "' target='_blank'  >". (int)(count($Linkedin['update-content']['person']['skills']['skill']) - 3) . " " . $this->translate('more') . "</a>";
																break;
															} ?>
                 <?php endforeach; endif; 
                    
                     
												
                 
                 
                  } else if (isset($Linkedin['update-content']['person']['positions'], $Linkedin['update-content']['person']['positions']['position'])) {
                    
         $status =  ' ' . $this->translate('has an updated current title:') .  ' ';
         
          $body = $Linkedin['update-content']['person']['positions']['position']['title'] . ' at ' . $Linkedin['update-content']['person']['positions']['position']['company']['name'];?>
                    
						<?php } else if (isset($Linkedin['update-content']['person']['recommendations-given'], $Linkedin['update-content']['person']['recommendations-given']['recommendation'])) {
                    
           $status =   ' ' . $this->translate('recommends') .  ' ';?>
          
                
                
                
                
                
                <?php $userNoPhoto =  $this->layout()->staticBaseUrl. 'application/modules/User/externals/images/nophoto_user_thumb_icon.png'?>
                <?php $Screen_Name_connected = $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['first-name'] . ' ' . $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['last-name']; ?>
                <?php $connetionUserPhoto = isset($Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['picture-url']) ? $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['picture-url'] : $userNoPhoto ;?>
                <?php $body =  "<div class='feed_item_photo'>
                                  <a href='" . $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['site-standard-profile-request']['url']  . "' target='_blank'>
                                  <img src='" . $connetionUserPhoto . "' alt='' class='thumb_icon' /> 
                                  </a>  
                                </div>";            
                
                     $body .= '<div class="feed_item_status">              
                                  <div class="feed_item_posted">
                                    <a href="'. $Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['site-standard-profile-request']['url']  . '" target="_blank">
                                    '. $Screen_Name_connected . '
                                    </a>  
                                  </div> 
                               </div>';                     
                     
                     $body .= '<div class="feed_item_body">              
                                  <div class="feed_item_posted">' .  Engine_Api::_()->advancedactivity()->getURLString($Linkedin['update-content']['person']['recommendations-given']['recommendation']['recommendee']['headline']) . '
                                   </div> 
                               </div>';
                
                  ?>            
						<?php }
						
						
						else if (isset($Linkedin['update-content']['person']['member-url-resources'], $Linkedin['update-content']['person']['member-url-resources']['member-url-resource']) && !empty($Linkedin['update-content']['person']['member-url-resources']['member-url-resource']['url'])) {
                    
         $status =   ' ' . $this->translate('has added new profile links:');
         
         $body = '<a href="'.$Linkedin['update-content']['person']['member-url-resources']['member-url-resource']['url'] . '" target="_blank">' . ucfirst($Linkedin['update-content']['person']['member-url-resources']['member-url-resource']['member-url-resource-type']['code']) . '</a>';?>
                    
						<?php }
						
						else if (isset($Linkedin['updated-fields'], $Linkedin['updated-fields']['update-field'])) { 
						           $fields_string = '';
						           if ($Linkedin['updated-fields']['@attributes']['count'] == 1) {
						             $fields_string = $fields_string . ucfirst(str_replace('person/', '', $Linkedin['updated-fields']['update-field']['name']));
												
						           }
						           else {
													foreach ($Linkedin['updated-fields']['update-field'] as $key => $field) {
															$fields_string = $fields_string . ucfirst(str_replace('person/', '', $field['name'])) . ', ';													}
													 $fields_string = rtrim($fields_string, ", ");
						           }
							
								$status =   ' ' .  $this->translate('has an updated profile') ;
                $body = $fields_string;?>
							
						<?php } else { 
                         
                           $status =  ' ' .  $this->translate('is now your connection.');
                  
                  }?>      
                
                
                
                
                
                
                
        
        <li id="activity-item-<?php echo $Linkedin['timestamp']?>-linkedin">
          
          
          <div id="main-feed-<?php echo $Linkedin['timestamp']?>-linkedin">
                <div class="feed_item_header">
                  <!--<div class="feed_items_options_btn">        
                    <a href="javascript:void(0);" onclick="sm4.activity.showOptions('<?php echo $Linkedin['timestamp']?>')" data-role="button" data-icon="cog" data-iconpos="notext" data-theme="c" data-inline="true"></a>
                  </div>-->
                  
                  
                    <div class='feed_item_photo'>
                        <a href= "<?php echo $Linkedin['update-content']['person']['site-standard-profile-request']['url'];?>" target="_blank" title="<?php echo $Linkedin['update-content']['person']['headline'];?>">

                          <img src="<?php echo isset($Linkedin['update-content']['person']['picture-url']) ? $Linkedin['update-content']['person']['picture-url'] : $this->layout()->staticBaseUrl. 'application/modules/User/externals/images/nophoto_user_thumb_icon.png' ;?>" alt="" class="thumb_icon" /> 
                        </a>  
                    </div>
                    <div class="feed_item_status">
                      <?php // Main Content  ?>
                      <div class="feed_item_posted">
                            <?php if (!isset($Linkedin['update-content']['person']['person-activities']['activity'])) { ?>
                              <a href= "<?php echo $Linkedin['update-content']['person']['site-standard-profile-request']['url']; ?>" target="_blank" title="<?php echo $Linkedin['update-content']['person']['headline']; ?>" class="feed_item_username">  
                                <?php echo $Screen_Name_connecter; ?>
                              </a>
                                
                              <?php
                            } else {

                              echo nl2br($Linkedin['update-content']['person']['person-activities']['activity']['body']);
                            }
                            ?>
                                  
                            <?php echo $status; ?>           
                              
                       </div>  
                    </div>
                    
                </div>
                  
            <div class='feed_item_body'>
              
              <div class='feed_item_attachments_wapper' style="width: 100%; position: relative;">
                <div class='feed_item_attachments' >
                  
                 <?php echo $body;?>
                  
                </div>
                
              </div>
            </div>
            <?php //SHOWING THE NO OF LIKES.............//
                $like_comment = 0;          
							//if (isset($Linkedin['is-likable']) && $Linkedin['is-likable'] === 'true') : 
								//CHECK IF THE CURRENT USER HAS LIKED OR NOT
								if (isset($Linkedin['is-liked']) && $Linkedin['is-liked'] === 'true'):
										$current_user_like = 1;
										$Linkedin_action = 'unlike';
										$like_unlike = $this->translate('Unlike');
										$like_unlike_title = $this->translate('Click to unlike this update');
								
								else :
										$current_user_like = 0;
										$Linkedin_action = 'like';
										$like_unlike = $this->translate('Like');
										$like_unlike_title = $this->translate('Clike to like this update');
								endif;		
			       
			       
								if (isset($Linkedin['num-likes']) && !empty($Linkedin['num-likes'])) :
										$post_count = $Linkedin['num-likes'];
								else:
										$post_count = 0;
								endif;   
  				      
  				      ?>
            
            <div class="feed_item_btm">
                  <span class="feed_item_date">
                    <?php echo $this->timestamp($Linkedin['timestamp']/1000) ?>
                  </span>
                  <?php if (isset($Linkedin['num-likes']) && !empty($Linkedin['num-likes'])):?>
                    <span class="sep">-</span>
                    <a href="javascript:void(0);"  class="feed_likes" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'view-linkedin-comments', 'post_id' => $Linkedin['update-key'], 'like_count' => $Linkedin['num-likes'], 'timestamp' => $Linkedin['timestamp']), 'default', 'true'); ?>", "feedsharepopup")'>
                      
                      <span><?php echo $this->translate(array('%s like', '%s likes', $Linkedin['num-likes']), $this->locale()->toNumber($Linkedin['num-likes'])); ?></span>
                    </a>	
                    <?php if (!empty($Linkedin['update-comments']) && !empty($Linkedin['update-comments']['@attributes']['total'])) : echo '<span class="sep">-</span>' ?> 
                      <a href="javascript:void(0);"  class="feed_comments" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'view-linkedin-comments', 'post_id' => $Linkedin['update-key'], 'like_count' => $Linkedin['num-likes'], 'timestamp' => $Linkedin['timestamp']), 'default', 'true'); ?>", "feedsharepopup")'>
                        
                        <span><?php echo $this->translate(array('%s comment', '%s comments', $Linkedin['update-comments']['@attributes']['total']), $this->locale()->toNumber($Linkedin['update-comments']['@attributes']['total']));
            endif; ?></span>
                    </a>
                  <?php elseif (!empty($Linkedin['update-comments']) && !empty($Linkedin['update-comments']['@attributes']['total'])) : ?>
                    <span class="sep">-</span>
                    <a href="javascript:void(0);" class="feed_comments" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'view-linkedin-comments', 'post_id' => $Linkedin['update-key'], 'like_count' => $Linkedin['num-likes'], 'timestamp' => $Linkedin['timestamp']), 'default', 'true'); ?>", "feedsharepopup")'>
                      
                      <span><?php echo $this->translate(array('%s comment', '%s comments', $Linkedin['update-comments']['@attributes']['total']), $this->locale()->toNumber($Linkedin['update-comments']['@attributes']['total'])); ?></span>
                    </a>
                  <?php endif; ?>
              </div>
            
             <div class="feed_item_option">
            <?php if (isset($Linkedin['is-likable']) && $Linkedin['is-likable'] === 'true'  || ($Linkedin['update-type'] != 'STAT' && $Linkedin['update-content']['person']['id'] != $this->currentuser_id)): ?>          
              <div data-role="navbar" data-inset="false">
                <ul>
                  <?php if (isset($Linkedin['is-likable']) && $Linkedin['is-likable'] === 'true'):
                      $front = Zend_Controller_Front::getInstance();
                    
                    ?>
                    <li>
                     <?php if ($like_unlike == 'Like') : ?>
                         <a href="javascript:void(0);" onclick="javascript:sm4.activity.like('<?php echo $Linkedin['timestamp'] ?>-linkedin', '', $(this));" data-url="<?php echo (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $front->getRequest()->getBaseUrl();?>/widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed?format=json&is_ajax=3&post_id=<?php echo  $Linkedin['update-key'] ?>" data-message="<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'get-linkedin-feed-likes', 'linkedin_id' => $Linkedin['update-key']), 'default', 'true'); ?>">
                          <i class="ui-icon ui-icon-thumbs-up"></i>
                  
                      <?php else : ?>
                             <a href="javascript:void(0);" onclick="javascript:sm4.activity.unlike('<?php echo $Linkedin['timestamp'] ?>-linkedin', '' , $(this));" data-url="<?php echo (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $front->getRequest()->getBaseUrl();?>/widget/index/mod/advancedactivity/name/advancedactivitylinkedin-userfeed?format=json&is_ajax=3&post_id=<?php echo  $Linkedin['update-key'] ?>" data-message="<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'get-linkedin-feed-likes', 'linkedin_id' => $Linkedin['update-key']), 'default', 'true'); ?>" >
                          <i class="ui-icon ui-icon-thumbs-down"></i>
                       <?php endif;?>                     
                          <span><?php echo $like_unlike ?></span>
                        </a>
                      </li>
                      <li>
                         <a href="javascript:void(0);" class="feed_likes" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'view-linkedin-comments', 'post_id' => $Linkedin['update-key'], 'like_count' => $Linkedin['num-likes'], 'timestamp' => $Linkedin['timestamp']), 'default', 'true'); ?>", "feedsharepopup")'>
                          <i class="ui-icon ui-icon-comment"></i>
                          <span><?php echo $this->translate('Comment'); ?></span>
                        </a>
                      </li>
                  <?php endif; ?>

                  <?php // Send Message  ?>
                  <?php if ($Linkedin['update-type'] != 'STAT' && $Linkedin['update-content']['person']['id'] != $this->currentuser_id): ?>
                    <li>
                        <a href="javascript:void(0);" onclick='sm4.activity.openPopup("<?php echo $this->url(array('module' => 'advancedactivity', 'controller' => 'socialfeed', 'action' => 'send-linkedin-message', 'to' => $Screen_Name_connecter, 'title' => $this->translate('Your new connection:') . ' ' . $Screen_Name_connected, 'memberid' => $Linkedin['update-content']['person']['id']), 'default', 'true'); ?>", "feedsharepopup")' class="feed_likes" >
                          <i class="ui-icon ui-icon-share-alt"></i>
                          <span><?php echo $this->translate('Send a message'); ?></span>
                        </a>
                      </li>
                  <?php endif; ?>
                </ul>
              </div>
            <?php endif; ?>
          </div> 
          </div>  
          
        </li>  
   
						
									
							
							<?php
							if (!isset($this->LinkedinFeeds['update'][0])):
							   break; endif; ?>
						<?php endforeach; ?>
     <?php if (empty($this->isajax) && empty($this->checkUpdate) && empty($this->getUpdate)) : ?>
     </ul>
     
         <div class="feed_viewmore" id="feed_viewmore-linkedinfeed" style="display: none;">
<?php
echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
    'id' => 'feed_viewmore_link-linkedinfeed',
    'class' => 'ui-btn-default icon_viewmore'
))
?>
  </div>

  <div class="feeds_loading" id="feed_loading-linkedinfeed" style="display: none;">
    <i class="ui-icon-spinner ui-icon icon-spin"></i>
  </div>

  <div class="feeds_no_more tip" id="feed_no_more-linkedinfeed" style="display: <?php echo ($this->allParams['endOfFeed']) ? 'block' : 'none' ?>;">
    <span>
<?php echo $this->translate("There are no more posts to show.") ?>
    </span>  
  </div>
     <?php endif;?>
 <script type="text/javascript"> 
      <?php if (empty($this->getUpdate)):?>

  //update_freq_fb = <?php //echo Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.update.frequency', 120000); ?>;
  sm4.core.runonce.add(function() {  
    sm4.activity.makeFeedOptions('linkedinfeed', <?php echo json_encode($this->allParams);?>, <?php echo json_encode($this->attachmentsURL);?>); 
    sm4.socialactivity.setUpdateData('<?php echo $current_linkedin_timestemp;?>', 'linkedinfeed');
  }); 

  <?php elseif (!empty($this->getUpdate)) :?>

    sm4.core.runonce.add(function() { 
      sm4.socialactivity.setUpdateData('<?php echo $current_linkedin_timestemp;?>', 'linkedinfeed');
    });
 
   <?php endif;?>
     </script> 
  <?php else: ?>
    <?php if (!empty($this->LinkedinLoginURL) && empty($this->isajax)) { 
        $execute_script = 0;
      
      ?>
      <div class="aaf_feed_tip"><?php echo $this->translate('LinkedIn is currently experiencing technical issues, please try again later.');?></div>
      
    <?php } else { ?>

            
     <?php } ?>       
     
  <?php 
  
  
  endif;?> 

<?php if (empty($this->isajax) && empty($this->checkUpdate) && empty ($this->getUpdate) && empty($this->tabaction)) : ?>

</div> 
<?php endif; ?>
