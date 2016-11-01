{if isset($items) && $items && isset($term)}
	<div class="info m-b text-i">{lang key='listings_found'}: <b>{$paginator.total}</b></div>
{/if}

{if isset($items) && $items}
	<div class="ia-items events">
		{foreach $items as $event}
			<div class="media ia-item ia-item--border event">
				{if $event.image}
					<a href="{$event.url}" class="media-object pull-left">{printImage imgfile=$event.image width=100 height=100 class='img-rounded img-responsive'}</a>
				{else}
					<a href="{$event.url}" class="media-object pull-left"><img src="{$smarty.const.IA_CLEAR_URL}plugins/events/templates/front/img/preview-image.png" alt="{$event.title}" width="100" height="100" class="img-rounded img-responsive"></a>
				{/if}

				<div class="media-body">
					<h4 class="media-heading">
						<a href="{$event.url}">{$event.title|escape:'html'}</a>
					</h4>
					<div class="media-date">
						{if $event.date_end|strtotime > $smarty.server.REQUEST_TIME}
							<span class="text-success"><span class="fa fa-clock-o"></span> {$event.date} - {$event.date_end}</span>
						{else}
							<span class="fa fa-clock-o"></span> {$event.date} - {$event.date_end}
						{/if}
						{if $event.venue}, <br><span class="fa fa-map-marker"></span> {$event.venue|escape:'html'}{/if}
					</div>

					{$event.description|strip_tags|truncate:300:"..."}
				</div>
				<div class="media-info">
					<span class="fa fa-user"></span>
					{lang key='published_by'}
					{if $event.owner}
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
	<div class="message alert bg-warning">{lang key='no_events'}</div>
{/if}

{ia_print_css files='_IA_URL_plugins/events/templates/front/css/style'}
