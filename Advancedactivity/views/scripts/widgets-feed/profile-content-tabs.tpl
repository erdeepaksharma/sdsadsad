<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: profile-content-tabs.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php

    $allowedModules = Engine_Api::_()->getApi('settings','core')->getSetting('aaf.allowed.buysell.content', array('user'));
    $coreApi = Engine_Api::_()->core();
    if($coreApi->hasSubject()){
        $type = ($coreApi->getSubject()->getType() == 'sitereview_listing') ? 'sitereview_listingtype_' . $coreApi->getSubject()->listingtype_id : $coreApi->getSubject()->getType();
    }else{
        $type = 'user';
    }
    if(!in_array($type,$allowedModules)){
      return;
    }
?>
<script type="text/javascript">
  var tabProfileContainerSwitch = function(element, force) {
    if (en4.core.request.isRequestActive() && !force)
      return;
    if (element.tagName.toLowerCase() == 'a') {
      element = element.getParent('li');
    }
    var myContainer = element.getParent('.aaf_tabs_feed').getParent();
    myContainer.getElements('ul > li').removeClass('aaf_tab_active');
    element.get('class').split(' ').each(function(className) {
      className = className.trim();
      if (className.match(/^tab_[0-9]+$/)) {
        element.addClass('aaf_tab_active');

      }
    });
  }
  var activeAAFAllTAb = function() {
    if ($('update_advfeed_blink'))
      $('update_advfeed_blink').style.display = 'none';
    var element = $('tab_advFeed_everyone');
    if (element.tagName.toLowerCase() == 'a') {
      element = element.getParent('li');
    }

    var myContainer = element.getParent('.aaf_tabs_feed').getParent();

    //  myContainer.getChildren('div:not(.tabs_alt)').setStyle('display', 'none');
    myContainer.getElements('ul > li').removeClass('aaf_tab_active');
    element.get('class').split(' ').each(function(className) {
      className = className.trim();
      if (className.match(/^tab_[0-9]+$/)) {
        //    myContainer.getChildren('div.' + className).setStyle('display', null);
        element.addClass('aaf_tab_active');
      }
    });
  }
</script>
<?php $authorizationApi = Engine_Api::_()->authorization(); ?>
<div class="aaf_tabs_feed">
  <div class="aaf_tabs_loader" style="display: none;" id="aaf_tabs_loader">
    <img alt="Loading" src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif" align="left" />
  </div>
  <ul class="aaf_tabs_apps_feed">
    <li class="tab_1"> 	
      <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
    getTabBaseContentFeed('owner', '0');
    $('feed-update').empty();
    $('feed-update').style.display = 'none';" title="<?php echo $this->subject()->getTitle() ?>">
        <?php
        if (($this->subject()->getType() === 'user') || ($this->subject()->getType() === 'sitepage_page' && Engine_Api::_()->sitepage()->isFeedTypePageEnable()) || ($this->subject()->getType() === 'sitebusiness_business' && Engine_Api::_()->sitebusiness()->isFeedTypeBusinessEnable() || ($this->subject()->getType() === 'sitegroup_group' && Engine_Api::_()->sitegroup()->isFeedTypeGroupEnable()) || ($this->subject()->getType() === 'sitestore_store' && Engine_Api::_()->sitestore()->isFeedTypeStoreEnable()))
        ):
          echo $this->subject()->getTitle();
        else:
