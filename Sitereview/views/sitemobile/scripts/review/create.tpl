<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: create.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>

<?php if (!Engine_Api::_()->seaocore()->isSitemobileApp()):
$breadcrumb = array(
    array("href"=>$this->sitereview->getHref(),"title"=>$this->sitereview->getTitle(),"icon"=>"arrow-r"),
    array("href"=>$this->sitereview->getHref(array('tab' => $this->content_id)),"title"=>"Review","icon"=>"arrow-d")
     );
echo $this->breadcrumb($breadcrumb);
endif;?>


<?php echo $this->form->render($this) ?>

<?php include APPLICATION_PATH . '/application/modules/Sitereview/views/sitemobile/scripts/_formCreateReview.tpl'; ?>