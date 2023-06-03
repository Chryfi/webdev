<?php

/**
 * Output a list of tags.
 * See /ressources/js/tagSearch.js for more concrete details.
 * @param $tags
 * @return void
 */
function outputTagList($tags) : void {
    foreach ($tags as $tag) {
        $id = "tag-search-result".$tag;
        echo <<<HTML
        <div id="{$id}" class="col-auto tag-item">
            <div class="row tag-element-row align-items-center">
                <p class="col-auto">{$tag}</p>
                <i class="col-auto fa-solid fa-xmark color-remove tag-remove-button"></i>
                <input type="text" name="tags[]" value="{$tag}" hidden>
            </div>
        </div>
        HTML;
    }
}
