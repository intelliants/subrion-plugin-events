{if isset($term)}
	<div class="info">{lang key='listings_found'}: <b>{$paginator.total}</b></div>
{/if}

{if isset($items) && $items}
<div class="media-items events">
	{foreach from=$items item=event}
		<div class="media event">
			<div class="media-body">
				<h4 class="media-heading">
					<a href="{$event.url}">{$event.title}</a>
				</h4>

				<div class="media-date">
					<span class="fa fa-clock-o"></span> {$event.date} - {$event.date_end}
					{if $event.venue}, <span class="fa fa-map-marker"></span> {$event.venue}{/if}
				</div>

				{$event.description|strip_tags|truncate:300}
			</div>
			<div class="media-info">
				<a href="{$event.url}" class="btn btn-sm btn-info"><span class="fa fa-info"></span></a>
				<span class="fa fa-user"></span>
				{if $event.owner}
					{lang key='published_by'}
					{ia_url item='members' data=$event.owner text=$event.owner_fullname}
				{else}
					<i>{lang key='guest'}</i>
				{/if}
			</div>
		</div>
	{/foreach}
</div>

{navigation aTotal=$paginator.total aTemplate=$paginator.template aItemsPerPage=$paginator.limit aNumPageItems=5 aTruncateParam=1}

{else}
	<div class="message alert">{lang key='no_events'}</div>
{/if}

{ia_print_css files='_IA_URL_plugins/events/templates/front/css/style'}
