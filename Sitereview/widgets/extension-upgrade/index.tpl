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
<?php $this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/scripts/admin/core.js');?>
<script type="text/javascript">
    function upgradePlugin(url) {
        Smoothbox.open(url);
    }
</script>

<h3>
    <?php
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) {
        $moduleTitle = 'Reviews & Ratings - Multiple Listing Types Plugin';
    } else {
        $moduleTitle = 'Reviews & Ratings Plugin';
    }
    $moduleTitle = 'Reviews & Ratings - Multiple Listing Types Plugin';
    ?>
    <?php echo 'Latest versions of Extensions for ' . $moduleTitle; ?>
</h3>
<p>
    <?php echo $this->translate('Here, you can upgrade the latest version of these extensions by using ‘Upgrade’ button available in front of all the desired extensions that needs to be upgraded.<br />The latest versions of Extensions for "' . $moduleTitle . '" are also available to you in your SocialEngineAddOns Client Area. Login into your SocialEngineAddOns Client Area here: <a href="http://www.socialengineaddons.com/user/login" target="_blank">http://www.socialengineaddons.com/user/login</a>.'); ?>
</p><br/>

<div class='sociealengineaddons_admin_tabs'>
    <?php
// Render the menu
    echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
</div>

<?php
if (count($this->OnSiteModules)):
    ?>
    <table class='admin_table'>
        <thead>
            <tr>
                <th align="left">
                    <?php echo $this->translate("Extension Title"); ?>
                </th>
                <th align="left">
                    <?php echo $this->translate("Latest version on SocialEngineAddOns.com"); ?>
                </th>
                <th align="left">
                    <?php echo $this->translate("Version on your website"); ?>
                </th>
                <th align="left">
                    <?php echo $this->translate("Should you Upgrade?"); ?>
                </th>
                <th align="left">
                    <?php echo $this->translate("Upgrade?"); ?>
                </th>
                <th align="left">
                    <input type="checkbox" class="seaocore_multi-upgrade_checkall" name="multi-upgrade" onclick="checkUncheckSeaoAll(this)">
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->channel as $item): ?>	
                <?php
                $running_version = $item['running_version'];
                $product_version = $item['product_version'];
                $versionInfo = 0;
                $status = $this->translate('No');
                $shouldUpgrade = FALSE;
                if (!empty($running_version) && !empty($product_version)) {
                    $isPlugin = true;
                    $verBool = Engine_Api::_()->seaocore()->checkVersion($product_version, $running_version);
                    if ($verBool == 1) {
                        $shouldUpgrade = TRUE;
                        $status = $this->translate('Yes');
                    }
                    ?>
                    <tr>
                        <td><?php echo $item['title']; ?></td>
                        <td><?php echo $product_version; ?></td>
                        <td><?php echo $running_version; ?></td>
                        <td><?php echo $status; ?></td>
                        <td>
                            <?php
                            $url = $this->url(array('module' => 'seaocore', 'controller' => 'settings', 'action' => 'upgrade-plugin', 'name' => @base64_encode($item['name']), 'version' => $item['product_version'], 'ptype' => $item['ptype'], 'key' => $item['key'], 'title' => str_replace("/", "_", @base64_encode($item['title'])), 'calling' => 'sitereview'), 'admin_default', true);
                            $title = $this->translate("Upgrade '%s' to latest version %s", $item['title'], $product_version);
                            if (empty($shouldUpgrade) || empty($this->mod_enabled)):
                                echo '-';
                            else:
                                ?>
                                <button title="<?php echo $title; ?>" style="font-size:11px;padding:2px;" onclick="upgradePlugin('<?php echo $url; ?>')">Upgrade</button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($shouldUpgrade)) : ?>
                                <input type="checkbox" class="seaocore_multi-upgrade" name="multi-upgrade" data-url='<?php echo $url; ?>'>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button title="Upgrade Selected" style="font-size:11px;padding:5px; float: right; margin: 10px" onclick="upgradeSelectedSeaoPlugins()">Upgrade Selected</button>
    <br />
<?php else: ?>
    <div class="tip">
        <span>     
            <?php echo $this->translate('There are no extensions of "$moduleTitle" available on your site. To see the available extensions for this plugin, please %1$sclick here%2$s', '<a href="http://www.socialengineaddons.com/catalog/advanced-events-extensions" target="_blank">', '</a>.'); ?>
        </span>
    </div>
<?php endif; ?>
