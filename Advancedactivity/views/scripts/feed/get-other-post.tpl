<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: get-other-post.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<?php if(isset($this->paginator->getCurrentPageNumber())):?>
<?php //if(!empty ($this->showViewMore)): ?>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
    hideViewMoreLink();
    });
    function getNextPageViewMoreResults(){
        return <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
    }
    function hideViewMoreLink(){
        if(document.getElementById('mutual_friend_pops_view_more'))
            document.getElementById('mutual_friend_pops_view_more').style.display = '<?php echo ( $this->paginator->count() ==
							$this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>';
    }

    function viewMoreTabMutualFriend()
    {
    var object_id = '<?php echo $this->object_id; ?>';
    document.getElementById('mutual_friend_pops_view_more').style.display ='none';
    document.getElementById('mutual_friends_pops_loding_image').style.display ='';
    en4.core.request.send(new Request.HTML({
        method : 'post',
        'url' : en4.core.baseUrl + 'advancedactivity/feed/get-other-post/id/' + object_id,
        'data' : {
            format : 'html',
            showViewMore : 1,
            page: getNextPageViewMoreResults()
        },
        onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {

            document.getElementById('mutual_results_friend').innerHTML =
						document.getElementById('mutual_results_friend').innerHTML + responseHTML;
            document.getElementById('mutual_friend_pops_view_more').destroy();
            document.getElementById('mutual_friends_pops_loding_image').style.display ='none';
        }
    }));

    return false;

  }
  </script>
<?php endif; ?>
<?php $includeFirstObject = false; ?>
<?php if (empty($this->showViewMore)): ?>
  <div class="seaocore_members_popup seaocore_members_popup_notbs">
    <div class="top">
      <div class="heading"><?php echo $this->translate('Members')?>
      <?php //echo $this->result->getTitle();

        //$this->htmlLink($this->result->getHref(), $this->result->getTitle(), array('title'=>
//$this->result->getTitle(), 'target' => '_blank'));?>
      </div>
    </div>
    <div class="seaocore_members_popup_content" id="mutual_results_friend">
       <?php $includeFirstObject = true; ?>
<?php endif; ?>

<?php
foreach( $this->paginator as $value ):
if ($includeFirstObject) { $includeFirstObject = false;  continue;
}
	$user_subject = Engine_Api::_()->getItem($value->subject_type, $value->subject_id);
  $profile_url = $this->url(array('id' => $value['subject_id']), 'user_profile');
?>

  <div class="item_member_list" id="more_results_shows">
    <div class="item_member_thumb">
      <a href="<?php echo $profile_url ?>"  target="_blank"> <?php echo
$this->itemPhoto($this->user($user_subject), 'thumb.icon') ?></a>
    </div>
    <div class="item_member_details">
      <div class="item_member_name">
        <a href="<?php echo $profile_url ?>" target="_blank"><?php echo
					$this->user($user_subject)->getTitle()?></a>
      </div>
    </div>
  </div>

<?php endforeach;?>

<?php if (empty($this->showViewMore)): ?>
<div class="" id="mutual_friend_pops_view_more" onclick="viewMoreTabMutualFriend()">
    <?php
    echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
            'id' => 'feed_viewmore_link',
            'class' => 'buttonlink icon_viewmore'
    ))
    ?>
</div>
<div class="" id="mutual_friends_pops_loding_image" style="display: none;">
  <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' style='margin-right: 5px;' />
    <?php echo $this->translate("Loading ...") ?>
</div>

<?php //if (empty($this->showViewMore)): ?>
    </div>
  </div>

  <div class="seaocore_members_popup_bottom">
      <button  onclick='javascript:parent.Smoothbox.close()' ><?php echo $this->translate('Close') ?></button>
  </div>
<?php endif; ?>
