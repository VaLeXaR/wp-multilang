<?php
$languages = wpm_get_languages();
$lang = wpm_get_language();
if ( count( $languages ) <= 1 ) {
  return;
}
$options = wpm_get_options();
?>
<h3 id="wpm-language-switcher" class="nav-tab-wrapper language-switcher">
  <?php foreach ( $languages as $key => $language ) { ?>
  <a class="nav-tab<?php if ( $lang == $language ) { ?> nav-tab-active<?php } ?>"
     href="<?php echo add_query_arg( 'edit_lang', $language, home_url( $_SERVER['REQUEST_URI'] ) ); ?>">
    <?php if ( $options[ $key ]['flag'] ) { ?>
    <img src="<?php echo WPM()->flag_dir() . $options[ $key ]['flag'] . '.png'; ?>"
         alt="<?php echo $options[ $key ]['name']; ?>">
    <?php } ?>
    <span><?php echo $options[ $key ]['name']; ?></span>
  </a>
  <?php } ?>
</h3>
