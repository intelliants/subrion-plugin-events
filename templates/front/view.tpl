<div class="tabbable">
	<ul class="nav nav-tabs">
		<li class="active">
			<a href="#tab-about" data-toggle="tab"><span>{lang key='about_the_event'}</span></a>
		</li>
		<li>
			<a href="#tab-details" data-toggle="tab"><span>{lang key='event_details'}</span></a>
		</li>
	</ul>
	<div class="tab-content">
		<div id="tab-about" class="tab-pane active">
			{if $item.image}
				<div class="ia-item-image">{printImage imgfile=$item.image fullimage=true class='img-responsive'}</div>
			{/if}
			<div class="ia-wrap">
				{$item.description}
			</div>
		</div>
		<div id="tab-details" class="tab-pane">
			<div class="ia-wrap">
				<p><span class="fa fa-clock-o"></span> {$item.date} - {$item.date_end}</p>
				{if $item.venue}
					<p><span class="fa fa-map-marker"></span> {$item.venue}</p>
					<input type="hidden" id="event-venue" value="{$item.venue}">
					<input type="hidden" name="longitude" value="{if isset($item.longitude)}{$item.longitude}{/if}">
					<input type="hidden" name="latitude" value="{if isset($item.latitude)}{$item.latitude}{/if}">

					<div id="event-gmap"></div>
				{/if}
			</div>
		</div>
	</div>
</div>

<div class="ia-item-panel">
	<!-- AddThis Button BEGIN -->
	<div class="addthis_toolbox addthis_default_style panel-item pull-left">
		<a class="addthis_counter addthis_pill_style"></a>
	</div>
	<script type="text/javascript">var addthis_config = { "data_track_addressbar":true };</script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5228073734bf0c90"></script>
	<!-- AddThis Button END -->

	<span class="panel-item pull-right">
		<i class="icon-user"></i>
		{if $item.owner}
			{lang key='published_by'}
			{ia_url item='members' data=$item.owner text=$item.owner_fullname}
		{else}
			<i>{lang key='guest'}</i>
		{/if}
	</span>
</div>

{ia_print_css files='_IA_URL_plugins/events/templates/front/css/style'}
{ia_print_js files='_IA_URL_plugins/events/js/frontend/view'}
