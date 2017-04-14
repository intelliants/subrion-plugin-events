<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
    {preventCsrf}

            {capture name="title" append="field_after"}
                <div class="row">
                    <label class="col col-lg-2 control-label" for="input-category">{lang key='category'}</label>
                    <div class="col col-lg-4">
                        <select name="category_id" id="input-category">
                            <option value="0">{lang key='_select_'}</option>
                            {html_options options=$categories selected=$item.category_id}
                        </select>
                    </div>
                </div>
            {/capture}

            {capture name="image" append="field_after"}
                <div class="row">
                    <label class="col col-lg-2 control-label" for="input-language">{lang key='venue'}</label>
                    <div class="col col-lg-4">
                        <input id="venue" type="text" name="venue" class="common" value="{$item.venue|escape:'html'}" maxlength="255">
                    </div>
                </div>

                <div class="row hidden" id="js-gmap-wrapper">
                    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&key={$core.config.events_gmap_key}"></script>
                    <div class="gmap-data hidden" id="item-gmap-data">
                        <input type="hidden" name="longitude" value="{if isset($smarty.post.longitude)}{$smarty.post.longitude}{elseif isset($item.longitude)}{$item.longitude}{/if}">
                        <input type="hidden" name="latitude" value="{if isset($smarty.post.latitude)}{$smarty.post.latitude}{elseif isset($item.latitude)}{$item.latitude}{/if}">
                    </div>

                    <label id="js-gmap-annotation" class="col col-lg-2 control-label">{lang key='drag_and_drop_marker'}</label>
                    <div id="js-gmap-renderer" class="col col-lg-8"></div>
                </div>
            {/capture}

    {include 'field-type-content-fieldset.tpl' isSystem=true datetime=true}

</form>
{ia_add_media files='datepicker'}
{ia_print_css files='_IA_URL_modules/events/templates/front/css/style'}
{ia_print_js files='_IA_URL_modules/events/js/admin/manage' order='3'}