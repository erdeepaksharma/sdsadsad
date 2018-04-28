<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: user-search.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php if (count($this->paginator) > 1): ?>
    <?php echo "Your search returned too many results; only displaying the first 20." ?>
<?php endif; ?>
<?php if(count($this->paginator) == 0):?>
<div class='tip'>
    <span>
      <?php echo "There are no members matching your search criteria."; ?>
    </span>
</div>
<?php else:?>
<?php foreach ($this->paginator as $user): ?>
    <?php if (!$this->siteforum->isModerator($user)): ?>
        <li>
            <a href='javascript:addModerator(<?php echo $user->getIdentity(); ?>);'><?php echo $user->getTitle(); ?></a>
        </li>
    <?php endif; ?>
<?php endforeach; ?>
<?php endif;?>