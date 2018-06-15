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

<?php
	$this->headLink()
        ->prependStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
?>

<script type="text/javascript">
  var dateAction =function(start_date, end_date, user_id){ 
    $('start_date').value = start_date;
    $('end_date').value = end_date;
    $('user_id').value = user_id;
    $('filter_form_archives').submit();
  }
</script>

<form id='filter_form_archives' class='global_form_box' method='get' action='<?php echo $this->url(array('action' => 'index'), 'sitereview_general_listtype_'.$this->sitereview->listingtype_id, true) ?>' style='display: none;'>
	<input type="hidden" id="start_date" name="start_date"  value=""/>
	<input type="hidden" id="end_date" name="end_date"  value=""/>
  <input type="hidden" id="user_id" name="user_id"  value=""/>
</form>

<?php if (count($this->archive_sitereview)): ?>
  <ul class="seaocore_sidebar_list">
    <?php foreach ($this->archive_sitereview as $archive): ?>
      <li>
        <a href='javascript:void(0);' onclick='javascript:dateAction(<?php echo $archive['date_start'] ?>, <?php echo $archive['date_end'] ?>, <?php echo $archive['user_id'] ?>);' <?php if ($this->start_date == $archive['date_start'])
        echo " class='bold'"; ?>><?php echo $archive['label'] ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
