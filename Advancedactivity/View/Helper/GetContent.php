<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: GetContent.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_View_Helper_GetContent extends Zend_View_Helper_Abstract {

    /**
     * Assembles action string
     * 
     * @return string
     */
    public function getContent($action, $asAttachment = false, $groupedFeeds = array(), $params = array()) {

        $view = Zend_Registry::get('Zend_View');
        $model = Engine_Api::_()->getApi('activity', 'advancedactivity');
        $similarActivities = $params['similarActivities'];
        $params = array_merge(
                $action->toArray(), (array) $action->params, array(
            'action' => $action,
            'subject' => $action->getSubject(),
            'object' => $action->getObject(),
            'action' => $action,
                )
        );
        $similarFeedType = $action->type . '_' . $action->getObject()->getGuid();
        $params['otherItems'] = $otherItems = array();
        if (!empty($similarActivities) && isset($similarActivities[$similarFeedType])) {
            $actionSubject = $action->getSubject();
            foreach ($similarActivities[$similarFeedType] as $activity) {
                $activitySubject = $activity->getSubject();
                if ($activity->getSubject() === $actionSubject) {
                    continue;
                }
                $otherItems[$activitySubject->getGuid()] = $activitySubject;
            }
            $params['otherItems'] = $otherItems;
        }
        $body = $action->getTypeInfo()->body;
        $body = trim(preg_replace('/[\r\n]+/', "", $body));
        if (count($params['otherItems']) > 0) {
            $otherText = '{item:$subject} and {others:$otherItems}';
            $translate = Zend_Registry::get('Zend_Translate');
            if ($translate instanceof Zend_Translate) {
                $body = $translate->translate($body);
                $otherText = $translate->translate($otherText);
            }
            $body = str_replace('{item:$subject}', $otherText, $body);
        }
        if (!empty($params['feelings'])) {
            $feelingContent = Engine_Api::_()->advancedactivity()->getFeelingContent($action);
            $params['feelingText'] = $feelingContent;
            if($action->type == 'sitetagcheckin_checkin'){
               $body = str_replace('{item:$subject} is', '{item:$subject} is {varFeeling:$feelingText} ', $body);
            }else{
               $body = str_replace('{item:$subject}', '{item:$subject} is {varFeeling:$feelingText}.', $body); 
            }
            
            $body = str_replace('{actors:$subject:$object}', '{actors:$subject:$object} is {varFeeling:$feelingText}', $body);
        }
        $params['body'] = '';
        $content = $model->assemble($body, $params);
        if (Engine_Api::_()->hasModuleBootstrap('siteevent')) {
            $content = $this->view->getEventContent($action, $content);
        }
        if (!$asAttachment && !empty($groupedFeeds)) {
            $content = $this->makeGroupFeed($action, $content, $groupedFeeds);
        }
        $allowType = array(
            'sitetagcheckin_checkin',
            'comment_sitereview_listing',
            'comment_sitereview_review',
            'nestedcomment_sitereview_listing',
            'nestedcomment_sitereview_review',
            'sitetagcheckin_add_to_map',
            'sitetagcheckin_content',
            'sitetagcheckin_lct_add_to_map'
        );
        if (false === strpos($action->type, 'post') && false !== strpos($action->type, 'status') && false !== strpos($action->type, 'photo') && !in_array($action->type, $allowType)) {
            return $content;
        }
        $composerOptions = $this->view->settings('advancedactivity.composer.options', array("emotions", "withtags"));
        if (empty($composerOptions)) {
            $composerOptions = array();
        }
        if (!$asAttachment && in_array("withtags", $composerOptions)) {
            $tagContent = Engine_Api::_()->advancedactivity()->getTagContent($action);
            $content .= $tagContent;
        } 
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitetagcheckin')) {
            $content = $this->view->getSitetagCheckin($action, $content);
        }
        return $content;
    }

    private function makeGroupFeed($action, $content, $groupedFeeds) {
        $count = count($groupedFeeds);
        if ($count < 2) {
            return $content;
        }
        if ($action->type == 'friends' || $action->type == 'tagged') {
            $subject = $action->getObject();
            $id = $action->getSubject()->getIdentity();
        } else {
            $subject = $action->getSubject();
            $id = $action->getObject()->getIdentity();
        }
        $removePattern = '<a '
                . 'class="notranslate feed_item_username sea_add_tooltip_link feed_user_title" '
                . 'rel="' . $subject->getType() . ' ' . $subject->getIdentity() . '" '
                . ( $subject->getHref() ? 'href="' . $subject->getHref() . '"' : '' )
                . '>'
                . $subject->getTitle()
                . '</a>';
        $otherids = array();
        $gp = array();
        foreach ($groupedFeeds as $groupedFeed) {
            $gp[] = $groupedFeed;
        }
        for ($i = 0; $i < count($gp) - 1; $i++) {
            $otherids[] = $gp[$i]->getIdentity();
        }
        $ids = http_build_query(array("type" => $action->type, "ids" => $otherids), '', '&');
        if ($count == 2) {
            $new_pattern = $this->view->translate('%1$s and %2$s ', $this->view->htmlLink($gp['0']->getHref(), $gp['0']->getTitle(), array('class' => 'sea_add_tooltip_link feed_user_title notranslate feed_item_username', 'rel' => $subject->getType() . ' ' . $gp['0']->getIdentity())), $this->view->htmlLink($gp['1']->getHref(), $gp['1']->getTitle(), array('class' => 'sea_add_tooltip_link feed_user_title feed_item_username', 'rel' => $subject->getType() . ' ' . $gp['1']->getIdentity())));
        } else {
            $URL = $this->view->url(array('module' => 'advancedactivity', 'controller' => 'feed', 'action' => 'groupfeed-other-post'), 'default', true) . "?$ids";
            $otherPeoples = '<span class="aaf_feed_show_tooltip_wrapper"><a href=' . $URL . ' class="smoothbox">' . $this->view->translate('%s others', ($count - 1)) . '</a><span class="aaf_feed_show_tooltip" style="margin-left:-8px;"><img src="' . $this->view->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/tooltip_arrow.png" />';
            for ($i = 1; $i < count($gp); $i++) {
                $otherPeoples .= $gp[$i]->getTitle() . "<br />";
            }
            $otherPeoples .= '</span></span>';
            $new_pattern = $this->view->translate('%1$s and %2$s ', $this->view->htmlLink($subject->getHref(), $subject->getTitle(), array('class' => 'sea_add_tooltip_link feed_user_title notranslate feed_item_username', 'rel' => $subject->getType() . ' ' . $subject->getIdentity())), $otherPeoples);
        }
        if (strpos($action->type, "like_") !== false) {
            $removePattern = $removePattern . $this->view->translate(' likes');
            $new_pattern = $new_pattern . $this->view->translate('like ');
        }
        if (strpos($action->type, "follow_") !== false) {
            $removePattern = $removePattern . $this->view->translate(' is');
            $new_pattern = $new_pattern . $this->view->translate('are ');
        }
        return str_replace($removePattern, $new_pattern, $content);
    }

}
