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
