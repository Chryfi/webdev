<?php

/**
 * Output a list of tags used for forms. Every tag item has a remove button and
 * a hidden input with the tag name as value.
 * See /ressources/js/tagSearch.js for more concrete details.
 * @param $tags
 * @return void
 */
function outputTagList($tags) : void {
    foreach ($tags as $tag) {
        $id = "tag-search-result-".$tag;
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


/**
 * Output a simple tag list only for display purposes.
 * @param $tags
 * @return void
 */
function outputSimpleTagList($tags) {
    foreach ($tags as $tag) {
        echo '<p class="col-auto tag-item">'.$tag.'</p>';
    }
}
