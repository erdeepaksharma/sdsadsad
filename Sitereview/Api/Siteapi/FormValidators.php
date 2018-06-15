<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteapi
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    TopicController.php 2015-09-17 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Api_Siteapi_FormValidators extends Siteapi_Api_Validators {

    /**
     * Validations of Create OR Edit Form.
     * 
     * @param object $subject get object
     * @param array $formValidators array variable
     * @return array
     */
    public function getVideoCreateFormValidators($subject = array(), $formValidators = array()) {
        $formValidators['title'] = array(
            'required' => true,
            'allowEmpty' => false,
            'validators' => array(array('NotEmpty', true), array('StringLength', false, array(3, 63)))
        );
        if (empty($subject)) {
            $formValidators['type'] = array(
                'required' => true,
                'allowEmpty' => false
            );
        }


        return $formValidators;
    }

    public function getListingCreateFormValidators($listingtypeArray = array(), $formValidators = array()) {
        $formValidators['title'] = array(
            'required' => true,
            'allowEmpty' => false,
            'validators' => array(array('NotEmpty', true), array('StringLength', false, array(3, 63)))
        );
        $formValidators['category_id'] = array(
            'required' => true,
            'allowEmpty' => false,
        );
        if ($listingtypeArray->body_allow) {
            if (!empty($listingtypeArray->body_required)) {
                $formValidators['body'] = array(
                    'required' => true,
                    'allowEmpty' => false,
                    'filters' => array(new Engine_Filter_Censor()),
                );
            }
            $formValidators['body'] = array(
                'filters' => array(new Engine_Filter_Censor()),
            );
        }

        return $formValidators;
    }

    public function getPhotoEditValidators($subject = array(), $formValidators = array()) {
        $formValidators['title'] = array(
            'validators' => array(
                array('StringLength', false, array(3, 63))
            )
        );

        return $formValidators;
    }

    public function getReviewCreateFormValidators($widgetSettingsReviews) {

        $getItemEvent = $widgetSettingsReviews['item'];
        $siteevent_proscons = $widgetSettingsReviews['settingsReview']['siteevent_proscons'];
        $siteevent_limit_proscons = $widgetSettingsReviews['settingsReview']['siteevent_limit_proscons'];
        $siteevent_recommend = $widgetSettingsReviews['settingsReview']['siteevent_recommend'];
        if ($siteevent_proscons) {
            if ($siteevent_limit_proscons) {
                $formValidators['pros'] = array(
                    'allowEmpty' => false,
                    'maxlength' => $widgetSettingsReviews['siteevent_limit_proscons'],
                    'required' => true,
                    'filters' => array(
                        'StripTags',
                        new Engine_Filter_Censor(),
                        new Engine_Filter_HtmlSpecialChars(),
                        new Engine_Filter_EnableLinks(),
                    ),
                );
            } else {
                $formValidators['pros'] = array(
                    'allowEmpty' => false,
                    'required' => true,
                    'filters' => array(
                        'StripTags',
                        new Engine_Filter_Censor(),
                        new Engine_Filter_HtmlSpecialChars(),
                        new Engine_Filter_EnableLinks(),
                    ),
                );
            }
            if ($siteevent_limit_proscons) {
                $formValidators['cons'] = array(
                    'allowEmpty' => false,
                    'maxlength' => $widgetSettingsReviews['siteevent_limit_proscons'],
                    'required' => true,
                    'filters' => array(
                        'StripTags',
                        new Engine_Filter_Censor(),
                        new Engine_Filter_HtmlSpecialChars(),
                        new Engine_Filter_EnableLinks(),
                    ),
                );
            } else {
                $formValidators['cons'] = array(
                    'allowEmpty' => false,
                    'required' => true,
                    'filters' => array(
                        'StripTags',
                        new Engine_Filter_Censor(),
                        new Engine_Filter_HtmlSpecialChars(),
                        new Engine_Filter_EnableLinks(),
                    ),
                );
            }
        }
    }

    public function getReviewUpdateFormValidators() {
        $formValidators['body'] = array(
            'allowEmpty' => true,
            'required' => false,
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_HtmlSpecialChars(),
                new Engine_Filter_EnableLinks(),
            ),
        );
        return $formValidators;
    }

    /**
     * Validation: sitereview signup field form
     * 
     * @return array
     */
    public function getFieldsFormValidations($values) {
        $option_id = $values['profile_type'];

        $mapData = Engine_Api::_()->getApi('core', 'fields')->getFieldsMaps('sitereview_listing');
        $getRowsMatching = $mapData->getRowsMatching('option_id', $option_id);
        $fieldArray = array();
        $getFieldInfo = Engine_Api::_()->fields()->getFieldInfo();
        foreach ($getRowsMatching as $map) {
            $meta = $map->getChild();
            $type = $meta->type;
             $label = $meta->label;
            if (!empty($type) && ($type == 'heading'))
                continue;

            $fieldForm = $getMultiOptions = array();
            $key = $map->getKey();

            if (!empty($meta->alias))
                $key = $key . '_' . ( $meta->alias ? 'alias_' . $meta->alias : sprintf('field_%d', $meta->alias->field_id) );
            else {
                $key = $key . '_' . 'field_' . $meta->field_id;
            }
            if (isset($meta->required) && !empty($meta->required))
                $fieldArray[$key] = array(
                    'required' => true,
                    'label'=>$label,
                    'allowEmpty' => false
                );

            if (isset($mets->validators) && !empty($mets->validators)) {
                $fieldArray[$key]['validators'] = $mets->validators;
            }
        }
        return $fieldArray;
    }

    public function getClaimListingFormValidators() {
        $formValidators['nickname'] = array(
            'allowEmpty' => true,
            'required' => false,
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '63')),
            ),
        );

        $formValidators['email'] = array(
            'allowEmpty' => false,
            'required' => true,
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '63')),
        ));
        $formValidators['about'] = array(
            'allowEmpty' => false,
            'required' => true,
            'filters' => array(
                'StripTags',
                new Engine_Filter_HtmlSpecialChars(),
                new Engine_Filter_EnableLinks(),
                new Engine_Filter_Censor(),
        ));
        $formValidators['terms'] = array(
            'allowEmpty' => false,
            'required' => true,
        );
        
        return $formValidators;
    }

}
