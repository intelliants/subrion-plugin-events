<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
    {preventCsrf}
    {*<div class="wrap-list">*}
        {*<div class="wrap-group">*}
            {*<div class="wrap-group-heading">*}
                {*<h4>{lang key='general'}</h4>*}
            {*</div>*}

            {*<div class="row">*}
                {*<label class="col col-lg-2 control-label" for="input-title">{lang key='title'} {lang key='field_required'}</label>*}

                {*<div class="col col-lg-4">*}
                    {*<input type="text" name="title" id="input-title" value="{$item.title|escape:'html'}">*}
                {*</div>*}
            {*</div>*}

            {*<div class="row">*}
                {*<label class="col col-lg-2 control-label" for="input-slug">{lang key='slug'}</label>*}

                {*<div class="col col-lg-4">*}
                    {*<input type="text" name="slug" id="input-slug" value="{$item.slug|escape:'html'}">*}
                {*</div>*}
            {*</div>*}
        {*</div>*}
        {include 'field-type-content-fieldset.tpl' isSystem=true datetime=true}

</form>