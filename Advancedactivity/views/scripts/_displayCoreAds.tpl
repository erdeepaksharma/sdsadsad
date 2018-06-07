<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: _displayCoreAds.tpl 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
?>

<script type="text/javascript">
    en4.core.runonce.add(function () {
        var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'utility', 'action' => 'advertisement'), 'default', true) ?>';
        var processClick = window.processClick = function (adcampaign_id, ad_id) {
            (new Request.JSON({
                'format': 'json',
                'url': url,
                'data': {
                    'format': 'json',
                    'adcampaign_id': adcampaign_id,
                    'ad_id': ad_id
                }
            })).send();
        }
    });
</script>

<div onclick="javascript:processClick(<?php echo $this->campaign->adcampaign_id.", ".$this->ad->ad_id?>)">
  <?php echo $this->ad->html_code; ?>
</div>
