<?php
/**
 * Redirect using javascript by echoing a script tag.
 * @param string $location
 * @return void
 */
function redirectJS(string $location) {
    echo '<script>
        window.location.href = \''.$location.'\';
    </script>';
}