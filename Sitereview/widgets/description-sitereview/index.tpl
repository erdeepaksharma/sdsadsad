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

<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seaocore/externals/styles/style_comment.css'); ?>

<?php if ($this->loaded_by_ajax): ?>
  <script type="text/javascript">
    var params = {
      requestParams:<?php echo json_encode($this->params) ?>,
      responseContainer: $$('.layout_sitereview_description_sitereview')
    }
    en4.sitereview.ajaxTab.attachEvent('<?php echo $this->identity ?>', params);
  </script>
<?php endif; ?>

<?php if ($this->showContent): ?>
  <div>
    <div class="sr_profile_overview">
      <?php echo $this->sitereview->body ?>
    </div>    

  </div>
<?php endif; ?>

<?php
//CHECK IF THE FACEBOOK PLUGIN IS ENABLED AND ADMIN HAS SET ONLY SHOW FACEBOOK COMMENT BOX THEN WE WILL NOT SHOW THE SITE COMMENT BOX.
$fbmodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('facebookse');
$success_showFBCommentBox = 0;

$checkVersion = Engine_Api::_()->sitereview()->checkVersion($fbmodule->version, '4.2.7p1');
if (!empty($fbmodule) && !empty($fbmodule->enabled) && $checkVersion == 1) {

  $success_showFBCommentBox = Engine_Api::_()->facebookse()->showFBCommentBox('sitereview');
}
?>

<?php if (empty($this->isAjax) && $this->showComments && $success_showFBCommentBox != 1): ?>
  
  <?php 
        include_once APPLICATION_PATH . '/application/modules/Seaocore/views/scripts/_listNestedComment.tpl';
    ?>
    
<?php endif; ?>

<?php if (empty($this->isAjax) && $success_showFBCommentBox != 0): ?>
  <?php echo $this->content()->renderWidget("Facebookse.facebookse-comments", array("type" => $this->sitereview->getType(), "id" => $this->sitereview->listing_id, 'task' => 1, 'module_type' => 'sitereview', 'curr_url' => ( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $this->sitereview->getHref())); ?>
<?php endif; ?>  