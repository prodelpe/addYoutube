<div class="m-b-1 m-t-1">
    <h2>{l s='YouTube Video' mod='addyoutube'}</h2>

    <fieldset class="form-group">
        
        {* Video URL *}
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <label class="form-control-label">{l s='YouTube Video URL' mod='addyoutube'}</label>
                <div class="translations tabbable">
                    <div class="translationsFields tab-content">
                        {foreach from=$languages item=language }
                            <div class="tab-pane translation-label-{$language.iso_code} {if $default_language == $language.id_lang}active{/if}">
                                <input type="text" name="addyoutube_lang_{$language.id_lang}" class="form-control" {if isset({$addyoutube_lang[$language.id_lang]}) && {$addyoutube_lang[$language.id_lang]} != ''}value="{$addyoutube_lang[$language.id_lang]}"{/if}/>
                            </div>  
                        {/foreach}    
                    </div>
                </div>
            </div>
        </div>

        {* Video Preview *}
        {if isset({$addyoutube_lang_embed}) && {$addyoutube_lang_embed} != ''}
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="translations tabbable">
                    <div class="translationsFields tab-content">
                        {foreach from=$languages item=language}
                            <div class="add_youtube tab-pane translation-label-{$language.iso_code} {if $default_language == $language.id_lang}active{/if}">
                                {$addyoutube_lang_embed nofilter}
                            </div>
                        {/foreach}  
                    </div>
                </div>
            </div>
        </div>
        {/if}
    </fieldset>

    <div class="clearfix"></div>
</div>

