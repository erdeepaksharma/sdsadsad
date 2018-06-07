<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: install.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<h2>
<?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME") . " " . $this->translate("Plugin") ?>
</h2>
<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php
    echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'feelingtype'), $this->translate("Back to Manage Feeling Type"), array('class' => 'seaocore_icon_back buttonlink')) ?>
<br style="clear:both;" /><br />
<h3>
  <?php echo $this->translate("Manage Feelings of: " . $this->feelingtype->getTitle()); ?>
</h3>
<p>
  <?php echo $this->translate('Below, you can manage and order the feeling of this feelingtype. Drag and drop feelings to arrange their sequence. You can also add new feeling to this feelingtype.
'); ?> 
</p>
<br />
<div class="collection_info">
  <div class="photo">
    <?php
    echo $this->itemPhoto($this->feelingtype, 'thumb.small-icon', '', array(
      'align' => 'center'))
    ?>
  </div>
  <div class="avatar_details">
    <div class="av_title">
      <?php echo $this->feelingtype->getTitle() ?>
    </div>
  </div>
  <div>
    <a href="<?php echo $this->url(array('action' => 'add-more', 'feelingtype_id' => $this->feelingtype->getIdentity())) ?>" class="buttonlink seaocore_icon_add" title="<?php echo $this->translate('Add more Feelings'); ?>"><?php echo $this->translate('Add more Feelings'); ?></a> |
    <a href="<?php echo $this->url(array('action' => 'edit', 'feelingtype_id' => $this->feelingtype->getIdentity())) ?>" class="buttonlink seaocore_icon_edit" title="<?php echo $this->translate('Edit'); ?>"><?php echo $this->translate('Edit'); ?></a>
  </div>
</div>



<div class="feelings_collection">
  <form id='saveorder_form' method='post' action='<?php
  echo $this->url(array('controller' => 'feeling',
    'action' => 'update-order'))
  ?>'>
    <input type='hidden'  name='order' id="order" value=''/>
    <input type='hidden'  name='feelingtype_id' id="feelingtype_id" value='<?php echo $this->feelingtype->getIdentity(); ?>'/>

    <div id='order-element'>
      <ul>
        <?php foreach( $this->feelingtype->getFeelings() as $feeling ): ?>
          <li>
            <input type='hidden'  name='order[]' value='<?php echo $feeling->getIdentity(); ?>'>
            <span>
              <span class="image">
                <?php
                echo $this->itemPhoto($feeling, '', '', array(
                  'align' => 'center'))
                ?>
              </span>
              <span class="info">
                <?php echo $feeling->getTitle() ?>             
              </span>
              <span class="options">
                <a href="<?php
                echo $this->url(array('controller' => 'feeling', 'action' => 'edit',
                  'feeling_id' => $feeling->getIdentity()))
                ?>" class="smoothbox" title="Edit">Edit</a> &nbsp; | &nbsp;
                <a href="<?php
                echo $this->url(array('controller' => 'feeling', 'action' => 'delete',
                  'feeling_id' => $feeling->getIdentity()))
                ?>" class="smoothbox" title="Delete">Delete</a>
              </span>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </form>
  <br />
  <button onClick="javascript:saveOrder(true);" type='submit'>
    <?php echo $this->translate("Save Order") ?>
  </button>
</div>

<style type="text/css">
  .feelings_collection ul {
    display: table;
    width: 450px;

  }
  .feelings_collection ul li{
    width: 100%;
    position: relative;
    display:table;
    margin-bottom: 7px;
    verflow: hidden;
    clear: both;
  }
  .feelings_collection ul li > span {
    width: 100%;
    font-size: .8em;
    clear: both;
    border-radius: 3px;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    overflow: hidden;
    display: table;
    box-sizing: border-box;
  }
  .feelings_collection ul li .image {
    vertical-align:middle;
    width: 50px;
    display: inline-block;
    box-sizing: border-box;
    padding: 3px 9px;
  }
  .feelings_collection ul li .info {
    width: 300px;
    display: table-cell;
    overflow: hidden;
    font-weight: bold;
    padding: 7px 10px 7px 10px;
    box-sizing: border-box;
  }
  .feelings_collection ul li .options {
    text-align: right;
    isplay: table-cell;
    width: 100px;
  }
  .feelings_collection ul li .image img {
    width: 32px;
    height: 32px;
  }
  /* Collections*/
  .collection_info {
    margin-top:10px;
  }
  .photo {
    width:64px;
    float:left;
    margin-right:10px;
  }
  .avatar_details {
    overflow:hidden;
  }
  .avatar_details + div{
    clear:both;
    padding-top:15px;
    padding-bottom:10px;
  }
  .av_title {
    font-weight:bold;
    text-transform:capitalize;
    margin-bottom:5px;
  }
</style>
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
  window.addEvent('domready', function () {
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

  window.onbeforeunload = function (event) {
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

