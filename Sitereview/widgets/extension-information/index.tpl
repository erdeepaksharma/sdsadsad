<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<style type="text/css">
ul.seaocores_browse *
{
	font-family:arial;
}
ul.seaocores_browse > li
{
  clear: both;
  overflow: hidden;
  padding-bottom: 15px;
}
ul.seaocores_browse > li + li
{
  padding-top: 15px;
  border-top-width: 1px;
}
ul.seaocores_browse .seaocores_photo
{
  float: left;
  overflow: hidden;
}
ul.seaocores_browse .seaocores_photo img
{
  width: 100px;
  display: block;
}
ul.seaocores_browse .seaocores_info
{
  padding-left: 10px;
  overflow: hidden;
}
ul.seaocores_browse .seaocores_title h3
{
  margin: 0px;
  color:#5F93B4;
}
ul.seaocores_browse .seaocores_title h3 a
{
  color:#5F93B4;
  font-weight:bold;
}
ul.seaocores_browse .seaocores_stat
{
  font-size: .8em;
}
ul.seaocores_browse .seaocores_stat b
{
	font-weight:bold;
}
ul.seaocores_browse .seaocores_desc
{
  margin-top: 5px;
  clear: both;
}
ul.seaocores_browse .seaocores_options
{
  float: right;
  overflow: hidden;
  width:200px;
  padding-left: 15px;
}
ul.seaocores_browse .seaocores_options a
{
  clear: both;
  margin: 5px 0px 0px 0px;
  padding-top: 1px;
  height: 16px;
  font-size:11px;
  font-weight:bold;
  float:left;
  background-repeat:no-repeat;
  padding-left:22px;
  color:#5F93B4;
}
ul.seaocores_browse .seaocores_options a.seaocores_type_photo
{
  background-image: url(<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/photo.png);
}
ul.seaocores_browse .seaocores_options a.seaocores_type_seaddons
{
  background-image: url(<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/seaocore-icon.png);
}
ul.seaocores_browse .seaocores_options a.seaocores_type_se
{
  background-image: url(<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Seaocore/externals/images/se-icon.png);
}
</style>

<?php
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/slimbox.js');
?>

<h3>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo 'Reviews & Ratings - Multiple Listing Types Plugin'; } else { echo 'Reviews & Ratings Plugin'; }?>
	<?php echo " Information"; ?>
</h3>
<br />
<div class="admin_search">
  <div class="clear">
    <div class="search">
      <form method="post" action="" class="global_form_box" enctype="" id="filter_form" name="form1">
        <div style="padding-top:5px;">
          <label class="" tag="" for="level_id">Extensions :</label>
        </div>
        <div>  
					<?php 
						if( $this->show_table == 1 ) {  $all_plugin = 'selected'; }
						else if( $this->show_table == 2 ) { $install_plugin = 'selected';  }
						else if( $this->show_table == 3 ) { $notinstall_plugin = 'selected'; }
					?>
          <select id="level_id" name="level_id" onchange="document.form1.submit();">
            <option label="" value="1" <?php if( !empty($all_plugin) ) { echo $all_plugin; } ?>><?php echo $this->translate('All Multiple Listing Types Extensions'); ?> </option>
            <option label="" value="2" <?php if( !empty($install_plugin) ) { echo $install_plugin; } ?> ><?php echo $this->translate('Extensions installed on your site') ?></option>
            <option label="" value="3" <?php if( !empty($notinstall_plugin) ) { echo $notinstall_plugin; } ?> ><?php echo $this->translate('Extensions not installed on your site') ?></option>
          </select>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="tabs" style="height:10px;"></div>

