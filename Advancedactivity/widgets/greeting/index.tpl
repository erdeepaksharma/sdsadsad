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
<?php if(!empty($this->userItSelfBirthday)): ?>
    <div class="aaf_user_self_birthday_greeting_wrapper">
    <a class="aaf_close_greeting" id="aaf_close_greeting" onclick="resetBirthDayGreeting('has_seen_own')" title="Close"></a>
        <div class="aaf_user_self_top_image"></div>
        <div class="aaf_birthday">
            <?php $user = Engine_Api::_()->user()->getUser($id); ?>
            <div class="aaf_birthday_greeting_wrapper-image">
                <?php echo $this->htmlLink($this->viewer->getHref(), $this->itemPhoto($this->viewer,'thumb.profile')); ?>
            </div>
            <div class="aaf_birthday_greeting_wrapper-title">
                <?php echo $this->htmlLink($this->viewer->getHref(), $this->viewer->getTitle()); ?>
            </div>
            <div class="aaf_birthday_greeting_wrapper-text">
              <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->translate('_SITE_TITLE')); ?>
                <?php echo $this->translate('Wishes you happy birthday !') ?></div>
        </div>

    </div>
<?php elseif(!empty($this->todaysBirthday)): ?>
<div class="aaf_birthday_greeting_wrapper">
    <?php foreach($this->todaysBirthday as $id):?>
        <div class="aaf_birthday_slides">
        <?php $user = Engine_Api::_()->user()->getUser($id); ?>
        <div class="aaf_birthday_greeting_wrapper-image">
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user,'thumb.profile')); ?>
        </div>
        <div class="aaf_birthday_greeting_wrapper-title">
        <?php echo $this->htmlLink($user->getHref(), $user->getTitle()); ?>
       </div>
        <div class="aaf_birthday_greeting_wrapper-text"><?php echo $this->translate('Hey! %s , It\'s %s birthday today. help him/her to celebrate their birthday',array_shift(explode(" ",$this->viewer->getTitle())),array_shift(explode(" ",$user->getTitle()))) ?></div>
        <div class="aaf_birthday_greeting_wrapper-action"><span class="aaf_birthday_greeting_wrapper-action-post" onclick="resetBirthDayGreeting('has_seen')"><a href="<?php echo $user->getHref() ?>"> <?php echo $this->translate('Write a post') ?></a></span><span class="aaf_birthday_greeting_wrapper-action-message" onclick="resetBirthDayGreeting('has_seen')"><a href="<?php echo $this->baseUrl('messages/compose') ?>/to/<?php echo $user->getIdentity() ?>"><?php echo $this->translate('Message') ?></a></span></div> 
        </div>
    <?php endforeach; ?>
</div>
<script type="text/javascript">
var currentIndex = 0;
function birthdaySlideshow() {
    var i;
    var x = $$(".aaf_birthday_slides");
    for (i = 0; i < x.length; i++) {
       x[i].style.display = "none";  
    }
    currentIndex++;
    if (currentIndex > x.length) { currentIndex = 1 }    
    x[currentIndex-1].style.display = "block";  
    setTimeout(birthdaySlideshow, 2000);
}
en4.core.runonce.add(function() { 
birthdaySlideshow();
});
</script>
<?php elseif(!empty($this->findFriends)): ?>
<div class="aaf_find_friend_wrapper">
    <div class="aaf_find_friend_heading"><?php echo $this->translate('Add More Friends') ?><a class="aaf_close_greeting" id="aaf_close_greeting" onclick="resetBirthDayGreeting('has_seen_find')" title="Close"></a></div>
    <div class="aaf_find_friend_section"><span class="aaf_find_friend_image"></span></div>
    <div class="aaf_find_friend_description">
        <?php echo $this->translate('Add Friends to See More Posts ?') ?>
    </div>
    <?php echo $this->htmlLink(array('route' => 'user_general'), $this->translate('Find Friends'), array("class"=>"aaf_find_friend_btn")) ?>
</div>
<?php else: ?>

<a class="aaf_close_greeting" id="aaf_close_greeting_<?php echo $this->identity ?>" onclick="closeThis()" title="Close"></a>
<span class="sep"> </span>
<div class="seaocore_content">
<?php $body =  str_replace("[USER_NAME]",$this->viewer->getTitle(),$this->body);
      echo  str_replace("[USER_PHOTO]",$this->itemPhoto($this->viewer,'thumb.icon'),$body)  ?> 
</div> 

<script>
 
function closeThis(){
   $('aaf_close_greeting_<?php echo $this->identity ?>').getParent().setStyle('display','none');
   url = en4.core.baseUrl + 'advancedactivity/index/set-cookie';
   greeting_id = '<?php  echo $this->greeting_id ?>';
   var request = new Request.JSON({
    'url' : url,
    'method':'post',
    'data' : {
    'format' : 'json',
    'greeting_id' : greeting_id,
    }
   });
    request.send();
    }
    
</script>
<?php endif; ?>
<script type="text/javascript">
function resetBirthDayGreeting(resetName) {
    var d = new Date();
    d.setTime(d.getTime() + (24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = resetName+" = true;" + expires + ";path=/";
}
</script>