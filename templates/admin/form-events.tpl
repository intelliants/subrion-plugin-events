<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
	{preventCsrf}
	<div class="wrap-list">
		<div class="wrap-group">
			<div class="wrap-group-heading">
				<h4>{lang key='options'}</h4>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label" for="input-language">{lang key='title'} <span class="text-danger">*</span></label>
				<div class="col col-lg-4">
					<input type="text" name="title" value="{$item.title}" maxlength="255">
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label" for="input-category">{lang key='category'}</label>
				<div class="col col-lg-4">
					<select name="category_id" id="input-category">
						<option value="0">{lang key='_select_'}</option>
						{html_options options=$categories selected=$item.category_id}
					</select>
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label" for="input-language">{lang key='date_start'} <span class="text-danger">*</span></label>
				<div class="col col-lg-4">
					<div class="input-group">
						<input type="text" name="date" class="js-datepicker" value="{$item.date}" data-date-show-time="true" data-date-format="yyyy-mm-dd H:i" readonly>
						<span class="input-group-addon js-datepicker-toggle"><span class="i-calendar"></span></span>
					</div>
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label" for="input-language">{lang key='date_end'} <span class="text-danger">*</span></label>
				<div class="col col-lg-4">
					<div class="input-group">
						<input type="text" name="date_end" class="js-datepicker" value="{$item.date_end}" data-date-show-time="true" data-date-format="yyyy-mm-dd H:i" readonly>
						<span class="input-group-addon js-datepicker-toggle"><span class="i-calendar"></span></span>
					</div>
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label" for="input-language">{lang key='repeat'}</label>
				<div class="col col-lg-4">
					{html_options options=$repeat selected=$item.repeat name='repeat'}
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label" for="input-description">{lang key='detailed_description'} <span class="text-danger">*</span></label>
				<div class="col col-lg-8">
					{ia_wysiwyg name='description' value=$item.description}
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label" for="input-image">{lang key='image'}</label>
				<div class="col col-lg-4">
					{if isset($item.image) && $item.image}
						<div class="input-group thumbnail thumbnail-single with-actions">
							<a href="{printImage imgfile=$item.image fullimage=true url=true}" rel="ia_lightbox">
								{printImage imgfile=$item.image}
							</a>
							<div class="caption">
								<a class="btn btn-small btn-danger" href="javascript:void(0);" title="{lang key='delete'}" onclick="return intelli.admin.removeFile('{$item.image}',this,'events','image','{$id}')"><span class="i-remove-sign"></span></a>
							</div>
						</div>
					{/if}

					{ia_html_file name='image' id='input-image'}
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label" for="input-language">{lang key='venue'}</label>
				<div class="col col-lg-4">
					<input id="venue" type="text" name="venue" class="common" value="{$item.venue}" maxlength="255">
				</div>
			</div>

			<div class="row hidden" id="js-gmap-wrapper">
				<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
				<div class="gmap-data hidden" id="item-gmap-data">
					<input type="hidden" name="longitude" value="{if isset($smarty.post.longitude)}{$smarty.post.longitude}{elseif isset($item.longitude)}{$item.longitude}{/if}">
					<input type="hidden" name="latitude" value="{if isset($smarty.post.latitude)}{$smarty.post.latitude}{elseif isset($item.latitude)}{$item.latitude}{/if}">
				</div>

				<label id="js-gmap-annotation" class="col col-lg-2 control-label">{lang key='drag_and_drop_marker'}</label>
				<div id="js-gmap-renderer" class="col col-lg-8"></div>
			</div>
	</div>

	{include file='fields-system.tpl'}
</form>

{ia_add_media files='datepicker'}
{ia_print_css files='_IA_URL_plugins/events/templates/front/css/style'}
{ia_print_js files='_IA_URL_plugins/events/js/admin/manage' order='3'}