<?php
global $qtn_config;
$lang = isset( $_GET['edit_lang'] ) ? qtn_clean( $_GET['edit_lang'] ) : $qtn_config->languages[ get_locale() ];
if ( count( $qtn_config->languages ) <= 1 ) {
  return;
}
?>
<h3 id="qtn-language-switcher" class="nav-tab-wrapper language-switcher">
  <?php foreach ( $qtn_config->languages as $key => $language ) { ?>
  <a class="nav-tab<?php if ( $lang == $language ) { ?> nav-tab-active<?php } ?>"
     href="<?php echo add_query_arg( 'edit_lang', $language, home_url( $_SERVER['REQUEST_URI'] ) ); ?>">
    <img src="<?php echo QN()->flag_dir() . $qtn_config->options[ $key ]['flag'] . '.png'; ?>"
         alt="<?php echo $qtn_config->options[ $key ]['name']; ?>">
    <span><?php echo $qtn_config->options[ $key ]['name']; ?></span>
  </a>
  <?php } ?>
</h3>
