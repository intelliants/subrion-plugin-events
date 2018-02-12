<form method="post" enctype="multipart/form-data" class="ia-form add-event">
    {preventCsrf}
    {include 'plans.tpl' item=$item}

    {capture name="title" append="field_after"}
        <div class="form-group">
            <label for="input-category"> {lang key='category'}</label>
            <select name="category_id" id="input-category" class="form-control">
                <option value="0">{lang key='_select_'}</option>
                {if iaCore::ACTION_EDIT == $pageAction}
                    {html_options options=$categories selected=$item.category_id}
                {else}
                    {html_options options=$categories}
                {/if}
            </select>
        </div>

    {/capture}

    {capture name="venue" append="field_after"}
        <div class="row hidden" id="js-gmap-wrapper">
            <script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false&key={$core.config.maps_api_key}"></script>
            <div class="gmap-data hidden" id="item-gmap-data">
                <input type="hidden" name="longitude" value="{if isset($smarty.post.longitude)}{$smarty.post.longitude}{elseif isset($item.longitude)}{$item.longitude}{/if}">
                <input type="hidden" name="latitude" value="{if isset($smarty.post.latitude)}{$smarty.post.latitude}{elseif isset($item.latitude)}{$item.latitude}{/if}">
            </div>

            <label id="js-gmap-annotation" class="col col-lg-2 control-label">{lang key='drag_and_drop_marker'}</label>
            <div id="js-gmap-renderer" class="col col-md-12"></div>
        </div>
    {/capture}

    {include 'item-view-tabs.tpl'}

    <div class="ia-form__after-tabs">
        {include 'captcha.tpl'}
        <div class="fieldset__actions">
            <button type="submit" name="create" class="btn btn-primary">{lang key='submit'}</button>
        </div>
    </div>
{*
    <div class="form-actions">
        {if isset($item.id)}
            <input type="hidden" name="id" value="{$item.id|intval}">
        {/if}
        <input type="submit" value="{lang key='save'}" name="create" class="btn btn-primary">
    </div>*}
</form>
{ia_add_media files='datepicker'}
{ia_print_css files='_IA_URL_modules/events/templates/front/css/style'}
{ia_print_js files='_IA_URL_modules/events/js/frontend/manage'}
