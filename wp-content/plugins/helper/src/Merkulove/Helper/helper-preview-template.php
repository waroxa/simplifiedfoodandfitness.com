<?php
/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}
?>
<!doctype html>

<html <?php language_attributes(); ?> style="height: 100%; overflow-y: hidden; position: relative;">

<head>
    <meta charset="utf-8">
    <title>Helper Live Preview</title>

    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <?php wp_head(); ?>
</head>

<body id="mdp-helper-live-preview-body">
<div id="mdp-helper-live-preview-container" data-live-preview="true">
    <style>

    </style>
    <?php \Merkulove\Helper\Caster::get_instance()->the_chat_bot( true ); ?>
</div>

<?php wp_footer(); ?>

</body>

</html>

