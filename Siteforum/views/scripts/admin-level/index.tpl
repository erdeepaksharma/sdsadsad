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
?>
<h2><?php echo "Advanced Forums Plugin" ?></h2>

<script type="text/javascript">
    var fetchLevelSettings = function (level_id) {
        window.location.href = en4.core.baseUrl + 'admin/siteforum/level/index/id/' + level_id;
    }
</script>

<div class='clear'>

    <?php if (count($this->navigation)): ?>
        <div class='tabs'>
            <?php
            // Render the menu
            //->setUlClass()
            echo $this->navigation()->menu()->setContainer($this->navigation)->render()
            ?>
        </div>
    <?php endif; ?>

    <div class='settings'>
        <?php echo $this->form->render($this) ?>
    </div>

</div>