<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<h3>
  <?php echo $this->translate('Manage Feelings'); ?>
</h3>
<p>
  <?php echo $this->translate('Below, you can manage and order various feelings listed below. Drag and drop items to arrange their sequence. You can also add a new feeling via ‘Create New Feeling’.'); ?> 
</p>
<br />
<div>
  
  <a href="<?php echo $this->url(array('action' => 'create')) ?>" class="buttonlink seaocore_icon_add" title="<?php echo $this->translate('Create New Feelingtype'); ?>"><?php echo $this->translate('Create New Feeling'); ?></a>
</div>
<br />

<div class="seaocore_admin_order_list">
  <div class="list_head">
    <div style="width:10%">
      <?php echo $this->translate("Icon"); ?>
    </div>
    <div style="width:35%">
      <?php echo $this->translate("Title"); ?>
    </div>
    <div style="width:10%">
      <?php echo $this->translate("Count"); ?>
    </div>
    <div style="width:15%">
      <?php echo $this->translate("Enable"); ?>
    </div>
    <div style="width:15%">
      <?php echo $this->translate("Options"); ?>
    </div>
  </div>

  <form id='saveorder_form' method='post' action='<?php echo $this->url(array('action' => 'update-order')) ?>'>
    <input type='hidden'  name='order' id="order" value=''/>
    <div id='order-element'>
      <ul>
        <?php foreach ($this->feelingtypes as $item) : ?>
          <li>
            <input type='hidden'  name='order[]' value='<?php echo $item->getIdentity(); ?>'>
            <div style="width:10%;" class='admin_table_bold uploaded_stickers'>
              <?php
              echo $this->itemPhoto($item, 'thumb.small-icon', '', array(
                'align' => 'center'))
              ?>
            </div>
            <div style="width:35%;" class='admin_table_bold'>
              <?php echo $item->getTitle(true); ?>
            </div>

            <div style="width:10%;" class='admin_table_bold'>
              <?php echo empty($item->type) ? $item->count() : '-'?>
            </div>
            <div style="width:15%;" class='admin_table_bold'>
              <?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' =>
'advancedactivity', 'controller' => 'feelingtype', 'action' => 'enabled', 'feelingtype_id' =>
$item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled1.gif',
'', array('title' => $this->translate('Disable Feeling Type'))), array())  :
$this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'feelingtype',
'action' => 'enabled', 'feelingtype_id' => $item->getIdentity()),
$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled0.gif', '', array('title' =>
$this->translate('Enable Feeling Type')))) ) ?>
            </div>
            <div style="width:15%;">
                <?php if(empty($item->type)) : ?>
              <a href='<?php echo $this->url(array('action' => 'manage', 'feelingtype_id' => $item->getIdentity())) ?>'>
                <?php echo $this->translate("Manage") ?>
              </a>
              | <?php endif; ?>
              <a href='<?php echo $this->url(array('action' => 'edit', 'feelingtype_id' => $item->getIdentity())) ?>'>
                <?php echo $this->translate("Edit") ?>
              </a>
               <?php if(empty($item->default)) : ?>
              | <a href='<?php echo $this->url(array('action' => 'delete', 'feelingtype_id' => $item->getIdentity())) ?>' class="smoothbox">
                <?php echo $this->translate("Delete") ?>
              </a>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>	
      </ul>
    </div>
  </form>
  <br />
  <button onClick="javascript:saveOrder(true);" type='submit'>
    <?php echo $this->translate("Save Changes") ?>
  </button>
</div>
<script type="text/javascript">

  var saveFlag = false;
  var origOrder;
  var changeOptionsFlag = false;

  function saveOrder(value) {
    saveFlag = value;
    var finalOrder = [];
    var li = $('order-element').getElementsByTagName('li');
    for (i = 1; i <= li.length; i++)
      finalOrder.push(li[i]);
    $("order").value = finalOrder;

    $('saveorder_form').submit();
  }
  window.addEvent('domready', function() {
    //         We autogenerate a list on the fly
    var initList = [];
    var li = $('order-element').getElementsByTagName('li');
    for (i = 1; i <= li.length; i++)
      initList.push(li[i]);
    origOrder = initList;
    var temp_array = $('order-element').getElementsByTagName('ul');
    temp_array.innerHTML = initList;
    new Sortables(temp_array);
  });

  window.onbeforeunload = function(event) {
    var finalOrder = [];
    var li = $('order-element').getElementsByTagName('li');
    for (i = 1; i <= li.length; i++)
      finalOrder.push(li[i]);



    for (i = 0; i <= li.length; i++) {
      if (finalOrder[i] != origOrder[i])
      {
        changeOptionsFlag = true;
        break;
      }
    }

    if (changeOptionsFlag == true && !saveFlag) {
      var answer = confirm("<?php echo $this->string()->escapeJavascript($this->translate("A change in the order of the tabs has been detected. If you click Cancel, all unsaved changes will be lost. Click OK to save change and proceed.")); ?>");
      if (answer) {
        $('order').value = finalOrder;
        $('saveorder_form').submit();

      }
    }
  }
</script>

<style>
    .uploaded_stickers img {
        width:30px;
        vertical-align:middle;
     }
</style>
