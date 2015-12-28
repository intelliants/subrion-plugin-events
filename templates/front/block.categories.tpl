<div class="list-group">
	{foreach $events.categories as $category}
		<a href="{$smarty.const.IA_URL}events/{$category.slug}/" class="list-group-item">
			<span class="badge">{$category.num}</span>
			{$category.title|escape:'html'}
		</a>
	{/foreach}
</div>