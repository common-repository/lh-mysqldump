<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

<a href="<?php echo wp_nonce_url( admin_url( 'tools.php?page=' ).self::return_plugin_filename(), self::return_plugin_namespace().'-generate_download', self::return_plugin_namespace().'-generate_download' ); ?>"> <button class="button button-blue button-bordered"><?php _e("Generate Download", self::return_plugin_namespace() ); ?></button></a>