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
	$this->headLink()
  	->prependStylesheet($this->layout()->staticBaseUrl.'application/modules/Advancedactivity/externals/styles/style_advancedactivity.css');
?>
<div class="adv_activity_welcome">
	<div class="adv_activity_welcome_num">
	</div>
  <div class="adv_activity_welcome_cont">	
  	<div class="adv_activity_welcome_cont_title">	
	  	<?php echo $this->translate('Search for People'); ?>
    </div>
    <div class="adv_activity_welcome_cont_des">
    	<?php echo $this->translate('Search by name or look for classmates and coworkers.'); ?>
    </div>
	  <div class="adv_activity_welcome_cont_search">
	  	<form action="<?php echo $this->url(array(), 'user_extended', true); ?>">
        <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemember')): ?>
          <input type="text" name="search" id="search" />
        <?php else: ?>
          <input type="text" name="displayname" id="displayname" />
        <?php endif; ?>	    	
	      <button type='submit'><?php echo $this->translate("Search"); ?></button>
	    </form>
	  </div>
	</div>
</div>	