<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2009-2010 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: create.tpl 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>


<?php 
$breadcrumb = array(
    array("href"=>$this->sitereview->getHref(),"title"=>$this->sitereview->getTitle(),"icon"=>"arrow-r"),
    array("href"=>$this->sitereview->getHref(array('tab' => $this->content_id)),"title"=>"Review","icon"=>"arrow-d")
     );
echo $this->breadcrumb($breadcrumb);
?>


<?php echo $this->form->render($this) ?>

<?php include_once APPLICATION_PATH . '/application/modules/Sitereview/views/sitemobile/scripts/_formUpdateReview.tpl'; ?>