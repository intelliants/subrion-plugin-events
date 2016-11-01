<form method="post" enctype="multipart/form-data" class="ia-form add-event">
	{preventCsrf}

	{include 'plans.tpl' item=$item}

	<div class="form-group">
		<label>{lang key='title'}: <span class="required-label">*</span>
			<input class="form-control" type="text" name="title" value="{$item.title|escape:'html'}" size="50" maxlength="255">
		</label>
	</div>
	<div class="form-group">
		<label>{lang key='category'}:
			<select class="form-control" name="category_id" id="input-category">
				<option value="0">{lang key='_select_'}</option>
				{html_options options=$categories selected=$item.category_id}
			</select>
		</label>
	</div>
	<div class="form-group">
		{assign var='default_date' value=(isset($item.date) && '0000-00-00 00:00' != $item.date) ? {$item.date|escape:'html'} : {$smarty.now|date_format:'%Y-%m-%d %H:%m'}}
		<label>{lang key='date_start'}: <span class="required-label">*</span>
			<div id="date-start" class="input-group">
				<input type="text" name="date" class="form-control js-datepicker" value="{$default_date}" data-date-show-time="true" data-date-format="yyyy-mm-dd H:i" readonly>
				<span class="input-group-addon js-datepicker-toggle"><span class="fa fa-calendar"></span></span>
			</div>
		</label>
	</div>
	<div class="form-group">
		{assign var='default_date_end' value=(isset($item.date_end) && '0000-00-00 00:00' != $item.date_end) ? {$item.date_end|escape:'html'} : {$smarty.now|date_format:'%Y-%m-%d %H:%m'}}
		<label>{lang key='date_end'}: <span class="required-label">*</span>
			<div id="date-end" class="input-group">
				<input type="text" name="date_end" class="form-control js-datepicker" value="{$default_date_end}" data-date-show-time="true" data-date-format="yyyy-mm-dd H:i" readonly>
				<span class="input-group-addon js-datepicker-toggle"><span class="fa fa-calendar"></span></span>
			</div>
		</label>
	</div>
	<div class="form-group">
		<label>{lang key='venue'}:
			<input class="form-control" type="text" name="venue" value="{$item.venue|escape:'html'}" size="50" maxlength="255">
		</label>
	</div>
	<div class="form-group">
		<label>{lang key='repeat'}:
			{html_options options=$repeat selected=$item.repeat name='repeat' class='form-control'}
		</label>
	</div>
	<div class="form-group form-group--fullwidth">
		<label for="description">{lang key='detailed_description'}: <span class="required-label">*</span></label>
		{ia_wysiwyg name='description' value=$item.description}
	</div>
	<div class="form-group form-group--fullwidth">
		<label for="image">{lang key='image'}:</label>

		{if isset($item.image) && $item.image}
			<div class="thumbnail">
				<div class="thumbnail__actions">
					<button class="btn btn-danger btn-sm js-delete-file" data-field="image" data-item="events" data-item-id="{$item.id|default:''}" data-picture-path="{$item.image}" title="{lang key='delete'}"><span class="fa fa-times"></span></button>
				</div>

				<a href="{printImage imgfile=$item.image fullimage=true url=true}" rel="ia_lightbox[image]">
					{printImage imgfile=$item.image}
				</a>

				<input type="hidden" name="image[path]" value="{$item.image}">
			</div>
		{/if}

		<div class="input-group js-files">
			<span class="input-group-btn">
				<span class="btn btn-primary btn-file">{lang key='browse'}<input type="file" name="image" id="input-image" class="form-control"></span>
			</span>
			<input type="text" class="form-control js-file-name"{if $item.image} value="{$item.image}"{/if} readonly>
		</div>
	</div>

	{include 'captcha.tpl'}

	<div class="form-actions">
		{if isset($item.id)}
			<input type="hidden" name="id" value="{$item.id|intval}">
		{/if}
		<input type="submit" value="{lang key='save'}" name="create" class="btn btn-primary">
	</div>
</form>
{ia_add_media files='datepicker'}
{ia_print_css files='_IA_URL_plugins/events/templates/front/css/style'}
{ia_print_js files='_IA_URL_plugins/events/js/frontend/manage'}
