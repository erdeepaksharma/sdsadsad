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
	<?php foreach ($this->paginator as $sitereview_video): ?>
    <?php  $this->partial()->setObjectKey('sitereview_video');
        echo $this->partial('application/modules/Sitereview/views/scripts/partialWidget.tpl', $sitereview_video);
		?>		    
					<?php
          $sitereview->listing_title  = Engine_Api::_()->getItem('sitereview_listing', $sitereview_video->listing_id);
					$truncation_limit = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.title.truncation', 18);
					$tmpBody = strip_tags($sitereview->listing_title);
					$listing_title = ( Engine_String::strlen($tmpBody) > $truncation_limit ? Engine_String::substr($tmpBody, 0, $truncation_limit) . '..' : $tmpBody );
          ?>
          <?php echo $this->translate("in ") . $this->htmlLink($sitereview_video->getHref(), $listing_title, array('title' => $sitereview->listing_title)) ?>    
          </div>
					<div class="seaocore_sidebar_list_details clr"> 
					<?php echo $this->translate(array('%s like', '%s likes', $sitereview_video->like_count), $this->locale()->toNumber($sitereview_video->like_count)) ?>,
					<?php echo $this->translate(array('%s view', '%s views', $sitereview_video->view_count), $this->locale()->toNumber($sitereview_video->view_count)) ?>
				</div>
			</div>
		</li>
	<?php endforeach; ?>
</ul>