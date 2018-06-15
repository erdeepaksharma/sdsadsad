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

<div>
  <div>
    <div class="sr_profile_overview">
      <?php echo $this->sitereview->body ?>
    </div>    

  </div>
</div>

<?php if( $this->showComments):?>


		<?php echo $this->content()->renderWidget("sitemobile.comments", array('type' => $this->sitereview->getType(), 'id' => $this->sitereview->getIdentity())); ?>
<?php endif;?>