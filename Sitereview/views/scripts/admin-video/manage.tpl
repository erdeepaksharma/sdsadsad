<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: manage.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>

<?php if( count($this->subNavigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->subNavigation)->render()
    ?>
  </div>
<?php endif; ?>

<h3>
  <?php echo $this->translate('Manage Videos'); ?>
</h3>

<p>
  <?php echo $this->translate('This page lists all of the videos your users have posted. You can use this page to monitor these videos and delete offensive material if necessary.');?>
</p>

<br />

<script type="text/javascript">
	var currentOrder = '<?php echo $this->order ?>';
	var currentOrderDirection = '<?php echo $this->order_direction ?>';
	var changeOrder = function(order, default_direction){

		if( order == currentOrder ) {
			$('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
		} else {
			$('order').value = order;
			$('order_direction').value = default_direction;
		}
		$('filter_form').submit();
	}
</script>

<?php if($this->type_video):?>

  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

  <script type="text/javascript">

  function multiDelete()
  {
    return confirm("<?php echo $this->translate("Are you sure you want to delete the selected videos?") ?>");
  }

  function selectAll()
  {
    var i;
    var multidelete_form = $('multidelete_form');
    var inputs = multidelete_form.elements;
    for (i = 1; i < inputs.length; i++) {
      if (!inputs[i].disabled) {
        inputs[i].checked = inputs[0].checked;
      }
    }
  }

  function killProcess(video_id) {
      (new Request.JSON({
        'format': 'json',
        'url' : '<?php echo $this->url(array('module' => 'video', 'controller' => 'admin-manage', 'action' => 'kill'), 'default', true) ?>',
        'data' : {
          'format' : 'json',
          'video_id' : video_id
        },
        'onRequest' : function(){
          $$('input[type=radio]').set('disabled', true);
        },
        'onSuccess' : function(responseJSON, responseText)
        {
          window.location.reload();
        }
      })).send();

    }
  </script>

  <?php if( count($this->paginator) ): ?>
    <form id='multidelete_form' method="post" action="<?php echo $this->url(array('action'=>'delete-selected'));?>" onSubmit="return multiDelete()">
    <table class='admin_table'>
      <thead>
        <tr>
          <th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
          <th class='admin_table_short'><a href="javascript:void(0);" onclick="javascript:changeOrder('video_id', 'DESC');"><?php echo $this->translate('ID'); ?></a></th>
          <th><a href="javascript:void(0);" onclick="javascript:changeOrder('title', 'ASC');"><?php echo $this->translate('Title'); ?></a></th>
          <th><a href="javascript:void(0);" onclick="javascript:changeOrder('displayname', 'ASC');"><?php echo $this->translate('Owner');?></a></th>
          <th><a href="javascript:void(0);" onclick="javascript:changeOrder('sitereview_title', 'ASC');"><?php echo $this->translate('Listing Title');?></a></th>
          <th><a href="javascript:void(0);" onclick="javascript:changeOrder('view_count', 'ASC');"><?php echo $this->translate('Views'); ?></a></th>
          <th><?php echo $this->translate("Type") ?></th>
          <th><?php echo $this->translate("State") ?></th>
          <th><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'DESC');"><?php echo $this->translate('Creation Date'); ?></th>
          <th><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
          <tr>
            <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->video_id;?>' value='<?php echo $item->video_id ?>' /></td>
            <td><?php echo $item->video_id ?></td>
            <?php             
              $truncation_limit = 13;
              $tmpBody = strip_tags($item->title);
              $item_title = ( Engine_String::strlen($tmpBody) > $truncation_limit ? Engine_String::substr($tmpBody, 0, $truncation_limit) . '..' : $tmpBody );
            ?>
            <td class='admin_table_bold'><?php echo $this->htmlLink($item->getHref(), $item_title, array('title' => $item->title, 'target' => '_blank')) ?></td>

            <?php             
              $truncation_limit = 13;
              $tmpBody = strip_tags($item->displayname);
              $item_owner = ( Engine_String::strlen($tmpBody) > $truncation_limit ? Engine_String::substr($tmpBody, 0, $truncation_limit) . '..' : $tmpBody );
            ?>

            <td class='admin_table_bold'><?php  echo $this->htmlLink($this->item('user', $item->owner_id)->getHref(), $item_owner, array('title' => $item->displayname, 'target' => '_blank')) ?></td>

            <?php             
                $truncation_limit = 13;
                $tmpBodytitle = strip_tags($item->sitereview_title);
                $item_sitereviewtitle = ( Engine_String::strlen($tmpBodytitle) > $truncation_limit ? Engine_String::substr($tmpBodytitle, 0, $truncation_limit) . '..' : $tmpBodytitle );
              ?>	
              <td><?php echo $this->htmlLink($this->item('sitereview_listing', $item->listing_id)->getHref(),$item_sitereviewtitle, array('title' => $item->sitereview_title, 'target' => '_blank')) ?></td>
            <td><?php echo $this->locale()->toNumber($item->view_count) ?></td>
            <td>
              <?php
                switch( $item->type ) {
                  case 1:
                    $type = $this->translate("YouTube");
                    break;
                  case 2:
                    $type = $this->translate("Vimeo");
                    break;
                  case 3:
                    $type = $this->translate("Uploaded");
                    break;
                  default:
                    $type = $this->translate("Unknown");
                    break;
                }
                echo $type;
              ?>
            </td>
            <td>
              <?php
                switch ($item->status){
                  case 0:
                    $status = $this->translate("queued");
                    break;
                  case 1:
                    $status = $this->translate("ready");
                    break;
                  case 2:
                    $status = $this->translate("processing");
                    break;
                  default:
                    $status = $this->translate("failed");
                }
                echo $status;
              ?>
              <?php if($item->status == 2):?>
              (<a href="javascript:void(0);" onclick="javascript:killProcess('<?php echo $item->video_id?>');">
                <?php echo $this->translate("end"); ?>
              </a>)
              <?php endif;?>
            </td>
            <td><?php echo $this->locale()->toDateTime($item->creation_date) ?></td>
            <td>
              <?php if($this->enable_video):?>
                <a href="<?php echo $this->url(array('user_id' => $item->owner_id, 'video_id' => $item->video_id), 'video_view') ?>">
                  <?php echo $this->translate("view") ?>
                </a>
                |
              <?php endif;?>
              <?php echo $this->htmlLink(array('route' => 'admin_default','module'=>'sitereview','controller'=>'video','action' => 'delete', 'video_id' => $item->video_id,'listing_id' => $item->listing_id), $this->translate('delete'), array(
                'class' => 'smoothbox',
              )) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <br />

    <div class='buttons'>
      <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
    </div>
    </form>

    <br />

    <div>
      <?php echo $this->paginationControl($this->paginator); ?>
    </div>

  <?php else: ?>
    <div class="tip">
      <span>
        <?php echo $this->translate("There are no videos posted by your members yet.") ?>
      </span>
    </div>
  <?php endif; ?>
<?php else:?>
  <div class='admin_members_results'>
    <?php 
      if( !empty($this->paginator) ) {
        $counter=$this->paginator->getTotalItemCount(); 
      }
      if(!empty($counter)): 

    ?>
      <div class="">
        <?php  echo $this->translate(array('%s listing video found.', '%s listing videos found.', $counter), $this->locale()->toNumber($counter)) ?>
      </div>
    <?php else:?>
      <div class="tip"><span>
        <?php  echo $this->translate("No results were found.") ?></span>
      </div>
    <?php endif; ?>
  </div>

  <script type="text/javascript">

    function killProcess(video_id) {
      (new Request.JSON({
        'format': 'json',
        'url' : '<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'admin-video', 'action' => 'kill'), 'default', true) ?>',
        'data' : {
          'format' : 'json',
          'video_id' : video_id
        },
        'onRequest' : function(){
          $$('input[type=radio]').set('disabled', true);
        },
        'onSuccess' : function(responseJSON, responseText)
        {
          window.location.reload();
        }
      })).send();

    }

  function multiDelete()
  {
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete selected listing videos ?")) ?>');
  }

  function selectAll()
  {
    var i;
    var multidelete_form = $('multidelete_form');
    var inputs = multidelete_form.elements;
    for (i = 1; i < inputs.length - 1; i++) {
      if (!inputs[i].disabled) {
        inputs[i].checked = inputs[0].checked;
      }
    }
  }

  </script>

  <div class='admin_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>

  <br />

  <?php  if( !empty($counter)):?>
    <form id='multidelete_form' method="post" action="<?php echo $this->url(array('action'=>'delete-selected'));?>" onSubmit="return multiDelete()">
      <table class='admin_table seaocore_admin_table'>
        <thead>
          <tr>
            <th width="1%" align="center"><input onclick="selectAll()" type='checkbox' class='checkbox'></th>
            <th style='width: 1%;'><a href="javascript:void(0);" onclick="javascript:changeOrder('video_id', 'DESC');"><?php echo $this->translate('ID'); ?></a></th>
            <th style='width: 1%;'><a href="javascript:void(0);" onclick="javascript:changeOrder('title', 'ASC');"><?php echo $this->translate('Title'); ?></a></th>
            <th style='width: 1%;'><a href="javascript:void(0);" onclick="javascript:changeOrder('displayname', 'ASC');"><?php echo $this->translate('Owner');?></a></th>
            <th style='width: 1%;'><a href="javascript:void(0);" onclick="javascript:changeOrder('sitereview_title', 'ASC');"><?php echo $this->translate('Listing Title');?></a></th>
            <th style='width: 1%;' class='admin_table_centered'><a href="javascript:void(0);" onclick="javascript:changeOrder('view_count', 'ASC');"><?php echo $this->translate('Views'); ?></a></th>
            <th style='width: 1%;' class='admin_table_centered'><a href="javascript:void(0);" onclick="javascript:changeOrder('comment_count', 'ASC');"><?php echo $this->translate('Comments'); ?></a></th>
            <th style='width: 1%;' class='admin_table_centered'><a href="javascript:void(0);" onclick="javascript:changeOrder('like_count', 'ASC');"><?php echo $this->translate('Likes'); ?></a></th>
            <th style='width: 1%;'><?php echo $this->translate('Type'); ?>
            </th>
            <th style='width: 1%;'><?php echo $this->translate("State") ?></th>
            <th style='width: 1%;'><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'DESC');"><?php echo $this->translate('Creation Date'); ?></a></th>
            <th style='width: 1%;' class='admin_table_options'><?php echo $this->translate('Options'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if( !empty($counter)): ?>
            <?php foreach( $this->paginator as $item ): ?>
              <tr>
                <td class="admin_table_centered"><input name='delete_<?php echo $item->video_id;?>' type='checkbox' class='checkbox' value="<?php echo $item->video_id ?>"/></td>

                <td><?php echo $item->video_id ?></td>
                <?php             
                  $truncation_limit = 13;
                  $tmpBody = strip_tags($item->title);
                  $item_title = ( Engine_String::strlen($tmpBody) > $truncation_limit ? Engine_String::substr($tmpBody, 0, $truncation_limit) . '..' : $tmpBody );
                ?>
                <td class='admin_table_bold'><?php echo $this->htmlLink($item->getHref(), $item_title, array('title' => $item->title, 'target' => '_blank')) ?></td>

                <td class='admin_table_bold'><?php  echo $this->htmlLink($this->item('user', $item->owner_id)->getHref(), $item->truncateOwner($this->user($item->owner_id)->displayname), array('title' => $item->displayname, 'target' => '_blank')) ?></td>

                <?php             
                  $truncation_limit = 13;
                  $tmpBodytitle = strip_tags($item->sitereview_title);
                  $item_sitereviewtitle = ( Engine_String::strlen($tmpBodytitle) > $truncation_limit ? Engine_String::substr($tmpBodytitle, 0, $truncation_limit) . '..' : $tmpBodytitle );
                ?>	

                <td class='admin_table_bold'><?php echo $this->htmlLink($this->item('sitereview_listing', $item->listing_id)->getHref(),$item_sitereviewtitle, array('title' => $item->sitereview_title, 'target' => '_blank')) ?></td>

                <td align="center" class="admin_table_centered"><?php echo $item->view_count ?></td>

                <td align="center" class="admin_table_centered"><?php echo $item->comment_count ?></td>

                <td align="center" class="admin_table_centered"><?php echo $item->like_count ?></td>
                <td>
                  <?php
                    switch( $item->type ) {
                      case 1:
                        $type = $this->translate("YouTube");
                        break;
                      case 2:
                        $type = $this->translate("Vimeo");
                        break;
                      case 3:
                        $type = $this->translate("Uploaded");
                        break;
                      default:
                        $type = $this->translate("Unknown");
                        break;
                    }
                    echo $type;
                  ?>
                </td>
                <td>
                  <?php
                    switch ($item->status){
                      case 0:
                        $status = $this->translate("queued");
                        break;
                      case 1:
                        $status = $this->translate("ready");
                        break;
                      case 2:
                        $status = $this->translate("processing");
                        break;
                      default:
                        $status = $this->translate("failed");
                    }
                    echo $status;
                  ?>
                  <?php if($item->status == 2):?>
                  (<a href="javascript:void(0);" onclick="javascript:killProcess('<?php echo $item->video_id?>');">
                    <?php echo $this->translate("end"); ?>
                  </a>)
                  <?php endif;?>
                </td>
                <td align="center"><?php echo gmdate('M d,Y g:i A',strtotime($item->creation_date)) ?></td>

                <td class='admin_table_options'>

                  <?php echo $this->htmlLink($item->getHref(), 'view', array('target' => '_blank')) ?>

                  |
                  <?php echo $this->htmlLink(array('route' => 'admin_default','module'=>'sitereview','controller'=>'video','action' => 'delete', 'video_id' => $item->video_id,'listing_id' => $item->listing_id), $this->translate('delete'), array(
                    'class' => 'smoothbox',
                  )) ?> 
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <br />
      <?php  echo $this->paginationControl($this->paginator); ?><br  />
      <div class='buttons'>
        <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
      </div>  
    </form>
  <?php endif; ?>
<?php endif;?>
