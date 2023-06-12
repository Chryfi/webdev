<?php
/**
 * Redirect using javascript by echoing a script tag.
 * @param string $location
 * @return void
 */
function redirectJSReplace(string $location) {
    echo '<script>
        window.location.replace(\''.$location.'\');
    </script>';
}

/**
 * Refreshes the website by outputting a script tag.
 * @return void
 */
function refreshWithJS() {
    echo '<script>
        window.location.reload();
    </script>';
}