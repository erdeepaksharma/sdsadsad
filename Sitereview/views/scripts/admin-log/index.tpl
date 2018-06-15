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

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>
<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?> </div>
<?php endif; ?>

<h3><?php echo $this->translate("Import History Browser") ?></h3>

<p>
  <?php echo $this->translate("Import history is helpful for troubleshooting and debugging. You can track a complete importing process over here. Select the history you would like to view below.") ?>
</p>

<br />

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'index'), $this->translate('Import a new file'), array('class' => 'buttonlink icon_sitereview_admin_import')) ?>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'manage'), $this->translate('Manage CSV Import Files'), array('class' => 'buttonlink icon_sitereview_admin_import_manage')) ?>
<br /><br />

<script type="text/javascript">
  window.addEvent('domready', function() {
    var el = $$('.admin_logs')[0];
    if( el ) {
      el.scrollTo(0, el.getScrollSize().y);
    }
    $('clear').addEvent('click', function() {
      if( $('file').get('value').trim() == '' ) {
        return;
      }
      var url = '<?php echo $this->url() ?>?clear=1';
      url += '&file=' + encodeURI($('file').get('value'));
      $('filter_form')
        .set('action', url)
        .set('method', 'POST')
        .submit();
        ;
    });
    $('download').addEvent('click', function() {
      if( $('file').get('value').trim() == '' ) {
        return;
      }
      var url = '<?php echo $this->url(array('action' => 'download')) ?>';
      url += '?file=' + encodeURI($('file').get('value'));
      (new IFrame({
        src : url,

        styles : {
          display : 'none'
        }
      })).inject(document.body);
    });
  });
</script>

<?php if( !empty($this->formFilter) ): ?>
  <div class="admin_search">
    <div class="search">
      <?php echo $this->formFilter->render($this) ?>
    </div>
  </div>

  <br />
<?php endif; ?>

<?php if( $this->error ): ?>
  <ul class="form-notices">
    <li>
      <?php echo $this->error ?>
    </li>
  </ul>
<?php endif; ?>

<?php if( !empty($this->logText) ): ?>

  <div class="admin_logs_container">

    <div class="admin_logs_info">
      <?php echo $this->translate(
        'Showing the last %1$s lines, %2$s bytes from the end. The file\'s size is %3$s bytes.',
        $this->locale()->toNumber($this->logLength),
        $this->locale()->toNumber($this->logSize - $this->logOffset),
        $this->locale()->toNumber($this->logSize)
      ) ?>
    </div>
    <br />

    <div class="admin_logs">
      <pre><?php echo $this->logText ?></pre>
    </div>
    
  </div>
<?php endif; ?>