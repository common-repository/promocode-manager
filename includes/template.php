<?php
if(!function_exists("convert_to_screen")){
    function convert_to_screen( $hook_name ) {
        if ( ! class_exists( 'WP_Screen' ) ) {
            _doing_it_wrong( 'convert_to_screen(), add_meta_box()', __( "Likely direct inclusion of wp-admin/includes/template.php in order to use add_meta_box(). This is very wrong. Hook the add_meta_box() call into the add_meta_boxes action instead." ), '3.3' );
            return (object) array( 'id' => '_invalid', 'base' => '_are_belong_to_us' );
        }

        return WP_Screen::get( $hook_name );
    }
}