<?php
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (preg_match('~(?:^|/)(?:[.]|database(?:/|$)|includes(?:/|$)|README[.]md$)|(?:/|^)router[.]php$~i', $path)) {
    http_response_code(404);
    return true;
}
return false;
