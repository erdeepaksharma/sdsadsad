<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: _formSubcategory.tpl 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
?>
<?php
$cateDependencyArray = Engine_Api::_()->getDbTable('categories', 'siteforum')->getCatDependancyArray();

$forum_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('forum_id');

if (!empty($forum_id)) {
    $forum = Engine_Api::_()->getItem('forum_forum', $forum_id);
}
?>

<?php
echo "
	<div id='subcategory_backgroundimage' class='form-wrapper'></div>
	<div id='subcategory_id-wrapper' class='form-wrapper' style='display:none;'>
		<div id='subcategory_id-label' class='form-label'>
		 	<label for='subcategory_id' class='optional'>" . $this->translate('Subcategory') . "</label>
		</div>
		<div id='subcategory_id-element' class='form-element'>
			<select name='subcategory_id' id='subcategory_id'>
			</select>
		</div>
	</div>";
?>

<script type="text/javascript">
    var cateDependencyArray = '<?php echo json_encode($cateDependencyArray); ?>';
    var previous_mapped_level = 0;
    var sub = '';
    var subcatname = '';
    var show_subcat = 1;
<?php if (!empty($this->siteforum->category_id)) : ?>
        show_subcat = 0;
<?php endif; ?>

    var subcategories = function (category_id, sub, subcatname)
    {
        document.getElementById('subcategory_id-wrapper').style.display = 'none';

        if (cateDependencyArray.indexOf(category_id) == -1 || category_id == 0) {
            return;
        }
        document.getElementById('subcategory_id-wrapper').style.display = 'block';
        if (document.getElementById('buttons-wrapper')) {
            document.getElementById('buttons-wrapper').style.display = 'none';
        }
        var url = '<?php echo $this->url(array('module' => 'siteforum', 'controller' => 'index', 'action' => 'subcat'), "default", true); ?>';
        document.getElementById('subcategory_backgroundimage').style.display = 'block';
        document.getElementById('subcategory_id').style.display = 'none';

        if (document.getElementById('subcategory_id-label'))
            document.getElementById('subcategory_id-label').style.display = 'none';

        document.getElementById('subcategory_backgroundimage').innerHTML = '<div class="form-label"></div><div class="form-element"><img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif" alt="" /></div>';

        en4.core.request.send(new Request.JSON({
            url: url,
            data: {
                format: 'json',
                category_id_temp: category_id
            },
            onSuccess: function (responseJSON) {
                if (document.getElementById('buttons-wrapper')) {
                    document.getElementById('buttons-wrapper').style.display = 'block';
                }
                document.getElementById('subcategory_backgroundimage').style.display = 'none';
                clear(document.getElementById('subcategory_id'));
                var subcatss = responseJSON.subcats;

                addOption(document.getElementById('subcategory_id'), " ", '0');
                for (i = 0; i < subcatss.length; i++) {
                    addOption(document.getElementById('subcategory_id'), subcatss[i]['category_name'], subcatss[i]['category_id']);
                    if (show_subcat == 0) {
                        document.getElementById('subcategory_id').disabled = 'disabled';
                    }
                    document.getElementById('subcategory_id').value = sub;
                }

                if (category_id == 0) {
                    clear(document.getElementById('subcategory_id'));
                    document.getElementById('subcategory_id').style.display = 'none';
                    if (document.getElementById('subcategory_id-label'))
                        document.getElementById('subcategory_id-label').style.display = 'none';
                }
            }
        }), {
            "force": true
        });
    };

    function clear(ddName)
    {

        if (ddName) {
            for (var i = (ddName.options.length - 1); i >= 0; i--)
            {
                ddName.options[ i ] = null;
            }
        }
    }

    function addOption(selectbox, text, value)
    {

        var optn = document.createElement("OPTION");
        optn.text = text;
        optn.value = value;

        if (optn.text != '' && optn.value != '') {
            document.getElementById('subcategory_id').style.display = 'block';
            if (document.getElementById('subcategory_id-wrapper'))
                document.getElementById('subcategory_id-wrapper').style.display = 'block';
            selectbox.options.add(optn);
        } else {
            document.getElementById('subcategory_id').style.display = 'none';
            if (document.getElementById('subcategory_id-wrapper'))
                document.getElementById('subcategory_id-wrapper').style.display = 'none';
            if (document.getElementById('subcategory_id-label'))
                document.getElementById('subcategory_id-label').style.display = 'none';
            selectbox.options.add(optn);
        }
    }

    var cat = '<?php echo (empty($forum) ? '' : $forum->category_id); ?>';
    if (cat != '') {
        sub = '<?php echo $forum->subcategory_id; ?>';
//        subcatname = '<?php //echo $this->subcategory_name;          ?>';
        subcategories(cat, sub, '');
    }
</script>
