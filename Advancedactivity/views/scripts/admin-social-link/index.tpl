<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
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
  <?php echo $this->translate('Manage Social Links'); ?>
</h3>
<p>
  <?php echo $this->translate('Below, you can manage your social link.'); ?> 
</p>
<br />

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
        <?php foreach ($this->links as $item) : ?>
          <li>
            <input type='hidden'  name='order[]' value='<?php echo $item->getIdentity(); ?>'>
            <div style="width:10%;" class='admin_table_bold '>
              <?php
              echo $this->htmlImage($item->icon_path,'', array('title' => $this->translate($item->title),'height'=>'40px','width'=>'40px'), array());  
              ?>
            </div>
            <div style="width:35%;" class='admin_table_bold'>
              <?php echo $item->title; ?>
            </div>
            <div style="width:10%;" class='admin_table_bold'>
             
               <?php echo ( $item->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' =>
'advancedactivity', 'controller' => 'social-link', 'action' => 'enabled', 'link_id' =>
$item->link_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled1.gif',
'', array('title' => $this->translate('Disable link'))), array())  :
$this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'social-link',
'action' => 'enabled', 'link_id' => $item->link_id),
$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/enabled0.gif', '', array('title' =>
$this->translate('Enable link')))) ) ?>
            </div>
            <div style="width:15%;">
               <a href='<?php echo $this->url(array('action' => 'edit', 'link_id' => $item->getIdentity())) ?>'>
                <?php echo $this->translate("Edit") ?>
              </a>
              | <a href='<?php echo $this->url(array('action' => 'delete', 'link_id' => $item->getIdentity())) ?>' class="smoothbox">
                <?php echo $this->translate("Delete") ?>
              </a>
            </div>
          </li>
        <?php endforeach; ?>	
      </ul>
    </div>
  </form>
</div>
 
 <br />
  <button onClick="javascript:saveOrder(true);" type='submit'>
    <?php echo $this->translate("Save Order") ?>
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
