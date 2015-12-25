<ul>
	{foreach $events.categories as $category}
	<li><a href="{$smarty.const.IA_URL}events/{$category.slug}/">{$category.title|escape:'html'}</a></li>
	{/foreach}
</ul>