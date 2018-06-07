<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitepagetwitter
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminSettingscontroller.php 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php //if ($this->widgetTitle):?>
	<!--<h3><?php //echo $this->widgetTitle;?></h3>-->
<?php //endif; ?>

<div class="page_fb_feed_mainbox">
	<?php if (!empty($this->loginUrl )) { 
			echo '<div class="white">' . $this->translate('You need to be logged into Facebook to see your Facebook News Feed.') . ' <a href="javascript:void(0);" onclick= "AAF_ShowFeedDialogue_FB(\'' . $this->loginUrl . '\')" >' . $this->translate('Click here') . '</a>.</div>';?>
			<script type='text/javascript'>
			action_logout_taken_fb = 1;
			
			</script>
									
	<?php   } else { 	?>
	<?php $picture_pageid = explode("_", $this->data[0]['id']);  ?>
		<div class="page_fb_feed_box_top">
	  	<div class="page_fb_page_photo">
	      <a href="http://www.facebook.com/profile.php?id=<?php echo $picture_pageid[0];?>" target="_blank">
	        <img src="http://graph.facebook.com/<?php echo $picture_pageid[0];?>/picture" alt="" />
	      </a>
	    </div>
	    <div class="page_fb_page_info">
	    	<div class="page_fb_page_name">
	    		Page Title
	    	</div>  
	      <?php if ($this->showfblikebutton) :?>
	       	<div class="page_fb_page_like_button">
	        	<iframe src="//www.facebook.com/plugins/like.php?href=<?php echo $this->fbPageUrl;?> &amp;send=false&amp;layout=standard&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=80&amp;appId=250456038386748" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:45   0px; height:30px;" allowTransparency="true" class="fb_ltr"></iframe>
	         </div>   
	  		<?php endif;?>
	  	</div>
	  </div>  
	  
  	<div class="page_fb_feed_box">
			<?php if (!empty($this->loggeduser_fbpagefeed) && count($this->loggeduser_fbpagefeed['data'])) { ?> 
	    <ul class="page_fb_feeds">
				<?php foreach ($this->data as $key => $feed) {  
				 $actions = explode("_", $feed['id']); 
				 if (empty($actions[0]) && !empty($actions[1]))
				    continue; 
				 ?>
					<li>
				    <div class="feed_item_photo">
				      <a href="http://www.facebook.com/profile.php?id=<?php echo $feed['from']['id'];?>" target="_blank">
				        <img src="http://graph.facebook.com/<?php echo $feed['from']['id'];?>/picture" alt="" />
				      </a>
				    </div>
				    <div class="feed_item_body">
				    	<div class="feed_item_generated seaocore_txt_light">
				    	<?php //IF TYPE IS QUESTION TYPE THEN WE WILL NOT SHOW THE OPTION FOR LIKE AND COMMENT.
				         $story = ''; 
				         if (($feed['type'] == 'question' || @$feed['application']['name'] == 'Questions' || $feed['type'] == 'status' ||  $feed['type'] == 'photo') && !empty($feed['story']))           {         
				            if ($feed['type'] == 'question' || @$feed['application']['name'] == 'Questions') {
				              $story_content = explode('asked: ', $feed['story']);
				              if (isset($story_content[1]) && !empty($story_content[1])) {
				                $story .= '<a href="http://www.facebook.com/profile.php?id=' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .   	  
				  	       	             $feed['from']['name'] . '</a> asked: ' . '<a href="http://www.facebook.com/questions/' . $feed['object_id']. '" target="_blank" class="feed_item_username">' . $story_content[1] . '</a>';
				              }
				            }
					       	   else {  
					       	     
				               $substring_story = '<a href="http://www.facebook.com/profile.php?id=' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .   	  
					       	             $feed['from']['name'] . '</a>';
				               $story = str_replace($feed['from']['name'], $substring_story, $feed['story']); 
					       	   }
				        }
					      else {
					        if ($feed['type'] != 'photo' || (($feed['type'] == 'photo') && empty($feed['story'])) ) { 
					         $story .= '<a href="http://www.facebook.com/profile.php?id=' . $feed['from']['id'] . '" target="_blank" class="feed_item_username">' .   	  
					       	   $feed['from']['name'] . '</a>';
					        }             
					      }
					      if (empty($story) && !empty($feed['story']) && !empty($feed['message'])) {
					        $story = $feed['story'];
					      }
					      echo $story;?>
				        <?php if (empty($feed['message']) && !empty($feed['story']) ) { 
				           
				           if ($feed['type'] == 'link')
				            echo 'shared <a href="' . $feed['link']. '" target="_blank"> a link. </a>';
				         } ?>
					            
					      <?php if (!empty($feed['to']) && empty($feed['message_tags'])) : ?>
					      	<img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Facebooksepage/externals/images/arrow-right.png" alt="" /><?php echo ' <a href="http://www.facebook.com/profile.php?id=' . $feed['to']['data'][0]['id']  . '" target="_blank" class="feed_item_username">' . $feed['to']['data'][0]['name']  . '</a>'; ?>
					      <?php endif; ?>
					    </div> 
				      
				      <?php $id_text = 0;
				
				      if (!empty($feed['message'])) {?>
				      	<div class="feed_item_body_txt">
				          <?php $Message_Length = strlen($feed['message']);
				          
				          if ($Message_Length > 200) {
				             $message = substr($feed['message'], 0, 200);
				             $id_text = $actions[1] . '_' . $key;
				             $message = $message . '... <a href="javascript:void(0);" onclick="FBNewsFeed_showText_More(1, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") .'</a>';
				          }else {
				            $message = $feed['message'];
				          } ?>
				         
				          <p id="fbnewsfeed_message_text_short_<?php echo $id_text;?>">
				           <?php  
				            $message = Engine_Api::_()->facebooksepage()->getURLString($message);
				            
				            //CHECK IF A CONTENT IS TAGED IN THE MESSAGE OR NOT.
				            
				            if (!empty($feed['message_tags']) && !empty($feed['to'])) :
				             //FIND THE FIRST OCCURANCE OF THE TAGED CONTENT
				             foreach ($feed['to']['data'] as $to) :
				             
				              $substring = '<a href="http://www.facebook.com/profile.php?id=' . $to['id']  . '" target="_blank" class="feed_item_username">' . $to['name']  . '</a>'; 
				              $message = str_replace($to['name'], $substring, $message);
				            endforeach; 
				           endif;       
				            echo nl2br($message);?>
				          </p>
				          <div id="fbnewsfeed_message_text_full_<?php echo $id_text;?>" style="display:none;">
				            <?php $feed['message'] = Engine_Api::_()->facebooksepage()->getURLString($feed['message']);
				            //CHECK IF A CONTENT IS TAGED IN THE MESSAGE OR NOT.
				            
				            if (!empty($feed['message_tags']) && !empty($feed['to'])) :
				             //FIND THE FIRST OCCURANCE OF THE TAGED CONTENT
				             foreach ($feed['to']['data'] as $to) :
				             
				              $substring = '<a href="http://www.facebook.com/profile.php?id=' . $to['id']  . '" target="_blank" class="feed_item_username">' . $to['name']  . '</a>'; 
				              $feed['message'] = str_replace($to['name'], $substring, $feed['message']);
				            endforeach; 
				           endif;
				           
				           echo nl2br($feed['message']);?>
				          </div>
				      	</div>
				      <?php } ?>
				      
				       <?php if (!empty($feed['picture']) || !empty($feed['name']) || !empty($feed['caption']) || !empty($feed['description']) || !empty($feed['properties'])) : ?>
				        <div class="feed_item_attachments">
				        	<span class="feed_attachment_facebook">
				        		<div>
						          <?php if (!empty($feed['picture']) && !empty($feed['link'])) : ?>
						          	<a href="<?php echo $feed['link'];?>" target="_blank" <?php if ($feed['type'] == 'photo'):?> class="feed_attachment_facebook_photo" <?php endif;?> ><img src="<?php echo $feed['picture'];?>" alt="" /></a> 
						          <?php endif; ?>
						          <div>   	     
								        <?php if (!empty($feed['name']) && !empty($feed['link'])) : ?>
								        	<div class="feed_item_link_title">
								            <a href="<?php echo $feed['link'];?>" target="_blank" class="title"><?php echo $feed['name'];?></a> 
								          </div>  
								        <?php endif; ?>
								        <div class="feed_item_link_desc">
									        <?php if (!empty($feed['caption'])) : ?>
									        	<span><?php 
									        	$feed['caption'] = Engine_Api::_()->facebooksepage()->getURLString($feed['caption']);
									        	  $caption_Length = strlen($feed['caption']);
							                if ($caption_Length > 200) {
							                   $caption = substr($feed['caption'], 0, 200);
							                   $id_text = 'caption_' .$actions[1] . '_' . $key;
							                   $caption = $caption . '... <a href="javascript:void(0);" onclick="FBNewsFeed_showText_More(2, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") .'</a>';
							                }else {
							                  $caption = $feed['caption'];
							              } ?> 
						                <div id="fbnewsfeed_descript_text_short_<?php echo   $id_text;?>">
						                  <?php 
						                  $caption = Engine_Api::_()->facebooksepage()->getURLString($caption);
						                  echo nl2br($caption);?>
						                </div>
						                <div id="fbnewsfeed_descript_text_full_<?php echo   $id_text;?>" style="display:none;">
						                  <?php 
						                  $feed['caption'] = Engine_Api::_()->facebooksepage()->getURLString($feed['caption']);
						                  echo nl2br($feed['caption']);?>
						                </div>       	
									        </span> 
									        <?php endif; ?>
									        <?php if (!empty($feed['description'])) : 
							                $Description_Length = strlen($feed['description']);
							                if ($Description_Length > 200) {
							                   $description = substr($feed['description'], 0, 200);
							                   $id_text = $actions[1] . '_' . $key;
							                   $description = $description . '... <a href="javascript:void(0);" onclick="FBNewsFeed_showText_More(2, \'' . $id_text . '\');" class="facebooksepage_seemore">' . $this->translate("See More") .'</a>';
							                }else {
							                  $description = $feed['description'];
							              } ?> 
						                <div id="fbnewsfeed_descript_text_short_<?php echo   $id_text;?>">
						                  <?php 
						                  $description = Engine_Api::_()->facebooksepage()->getURLString($description);
						                  echo nl2br($description);?>
						                </div>
						                <div id="fbnewsfeed_descript_text_full_<?php echo   $id_text;?>" style="display:none;">
						                  <?php 
						                  $feed['description'] = Engine_Api::_()->facebooksepage()->getURLString($feed['description']);
						                  echo nl2br($feed['description']);?>
						                </div>
					                <?php endif; ?>
					               		<?php if (!empty($feed['properties']) && isset($feed['properties'][0]) &&  isset($feed['properties'][0]['name'])) : ?>
					               	  
					         					<?php  if (!empty($feed['properties'][0]['href'])) 
					         					         echo '<br /> &#160 ' . $feed['properties'][0]['name'] .  ' :<a href="' . $feed['properties'][0]['href'] . '" target="_blank"> ' .  $feed['properties'][0]['text'] . '</a><br />';
					         					       else 
					         					           echo '<br /> &#160 ' . $feed['properties'][0]['name'] .  ' : ' .  $feed['properties'][0]['text'] . '<br />'; 
					         					         
					         					         ?>
					        				<?php endif; ?>
					        			</div>	
					            </div>
					        	</div>
				        	</span>
				        </div>
				        <?php endif; ?>
				        
				        <div class="feed_item_stats">
				        	<?php 
				           $post_url = "http://www.facebook.com/" . $actions[0] . "/posts/" . $actions[1] ;
				            $feed_icon = '';
				            if (!empty($feed['icon'])) {
				              $feed_icon = '<i style="background-image:url(' . @$feed['icon'] . ');"></i>';
				            }   
				          ?>
				           
				           <?php echo $feed_icon.	'<div class="seaocore_txt_light feed_item_time"><a href="http://www.facebook.com/'.  $actions[0] .'/posts/'. $actions[1] . '" target="_blank" class="timesep">' .	$this->timestamp(strtotime($feed['created_time'])) . '</a></div>';  ?>
				
								</div>			
				      </div>	
					 </li>

     
<?php } echo '</ul>';
 }
 else {
   echo "<div class='tip'><span>There are no feeds to show.</span></div>";
   
 }

 echo '</div>';
} ?>

</div>
<script type="text/javascript">

if (window.opener!= null) { 
<?php if (isset($_GET['redirect_page_fbpage'])) : ?>
    
  window.opener.location.reload();
   close();
 
 <?php endif;?>
 }
 
 </script>