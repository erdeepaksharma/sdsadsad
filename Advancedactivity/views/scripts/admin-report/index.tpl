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

<script type="text/javascript">
  en4.core.runonce.add(function() {
    $$('th.admin_table_short input[type=checkbox]').addEvent('click', function() {
      $$('input[type=checkbox]').set('checked', $(this).get('checked', false));
    });
  });
  
  var delectSelected = function() {
    var checkboxes = $$('input[type=checkbox]');
    var selecteditems = [];

    checkboxes.each(function(item, index){
      var checked = item.get('checked', false);
      var value = item.get('value', false);
      if (checked == true && value != 'on'){
        selecteditems.push(value);
      }
    });

    $('ids').value = selecteditems;
    $('delete_selected').submit();
  }

  function multiDelete()
  {
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure that you want to delete this report?")) ?>');
  }
</script>

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

<h2><?php echo $this->translate("ADVANCED_ACTIVITY_PLUGIN_NAME")." ".$this->translate("Plugin") ?></h2>
<?php if( count($this->subnavigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render(); ?>
  </div>
<?php endif; ?>

<h3><?php echo $this->translate("Abuse Reports") ?></h3>
<p>
  <?php echo $this->translate('This page lists all of the reports your users have sent in regarding inappropriate activity feeds content. Below, you can view the content for each report by using the "take action" option and can perform various actions on the report.') ?> 
</p>


<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <script type="text/javascript">
    var currentOrder = '<?php echo $this->filterValues['order'] ?>';
    var currentOrderDirection = '<?php echo $this->filterValues['direction'] ?>';
    var changeOrder = function(order, default_direction){
      // Just change direction
      if( order == currentOrder ) {
        $('direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
      } else {
        $('order').value = order;
        $('direction').value = default_direction;
      }
      $('filter_form').submit();
    }
  </script>
  <br />
<?php endif; ?>

<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s report found", "%s reports found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'query' => $this->filterValues,
      'pageAsQuery' => true,
    )); ?>
  </div>
</div>
<br />

<?php if( count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url(array('action' => 'deleteselected'), 'admin_default');
?>" onSubmit="return multiDelete()" class="global_form">

<div>
  <table class='admin_table'>
    <thead>
      <tr>
        <th style="width: 1%;" class="admin_table_short"><input type='checkbox' class='checkbox'></th>
        <th style="width: 1%;">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('report_id', 'ASC');">
            <?php echo $this->translate("ID") ?>
          </a>
        </th>
        <th style="width: 1%;" align="left">
          <?php echo $this->translate("Reporter") ?>
        </th>
        <th style="width:18%;" align="left">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('description', 'ASC');">
            <?php echo $this->translate("Description") ?>
          </a>
        </th>
        <th style="width: 1%;" align="left">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'ASC');">
            <?php echo $this->translate("Date") ?>
          </a>
        </th>
        <th style="width: 1%;" align="left">
          <a href="javascript:void(0);" onclick="javascript:changeOrder('category', 'ASC');">
            <?php echo $this->translate("Reasons") ?>
          </a>
        </th>
        <th style="width: 10%;white-space: nowrap;" align="left">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
          <td><input name='delete_<?php echo $item->report_id; ?>' type='checkbox' class='checkbox' value="<?php echo $item->report_id ?>"/></td>
          <td><?php echo $item->report_id ?></td>
          <td class="nowrap"><?php echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $this->item('user',
          $item->user_id)->getTitle(), array('target' => '_blank')) ?></td>
          <td style="white-space: normal;"><?php echo $item->description ?></td>
          <td class="nowrap"><?php echo $item->creation_date ?></td>
          <td class="nowrap"><?php echo $item->category ?></td>
          <td class="admin_table_options">
            <?php echo $this->htmlLink(array('route' => 'default','module' => 'advancedactivity','controller' => 'take-action', 'action' => 'action', 'id' => $item->getIdentity(), 'reset' =>
false, 'format' => 'smoothbox'), $this->translate("take action"), array('class' =>
            'smoothbox'))          ?>
            <span class="sep">|</span>
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'advancedactivity', 'controller' => 'report', 'action' => 'delete', 'id' => $item->getIdentity()),
              $this->translate('dismiss'), array('class' => 'smoothbox',)) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <br/>
  <div class='buttons'>
    <button type='submit'><?php echo $this->translate('Dismiss Selected'); ?></button>
  </div>
  <?php else:?>
    <div class="tip">
      <span><?php echo $this->translate("There are currently no outstanding abuse reports.") ?></span>
    </div>
  <?php endif; ?>
  </div>
</form>

