<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: edit.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php 
	$breadcrumb = array(
			array("href"=>$this->sitereview->getHref(),"title"=>$this->sitereview->getTitle(),"icon"=>"arrow-r"),
			array("href"=>$this->sitereview->getHref(array('tab' => $this->tab_selected_id)),"title"=>"Videos","icon"=>"arrow-d")
			);
	echo $this->breadcrumb($breadcrumb);
?>

<div class="layout_middle">
  <?php
  echo $this->form->render();
  ?>
</div>