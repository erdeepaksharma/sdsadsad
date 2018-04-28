<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?><ul class="seaocore_sidebar_list" id="browse_siteforum_tagsCloud">
    <li>
        <div>
            <?php foreach ($this->tag_array as $key => $frequency): ?>
                <?php $string = $this->string()->escapeJavascript($key); ?>
                <?php $step = $this->tag_data['min_font_size'] + ($frequency - $this->tag_data['min_frequency']) * $this->tag_data['step'] ?>
                <a href='<?php echo $this->url(array('action' => 'search'), "siteforum_general"); ?>?tag=<?php echo urlencode($key) ?>&tag_id=<?php echo $this->tag_id_array[$key] ?>' style="font-size:<?php echo $step ?>px;" title=''><?php echo $key ?><sup><?php echo $frequency ?></sup></a> 
            <?php endforeach; ?>
        </div>		
    </li>
    <?php if (!$this->notShowExploreTags) : ?>
        <li>
            <a class="more_link" href="<?php echo $this->url(array('controller' => 'index', 'action' => 'tags-cloud'), 'siteforum_general'); ?>"><?php echo $this->translate('Explore More &raquo;'); ?></a>
        </li>
    <?php endif; ?>
</ul>

