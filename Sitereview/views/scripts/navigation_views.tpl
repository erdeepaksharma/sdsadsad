<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: navigation_views.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>
<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/styles.css'); ?>
<?php $this->headLink()->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css'); ?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
   
    var moreTabSwitchNavigation = window.moreTabSwitchNavigation = function(el) {
      el.toggleClass('seaocore_tab_open active');
      el.toggleClass('tab_closed');
    }
  });
</script>

<?php 
$request = Zend_Controller_Front::getInstance()->getRequest();
$controller = $request->getControllerName();
$action = $request->getActionName();
$showCategriesMenu = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.showcategories.menu', 1);
$listingtype_id = !empty($this->listingtype_id) ? $this->listingtype_id : -1;
if ($showCategriesMenu): ?>
  <?php echo $this->content()->renderWidget("sitereview.listtypes-categories", array('beforeNavigation'=>1, 'listingtype_id' => $listingtype_id)) ?>
<?php endif; ?>

<div class="headline">
  <h2 > <?php echo $this->title ? $this->translate($this->title) : $this->translate("Reviews"); ?> </h2>
  <div class="tabs">
    <?php $navigation_common = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_common"); ?>
    <?php
    if (count($this->navigation) > 0):
      foreach ($navigation_common->getPages() as $page):
        $page->set('order', $page->get('order') + 900);
      endforeach;
      foreach ($this->navigation->getPages() as $page):
        $navigation_common->addPage($page);
      endforeach;
    else:
      ?>
    <?php endif;
    ?>
    <?php if (count($navigation_common)): ?>
      <?php
      ?>
      <?php 
        $tabs_listingtype_id = !empty($this->listingtype_id) ? $this->listingtype_id : 1;
        Engine_Api::_()->sitereview()->setListingTypeInRegistry($tabs_listingtype_id);
        $this->max = Zend_Registry::get('listingtypeArray' . $tabs_listingtype_id)->navigation_tabs; 
      ?>

      <ul class='navigation sr_navigation_common'>
        <?php $key = 0; ?>
        <?php foreach ($navigation_common as $item):?>
          <?php if ($key < $this->max): ?>
            <li <?php if ($item->isActive() || ($this->package_show == 1 && $item->action ==  'create' && $controller == 'package' && $action != 'update-package')) : ?> class="active" <?php endif; ?>>
              <?php $name = trim(str_replace('menu_core_main ', '', $item->getClass())); ?>
              <?php if($item->action == 'create'):?>
              <?php $explodedRoute = explode('sitereview_general_listtype_', $item->getRoute());?>
             <?php $tabs_listingtype_id = $explodedRoute[1];?>
             <?php if(Engine_Api::_()->sitereview()->hasPackageEnable())
              $PackageCount = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getPackageCount($tabs_listingtype_id);
            else
              $PackageCount = 0; ?>
              <?php if($PackageCount > 0):?>
                <a class= "<?php echo $item->class ?>" href='<?php echo $this->url(array('action' => 'index'), 'sitereview_package_listtype_' . $tabs_listingtype_id, true)?>' <?php if ($item->target): ?> target="_blank" <?php endif; ?>><?php echo $this->translate($item->label); ?></a>
              <?php else:?>
                <a href="<?php echo $item->getHref(); ?>" class="<?php echo $item->getClass() ?>" <?php if ($item->target): ?> target="_blank" <?php endif; ?> >
                        <?php echo $this->translate($item->getLabel()); ?>
                </a>
              <?php endif;?>
              <?php else:?>
                <a href="<?php echo $item->getHref(); ?>" class="<?php echo $item->getClass() ?>" <?php if ($item->target): ?> target="_blank" <?php endif; ?> >
                        <?php echo $this->translate($item->getLabel()); ?>
                </a>
              <?php endif;?>
            </li>
          <?php else: ?>
            <?php break; ?>
          <?php endif; ?>
          <?php $key++ ?>
        <?php endforeach; ?>

        <?php if (count($navigation_common) > $this->max): ?>
          <li class="tab_closed more_tab" onclick="moreTabSwitchNavigation($(this));">
            <div class="tab_pulldown_contents_wrapper">
              <div class="tab_pulldown_contents">          
                <ul>
                  <?php $key = 0; ?>
                  <?php foreach ($navigation_common as $item): ?>
                    <?php if ($key >= $this->max): ?>
                        <li <?php if ($item->isActive() || ($item->action ==  'create' && $controller == 'package' && $action != 'update-package')) : ?> class="active" <?php endif; ?>>
                        <?php $name = trim(str_replace('menu_core_main ', '', $item->getClass())); ?>
												<?php if($item->action == 'create'):?>
             <?php $explodedRoute = explode('sitereview_general_listtype_', $item->getRoute());?>
             <?php $tabs_listingtype_id = $explodedRoute[1];?>
             <?php if(Engine_Api::_()->sitereview()->hasPackageEnable())
              $PackageCount = Engine_Api::_()->getDbTable('packages', 'sitereviewpaidlisting')->getPackageCount($tabs_listingtype_id);
            else
              $PackageCount = 0; ?>
             <?php if($PackageCount > 0):?>
              <a class= "<?php echo $item->class ?>" href='<?php echo $this->url(array('action' => 'index'), 'sitereview_package_listtype_' . $tabs_listingtype_id, true)?>' <?php if ($item->target): ?> target="_blank" <?php endif; ?>><?php echo $this->translate($item->label); ?></a>
             <?php else:?>
              <a href="<?php echo $item->getHref(); ?>" class="<?php echo $item->getClass() ?>" <?php if ($item->target): ?> target="_blank" <?php endif; ?> >
													<?php echo $this->translate($item->getLabel()); ?>
              </a>
             <?php endif;?>
												<?php else:?>
													<a href="<?php echo $item->getHref(); ?>" class="<?php echo $item->getClass() ?>" <?php if ($item->target): ?> target="_blank" <?php endif; ?> >
													<?php echo $this->translate($item->getLabel()); ?>
													</a>
												<?php endif;?>
                      </li>
                    <?php endif; ?>
                    <?php $key++ ?>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <a href="javascript:void(0);"><?php echo $this->translate('More +') ?><span></span></a>
          </li>
        <?php endif; ?>
      </ul>

    <?php endif; ?>
  </div>
</div>
<?php  $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();?>


<script type="text/javascript">
  <?php if ($this->listingtype_id > 0 && $listingTypeCount > 1): ?>
    en4.core.runonce.add(function() {
      var element= document.getElement('.core_main_sitereview_listtype_'+<?php echo $this->listingtype_id ?>);
      if(element){
        var myContainer = document.getElement('.layout_core_menu_main');
        if(myContainer)
        myContainer.getElements('ul > li').removeClass('active');
        element.getParent().addClass('active');
      }else{
        var myContainer = document.getElement('.layout_core_menu_main');
        if(myContainer)
        myContainer.getElements('ul > li').removeClass('active');
      }
    });
  <?php else: ?>
    var myContainer = document.getElement('.layout_sitereview_navigation_sitereview');
    if(myContainer){
      var element= myContainer.getElement('ul > li.active');
        if(element){
          var title=element.getElement('a').get('html');
          var className=element.getElement('a').get('class');
          if(className.indexOf('sitereview_main_common_reviews')!== -1){
            title='<?php echo $this->string()->escapeJavascript($this->translate('Reviews')) ?>';
          }
          myContainer.getElement('h2').set('html',title);
        }
      }
  <?php endif; ?>
</script>
