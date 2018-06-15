<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<script type="text/javascript">
	sm4.core.runonce.add(function() {
		sm4.core.map.initialize('map_canvas_<?php echo $this->subject()->getGuid() . "_" . $this->location->location_id ?>',{latitude:'<?php echo $this->location->latitude ?>',longitude:'<?php echo $this->location->longitude ?>',location_id:'<?php echo $this->location->location_id ?>',marker:true,title:'<?php echo $this->location->location; ?>',zoom:<?php echo $this->location->zoom; ?>});
	});
</script>
<div>
	<div class='profile_fields'>
		<div class="map_canvas_sitereview_browse" id="map_canvas_<?php echo $this->subject()->getGuid() . "_" . $this->location->location_id ?>"></div><br />
		<h4>
			<span><?php echo $this->translate('Location Information') ?></span>
		</h4>
		<div class="sm_ui_item_profile_details">
			<table>
				<tbody>
					<tr valign="top">
						<td class="label"><div><?php echo $this->translate('Location:'); ?> </div></td>
						<td><b><?php echo $this->location->location; ?></b></td>
					</tr>
					<?php if (!empty($this->location->formatted_address)): ?>
						<tr valign="top">
							<td class="label"><div><?php echo $this->translate('Formatted Address:'); ?> </div></td>
							<td><?php echo $this->location->formatted_address; ?> </td>
						</tr>
					<?php endif; ?>
					<?php if (!empty($this->location->address)): ?>
						<tr valign="top">
							<td class="label"><div><?php echo $this->translate('Street Address:'); ?> </div></td>
							<td><?php echo $this->location->address; ?> </td>
						</tr>
					<?php endif; ?>
					<?php if (!empty($this->location->city)): ?>
						<tr valign="top">
							<td class="label"><div><?php echo $this->translate('City:'); ?></div></td>
							<td><?php echo $this->location->city; ?> </td>
						</tr>
					<?php endif; ?>
					<?php if (!empty($this->location->zipcode)): ?>
						<tr valign="top">
							<td class="label"><div><?php echo $this->translate('Zipcode:'); ?></div></td>
							<td><?php echo $this->location->zipcode; ?> </td>
						</tr>
					<?php endif; ?>
					<?php if (!empty($this->location->state)): ?>
						<tr valign="top">
							<td class="label"><div><?php echo $this->translate('State:'); ?></div></td>
							<td><?php echo $this->location->state; ?></td>
						</tr>
					<?php endif; ?>
					<?php if (!empty($this->location->country)): ?>
						<tr valign="top">
							<td class="label"><div><?php echo $this->translate('Country:'); ?></div></td>
							<td><?php echo $this->location->country; ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<style type="text/css">
  .map_canvas_sitereview_browse {
    width: 100%;
    height: 200px;
  }
  .map_canvas_sitereview_browse > div{
    height: 200px;
  }
</style>