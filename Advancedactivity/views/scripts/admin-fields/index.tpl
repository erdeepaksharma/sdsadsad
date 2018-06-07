<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>


<?php
  // Render the admin js
  echo $this->render('_jsAdmin.tpl')
?>

<h2>Advancedactivity Plugin</h2>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<p> <?php echo $this->translate('Below, you can manage and order various fields to be available to advertise a single product from the status box.') ?></p>
<br />

<div class="admin_fields_options">
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addquestion">Add Field</a>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addheading" style="display:none;">Add Heading</a>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_saveorder" style="display:none;">Save Order</a>
</div>

<br />

<div class='seaocore_admin_form'>
  <div class='settings'>
    <ul class="admin_fields">
      <?php foreach( $this->topLevelMaps as $field ): ?>
        <?php echo $this->adminFieldMeta($field) ?>
      <?php endforeach; ?>
    </ul>
  </div> 
</div>
<br />
<br />


