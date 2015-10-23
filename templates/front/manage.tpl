<form method="post" action="{$smarty.const.IA_SELF}" class="ia-form">
{preventCsrf}

{include file='plans.tpl' item=$item}

<div class="form-group">
	<label class="control-label">{lang key='title'}: <span class="required-label">*</span></label>
	<input class="form-control" type="text" name="title" value="{$item.title}" size="50" maxlength="255">
</div>
<div class="form-group">
	{assign var='default_date' value=(isset($item.date) && '0000-00-00 00:00' != $item.date) ? {$item.date|escape:'html'} : {$smarty.now|date_format:'%Y-%m-%d %H:%m'}}
	<label class="control-label">{lang key='date_start'}: <span class="required-label">*</span></label>
	<div id="date-start" class="input-group">
		<input type="text" name="date" class="form-control js-datepicker" value="{$default_date}" data-date-show-time="true" data-date-format="yyyy-mm-dd H:i" readonly>
		<span class="input-group-addon js-datepicker-toggle"><span class="fa fa-calendar"></span></span>
	</div>
</div>
<div class="form-group">
	{assign var='default_date_end' value=(isset($item.date_end) && '0000-00-00 00:00' != $item.date_end) ? {$item.date_end|escape:'html'} : {$smarty.now|date_format:'%Y-%m-%d %H:%m'}}
	<label for="" class="control-label">{lang key='date_end'}: <span class="required-label">*</span></label>
	<div id="date-end" class="input-group">
		<input type="text" name="date_end" class="form-control js-datepicker" value="{$default_date_end}" data-date-show-time="true" data-date-format="yyyy-mm-dd H:i" readonly>
		<span class="input-group-addon js-datepicker-toggle"><span class="fa fa-calendar"></span></span>
	</div>
</div>
<div class="form-group">
	<label for="" class="control-label">{lang key='venue'}:</label>
	<input class="form-control" type="text" name="venue" value="{$item.venue}" size="50" maxlength="255">
</div>
<div class="form-group">
	<label for="" class="control-label">{lang key='repeat'}:</label>
	{html_options options=$repeat selected=$item.repeat name='repeat' class='form-control'}
</div>
<div class="form-group">
	<label for="" class="control-label">{lang key='detailed_description'}: <span class="required-label">*</span></label>
	{ia_wysiwyg name='description' value=$item.description}
</div>

{include file='captcha.tpl'}

<div class="form-actions">
	{if isset($item.id)}
		<input type="hidden" name="id" value="{$item.id}">
	{/if}
	<input type="submit" value="{lang key='save'}" name="create" class="btn btn-primary">
</div>

</form>

{ia_add_media files='datepicker'}
{ia_print_css files='_IA_URL_plugins/events/templates/front/css/style'}
{ia_print_js files='_IA_URL_plugins/events/js/frontend/manage'}
