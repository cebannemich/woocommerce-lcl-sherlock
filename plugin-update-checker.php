<?php
require_once 'plugin-update-checker-5.4/plugin-update-checker.php';

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://example.com/plugins/mon-plugin/info.json',
    __FILE__,
    'mon-plugin'
);
