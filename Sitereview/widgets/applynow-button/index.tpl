<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php if(!empty($this->widgetTitle)):?>
  <?php $title = $this->translate($this->widgetTitle);?>
<?php else:?>
  <?php $title = $this->translate('Apply Now');?>
<?php endif;?>

<?php if(!empty($this->viewer_id)):?>
  <div class="layout_sitereview_review_button">
    <button class="sr_review_button" onclick="applyNow('<?php  echo $this->url(array('action' => 'applynow', 'listing_id' => $this->listing_id), "sitereview_specific_listtype_$this->listingtype_id"); ?>', 0);"><?php echo $title ?></button>
  </div>
<?php else:?>
  <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemenu')):?>
    <div class="layout_sitereview_review_button">
      <button class="sr_review_button" onclick="advancedMenuUserLoginOrSignUp('login', '', '');"><?php echo $title ?></button>
    </div>
  <?php else:?>
    <?php 
      $urlO = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
      $request_url = explode('/',$urlO);
      empty($request_url['2']) ? $param = 2 : $param = 1;
      $return_url = (!empty($_ENV["HTTPS"]) && 'on' == strtolower($_ENV["HTTPS"])) ? "https://":"http://";
      $currentUrl = urlencode($urlO);
    ?> 
    <?php
    $addUrl = $this->url(array('action' => 'applynow', 'listing_id' => $this->listing_id, 'param' => $param,'request_url' => $request_url['1']), 'sitereview_specific_listtype_' . $this->listingtype_id)."?"."return_url=".$return_url.$_SERVER['HTTP_HOST'].$currentUrl;
    ?>
    <div class="layout_sitereview_review_button">
      <button class="sr_review_button" onclick="applyNow('<?php echo $addUrl;?>', '1');"><?php echo $title ?></button>
    </div>
  <?php endif;?>
<?php endif;?>

<script type="text/javascript">
  function applyNow(url, type){
   if(type == 1)
   window.location.href = url;
   else
   Smoothbox.open(url);
  }
 </script>





  


