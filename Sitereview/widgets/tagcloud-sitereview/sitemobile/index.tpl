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

<?php if($this->showcontent):?>
    <?php 
     $request = Zend_Controller_Front::getInstance()->getRequest();
     $module = $request->getModuleName();
     $controller = $request->getControllerName();
     $action = $request->getActionName();
   ?>

   <?php if($module == 'sitereview' && $controller == 'index' && $action == 'top-rated'):?>
     <?php $url_action = 'top-rated';?>
   <?php else:?>
     <?php $url_action = 'index';?>
   <?php endif;?>

    <section class="sm-widget-block">
      <div>
        <?php foreach($this->tag_array as $key => $frequency):?>
          <?php $step = $this->tag_data['min_font_size'] + ($frequency - $this->tag_data['min_frequency'])*$this->tag_data['step'] ?>
          <a href='<?php echo $this->url(array('action' => $url_action), "sitereview_general_listtype_" . $this->listingtype_id); ?>?tag=<?php echo urlencode($key) ?>&tag_id=<?php echo $this->tag_id_array[$key] ?>' style="font-size:<?php echo $step ?>px;" title=''><?php echo $key ?><sup><?php echo $frequency ?></sup></a> 
        <?php endforeach;?>
      </div>		

      <div>
        <?php echo $this->htmlLink(array('route' => "sitereview_general_listtype_$this->listingtype_id", 'action' => 'tagscloud'), $this->translate('EXPLORE_'.$this->listing_singular_upper.'_TAGS &raquo;'), array('class' => 'ui-btn')) ?>
      </div>
    </section>
<?php endif; ?>