//           echo ($this->subject()->getType() === 'siteevent_event' ? $this->translate("Leaders") : $this->translate("Owner")) . " (" . $this->string()->truncate($this->subject()->getTitle(), 15) . ")";



          echo ($this->subject()->getType() === 'siteevent_event' ? $this->translate("Leaders %s", " (" . $this->string()->truncate($this->subject()->getTitle(), 15) . ")") : $this->translate("Owner %s", " (" . $this->string()->truncate($this->subject()->getTitle(), 15) . ")")) ;


        endif;
        ?>  
      </a>
    </li>
    <?php if ($this->subject()->getType() === 'siteevent_event' ): ?>
      <li class="tab_2">
        <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
      getTabBaseContentFeed('membership', '0');
      $('feed-update').empty();"  title="<?php echo  $this->translate("Guests")  ?>"><?php echo  $this->translate("Guests")  ?></a>
      </li>   
    <?php elseif ($this->viewer()->getIdentity() && ($this->subject()->getType() != 'user')): ?>	
      <li class="tab_2">
        <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
      getTabBaseContentFeed('membership', '0');
      $('feed-update').empty();" <?php echo  $this->translate("Friends") ?> ><?php echo  $this->translate("Friends") ?></a>
      </li>   
<?php endif; ?>
<?php if (in_array($type,$allowedModules) && $authorizationApi->isAllowed('advancedactivity_feed', $this->viewer, 'aaf_advertise_enable')) : ?> 
        <li class="tab_3">        
          <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
        getTabBaseContentFeed('advertise', '0');
        $('feed-update').empty();" title="<?php echo $this->translate("BuySell") ?>"><?php echo $this->translate("BuySell") ?></span></a>
        </li>
<?php endif; ?>
<?php if ($this->subject()->getType() === 'user' && ($this->viewer()->getIdentity() == $this->subject()->getIdentity())) : ?>
   
    <?php $statusBoxOptions = $this->settings('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy", "webcam", "postTarget","schedulePost")); ?>
    <?php if ($authorizationApi->isAllowed('advancedactivity_feed', $this->viewer, 'aaf_schedule_post_enable') && in_array('schedulePost',$statusBoxOptions)) : ?>
    
        <li class="tab_4">        
          <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
        getTabBaseContentFeed('schedule_post', '0');
        $('feed-update').empty();" title="<?php echo $this->translate("Scheduled Posts") ?>"><?php echo $this->translate("Scheduled Post") ?></a>
        </li>
    <?php endif; ?>
    <li class="tab_5">        
      <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
    getTabBaseContentFeed('hidden_post', '0');
    $('feed-update').empty();" title="<?php echo $this->translate("Hidden Post") ?>"><?php echo $this->translate("Hidden Post") ?></a>
    </li>
    <?php if ($authorizationApi->isAllowed('advancedactivity_feed', $this->viewer, 'aaf_memories_enable')) : ?>
    <li class="tab_6" id="tab_memories">        
          <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
        getTabBaseContentFeed('memories', '0');
        $('feed-update').empty();" title="<?php echo $this->translate("On this day") ?>"><?php echo $this->translate("On this day") ?></a>
        </li>
    <?php endif; ?>
<?php endif; ?>
    <?php $allowPin = Engine_Api::_()->authorization()->isAllowed('advancedactivity_feed', $this->viewer(), 'aaf_pinunpin_enable');
    if (0 && $allowPin) : ?>
    <li class="tab_6" id="tab_pinfeed">        
          <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
        getTabBaseContentFeed('pinfeed', '0');
        $('feed-update').empty();" title="<?php echo $this->translate("Pin Feed") ?>"><?php echo $this->translate("Pin Feed") ?></a>
        </li>
    <?php endif; ?>    
    <li class="tab_7 aaf_tab_active" id="tab_advFeed_everyone">        
      <a href="javascript:void(0);"   onclick="javascript: tabProfileContainerSwitch($(this));
    getTabBaseContentFeed('all', '0');
    $('feed-update').empty();" title="<?php echo $this->translate("Everyone") ?>"><?php echo $this->translate("Everyone") ?><span id="update_advfeed_blink" class="notification_star"></span></a>
    </li>
  </ul> 	
</div>
<?php if(!empty($this->onthisday)): ?>
  <script type="text/javascript">
    en4.core.runonce.add(function(){
        (function(){
         tabProfileContainerSwitch($('tab_memories'));   
        getTabBaseContentFeed('memories', '0');    
         }).delay(5000);  
    });
  </script>
<?php endif; ?>