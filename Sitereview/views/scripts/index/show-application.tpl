<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: show-application.tpl 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){
  
    var anchor = $('paginate_content').getParent();
    $('paginate_content_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    $('paginate_content_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';
   
    $('paginate_content_previous').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : '<?php echo $this->url(array('action' => 'show-application', 'listing_id'=>$this->listing_id), "sitereview_specific_listtype_$this->listingtype_id", true) ?>',
        method: 'get',
        data : {
          format : 'html',
          is_ajax: 1,
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    $('paginate_content_next').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : '<?php echo $this->url(array('action' => 'show-application', 'listing_id'=>$this->listing_id), "sitereview_specific_listtype_$this->listingtype_id", true) ?>',
        method: 'get',
        data : {
          format : 'html',
          is_ajax: 1,
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
  });
</script>


<script type="text/javascript">
  
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
  
  function multiDelete(){
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete selected application?")) ?>');
  }
  
  function showsmoothbox(url) {
    Smoothbox.open(url);
  } 
</script>

<?php if (empty($this->is_ajax)) : ?>
  <?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/scripts/_DashboardNavigation.tpl'; ?>
  <div class="sr_dashboard_content">
  <?php echo $this->partial('application/modules/Sitereview/views/scripts/dashboard/header.tpl', array('sitereview'=>$this->sitereview));?>
<div id="show_tab_content">
  <h3><?php echo $this->translate('Manage Applications') ?></h3><br />
      <p class="mbot10"><?php echo $this->translate("Below you can manage all the applications submitted for your Listing. Here, you can also view messages, download and delete applications by using appropriate links below."); ?></p>
<?php endif; ?> 

    <div class='sitereview_package_page mtop15'>
      
      <?php if (count($this->paginator)): ?>
        <?php  
        $paramsString = Engine_Api::_()->sitereview()->getWidgetparams();
        $params = !empty($paramsString) ? Zend_Json::decode($paramsString): array(); ?>
        <div class="sitereview_data_table product_detail_table fleft mbot10" id="paginate_content">
         <form id='multidelete_form' method="post" action="<?php echo $this->url(array('action' => 'multi-delete-application', 'listing_id' => $this->listing_id), "sitereview_dashboard_listtype_$this->listingtype_id");?>" onSubmit="return multiDelete()">
          <table class="mbot10">
            <tr class="product_detail_table_head">
              <th class='store_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
              <th><?php echo $this->translate("ID") ?></th>
              <?php if(in_array(1, $params['show_option'])):?>
                <th><?php echo $this->translate("Sender Name") ?></th>
              <?php endif;?>
              <?php if(in_array(2, $params['show_option'])):?>
                <th><?php echo $this->translate("Sender Email") ?></th>
              <?php endif;?>
              <?php if(in_array(3, $params['show_option'])):?>
                <th><?php echo $this->translate("Contact") ?></th>
              <?php endif;?>
              <th><?php echo $this->translate("Applying Date") ?></th>                
              <th><?php echo $this->translate("Options") ?></th>
            </tr>
            <?php foreach ($this->paginator as $item): ?>
              <tr>
                <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->job_id ?>' value="<?php echo $item->job_id ?>"></td>
                <td> <?php echo $item->job_id; ?> </td>
                <?php if(in_array(1, $params['show_option'])):?>
                  <td title="<?php echo $item->sender_name  ?>"><?php if($item->sender_name != ''):?><?php echo $this->string()->truncate($this->string()->stripTags($item->sender_name ), 15) ?><?php else:?>-<?php endif;?></td>    
                <?php endif;?>
                <?php if(in_array(2, $params['show_option'])):?>
                  <td title="<?php echo $item->sender_email  ?>"><?php if($item->sender_email != ''):?><?php echo $this->string()->truncate($this->string()->stripTags($item->sender_email ), 28) ?><?php else:?>-<?php endif;?></td>
                <?php endif;?>
                <?php if(in_array(3, $params['show_option'])):?>
                  <td><?php if(!empty($item->contact)):?><?php echo $item->contact;  ?><?php else:?>-<?php endif;?></td>
                <?php endif;?>
                <td><?php echo gmdate('M d,Y, g:i A',strtotime($item->creation_date)); ?></td>
                <td>
                  <a href="javascript:void(0);" onclick='showsmoothbox("<?php echo $this->url(array('action' => 'application-detail', 'listing_id' => $item->listing_id,'job_id' => $item->job_id), "sitereview_dashboard_listtype_$this->listingtype_id", true) ?>");return false;' ><?php echo $this->translate("details") ?></a> |
                  <?php if($item->file_id && in_array(4, $params['show_option'])): ?>   
                    <a href="<?php echo $this->url(array('action' => 'download-application', 'listing_id'=>$item->listing_id, 'job_id'=>$item->job_id), "sitereview_dashboard_listtype_$this->listingtype_id", true) ?>" target="downloadframe"><?php echo $this->translate('download') ?></a> |
                  <?php endif; ?>
                  <a href="javascript:void(0);" onclick='showsmoothbox("<?php echo $this->url(array('action' => 'delete-application', 'listing_id' => $item->listing_id,'id' => $item->job_id), "sitereview_dashboard_listtype_$this->listingtype_id", true) ?>");return false;' ><?php echo $this->translate("delete") ?></a>
                </td>
              </tr>
            <?php endforeach; ?>
          </table>
          <div class='buttons fleft'>
            <button type='submit' name="submit"><?php echo $this->translate("Delete Selected") ?></button>
            <span id="delete_selected_shipping_spinner"></span>
          </div>
          <br/>
        </form> 
      </div>
      <div>
       <div id="paginate_content_previous" class="paginator_previous">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
         'onclick' => '',
         'class' => 'buttonlink icon_previous'
        )); ?>
       </div>
       <div id="paginate_content_next" class="paginator_next">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
         'onclick' => '',
         'class' => 'buttonlink_right icon_next'
        )); ?>
       </div>
      </div>
      <?php else: ?>
      <div id="no_location_tip" class="tip">
        <span>
      <?php echo $this->translate("No member has applied for this listing yet.") ?>        
        </span>
      </div>
      <?php endif; ?>
      </div>
    <?php if (empty($this->is_ajax)) : ?>		
	</div>
</div>
  </div>
<?php endif; ?>
 