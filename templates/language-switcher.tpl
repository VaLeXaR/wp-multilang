<?php
$languages = qtn_get_languages();
$lang = isset( $_GET['edit_lang'] ) ? qtn_clean( $_GET['edit_lang'] ) : qtn_clean( $_COOKIE['edit_language'] );
if ( count( $languages ) <= 1 ) {
  return;
}
$options = qtn_get_options();
?>
<h3 id="qtn-language-switcher" class="nav-tab-wrapper language-switcher">
  <?php foreach ( $languages as $key => $language ) { ?>
  <a class="nav-tab<?php if ( $lang == $language ) { ?> nav-tab-active<?php } ?>"
     href="<?php echo add_query_arg( 'edit_lang', $language, home_url( $_SERVER['REQUEST_URI'] ) ); ?>">
    <img src="<?php echo QN()->flag_dir() . $options[ $key ]['flag'] . '.png'; ?>"
         alt="<?php echo $options[ $key ]['name']; ?>">
    <span><?php echo $options[ $key ]['name']; ?></span>
  </a>
  <?php } ?>
</h3>
