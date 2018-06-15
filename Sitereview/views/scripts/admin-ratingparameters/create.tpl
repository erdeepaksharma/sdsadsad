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

<div class="settings sr_parameters_popup">
  
  <?php echo $this->form->setAttrib('class', 'global_form')->render($this) ?>
  <a href="javascript: void(0);" onclick="return addAnotherOption();" id="addOptionLink"><?php echo $this->translate("Add More Parameters") ?></a>
  <script type="text/javascript">
    //<!--
    en4.core.runonce.add(function() {
      var maxOptions = 100;
      var options = <?php echo Zend_Json::encode($this->options) ?>;
      var optionParent = $('options').getParent();

      var addAnotherOption = window.addAnotherOption = function (dontFocus, label) {
        if (maxOptions && $$('input.ratingOptionInput').length >= maxOptions) {
          return !alert(new String('<?php echo $this->string()->escapeJavascript($this->translate("A maximum of %s parameters are permitted.")) ?>').replace(/%s/, maxOptions));
          return false;
        }

        var optionElement = new Element('input', {
          'type': 'text',
          'name': 'optionsArray[]',
          'class': 'ratingOptionInput',
          'value': label
        });
        
        if( dontFocus ) {
          optionElement.inject(optionParent);
        } else {
          optionElement.inject(optionParent).focus();
        }

        $('addOptionLink').inject(optionParent);

        if( maxOptions && $$('input.ratingOptionInput').length >= maxOptions ) {
          $('addOptionLink').destroy();
        }
      }
      
      // Do stuff
      if( $type(options) == 'array' && options.length > 0 ) {
        options.each(function(label) {
          addAnotherOption(true, label);
        });
        if( options.length == 1 ) {
          addAnotherOption(true);
        }
      } else {
        // display two boxes to start with
        addAnotherOption(true);
        //addAnotherOption(true);
      }
    });
    // -->
  </script>  
  
</div>

<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>