<ul class='seaocores_browse'>
	<?php $product_id = 1; ?>

	<?php 
		// No plugin installed of 'SocialEngineAddOns' in the site then show the msg.
		if( !empty($install_plugin) ) {
			if( empty($this->channel) ) {
		echo '<div class="tip"><span>No plugins by SocialEngineAddOns were found on your site. Click <a href="http://www.socialengineaddons.com/catalog/1/plugins" target="_blank">here</a> to view and purchase them.</span></div>';
			}
		}  
		?>
		<?php foreach( $this->channel as $item ): ?>
			<?php if( !empty($item) ) {
			if( empty($item['running_version']) ) {
				$should_do = '<a href="'.$item['link'].'" class="seaocores_type_seaddons" target="_blank">' . $this->translate('Purchase and Download') . '</a>';
				$running_version = 0;
			} else {
					$running_version = $item['running_version'];
					$product_version = $item['product_version'];
					$versionInfo = strcasecmp($running_version, $product_version);
					if( $versionInfo < 0 ) {
					$should_do = '<a href="http://www.socialengineaddons.com/user" class="seaocores_type_seaddons" target="_blank">' . $this->translate('Download Latest Version') . '</a>';
				} else {
					$should_do = '<a href="'.$item['link'].'" class="seaocores_type_seaddons" target="_blank">' . $this->translate('View') . '</a>';
				}
				$running_version = $item['running_version'];
			}
			?>
		<li>
			<div class="seaocores_photo">
			<?php echo '<a rel="lightbox-atomium_'.$product_id.'" title="'.$item['title'].'" href="'.$item['image'][0].'"><img src="'.$item['image'][0].'" width="50" /></a>';
			$check_image = 0;
			foreach( $item['image'] as $image ) {
				if ( !empty($check_image) ) {
					echo '<a rel="lightbox-atomium_'.$product_id.'" title="'.$item['title'].'" href="'.$image.'"></a>';
				} $check_image ++;
			}
		?>
			</div>
			<div class="seaocores_options">
				<?php
					if ( !empty($item['image']) ) {
						echo '<a class="seaocores_type_photo" rel="lightbox-atomium_'.$product_id.'" title="'.$item['title'].'" href="'.$item['image'][0].'"> ' . $this->translate('Photos') . ' </a>';
						$check_image = 0;
						foreach( $item['image'] as $image ) {
							if ( !empty($check_image) ) {
								echo '<a rel="lightbox-atomium_'.$product_id.'" title="'.$item['title'].'" href="'.$image.'" style="display:none;"></a>';
							} $check_image ++;
						}
					}
				?>
				<?php echo '<a href="http://demo.socialengineaddons.com" class="seaocores_type_seaddons" target="_blank">' . $this->translate('Demo') . '</a>'; ?>
				<?php if ( !empty($should_do) ) { echo $should_do; } ?>
				<?php echo '<a href="'.$item['socialengine_url'].'" class="seaocores_type_se" target="_blank">' . $this->translate('SocialEngine Plugin Group') . '</a>'; ?>
			</div>
			<div class="seaocores_info">
				<div class="seaocores_title">
					<h3><a href="<?php echo $item['link'] ?>" target="_blank"><?php echo $item['title'] ?></a></h3>
				</div>
				<div class="seaocores_stat">
					<?php 
						if (!empty($item['product_version']) && !empty($running_version)) {
							$show_label = Zend_Registry::get('Zend_Translate')->_('Current Product Version: <b>%s</b>');
							$show_label = sprintf($show_label, $item['product_version']);
							echo $show_label; 
						} ?>
				</div>
				<div class="seaocores_stat">
					<?php
						if (!empty($running_version)) {
							$show_label = Zend_Registry::get('Zend_Translate')->_('Running Version: <b>%s</b>');
							$show_label = sprintf($show_label, $running_version);
							echo $show_label; 
						} ?>
				</div>
				<div class="seaocores_stat">
					<?php
						if (!empty($item['key'])) {
							$show_label = Zend_Registry::get('Zend_Translate')->_('Key: <b>%s</b>');
							$show_label = sprintf($show_label, $item['key']);
							echo $show_label; 
						} ?>
				</div>
				<div class="seaocores_stat">
					<?php
						if (!empty($item['price'])) {
							$show_label = Zend_Registry::get('Zend_Translate')->_('Price: <b>%s</b>');
							$show_label = sprintf($show_label, $item['price']);
							echo $show_label; 
						} ?>
				</div>	
				
				<div class="seaocores_desc">
					<?php echo $item['description'] . '<a href="' . $item['link'] . '" target="_blank">More >></a>'; ?>
				</div>
			</div>
		</li>
	<?php  } $product_id++; ?>
	<?php endforeach; ?>  
</ul>

<style type="text/css">
  #filter_form select {
    width: 250px;
  }
</style>
