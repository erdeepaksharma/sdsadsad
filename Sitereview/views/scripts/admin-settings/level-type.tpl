<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: level-type.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<script type="text/javascript">
  var fetchLevelSettings =function(level_id){
    var listingtype_id = 1;
    if($('listingtype_id')) {
      listingtype_id = $('listingtype_id').value;
    }
    window.location.href= en4.core.baseUrl+'admin/sitereview/settings/level-type/id/'+level_id+'/listingtype_id/'+listingtype_id;
  }

  var fetchListingTypeSettings =function(listingtype_id){
		var level_id = $('level_id').value;
    window.location.href= en4.core.baseUrl+'admin/sitereview/settings/level-type/id/'+level_id+'/listingtype_id/'+listingtype_id;
  }
</script>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<?php if($this->listingTypeCount > 1): ?>
  <div class='tabs'>
    <ul class="navigation">
      <li class="<?php if($this->tab_type == 'levelType') { echo "active"; } ?>">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'settings', 'action' => 'level-type', 'listingtype_id' => $this->listingtype_id), $this->translate('Listing Type-Member Level Settings')) ?>
      </li>
      <li class="<?php if($this->tab_type == 'level') { echo "active"; } ?>">
       <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'settings', 'action' => 'level'), $this->translate('General-Member Level Settings')) ?>
      </li>
    </ul>
  </div>
<?php endif; ?>

<div class='seaocore_settings_form'>
  <div class='settings'>
    <?php echo $this->form->render($this) ?>
  </div>
</div>