<?php
/**
Plugin Name: Import Posts From GCN Plugin
Plugin URI: https://www.goodcitizen.network
Description: Import posts from Good Citizen Network
Version: 20190704
Author: Good Citizen Network
*/

register_activation_hook( __FILE__, 'display_gcn_install');

register_deactivation_hook( __FILE__, 'display_gcn_remove' );


function display_gcn_install() {
    add_option("display_gcn_token_text", "", '', 'yes');
}
function display_gcn_remove() {
    delete_option('display_gcn_token_text');
}

if( is_admin() ) {
    add_action('admin_menu', 'display_gcn_menu');
}

function display_gcn_menu() {
    add_options_page('Set GCN Account', 'GCN Menu', 'administrator','display_gcn', 'display_gcn_html_page');
}
function display_gcn_html_page() {
    ?>
    <div>
        <h2>Set GCN Account</h2>

        <form method="post" action="options.php">
            <?php /* below code is for storing token to database */ ?>
            <?php wp_nonce_field('update-options'); ?>

            <p>GCN Token :<input type="text" id="display_gcn_token_text" name="display_gcn_token_text" value="<?php echo get_option('display_gcn_token_text'); ?>"/></p>
            <p>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="display_gcn_token_text" />

                <input type="submit" value="Save" class="button-primary" />
            </p>

        </form>
    </div>
    <?php
}

/**
 * This is our callback function that embeds our resource in a WP_REST_Response.
 *
 * The parameter is already sanitized by this point so we can use it without any worries.
 */
function gcn_add_post( $request ) {
    if ( isset( $request['title'] ) && isset( $request['content'] ) && isset( $request['token'] ) && isset($request['category'])) {
        if($request['token'] != get_option('display_gcn_token_text')){
            return new WP_Error( 'rest_invalid', esc_html__( 'Token is wrong.', 'my-text-domain' ), array( 'status' => 400 ) );
        }
        $title = sanitize_text_field ($request['title']);
        $content = wp_filter_post_kses ($request['content']);
        $category = sanitize_text_field ($request['category']);
        $my_post = array(

            'post_title' => $title,

            'post_content' =>$content,

            'post_status' => 'publish',

            'post_author' => 1,

            'post_category' => array($category),

        );
        $wp_error='';
        $post_id = wp_insert_post( $my_post,$wp_error  );

        return rest_ensure_response( $post_id);
    }

    return new WP_Error( 'rest_invalid', esc_html__( 'The data parameter is required.', 'my-text-domain' ), array( 'status' => 400 ) );
}


function gcn_update_post( $request ) {
    if ( isset( $request['title'] ) && isset( $request['content'] ) && isset( $request['token'] ) && isset( $request['post_id'] )) {
        if($request['token'] != get_option('display_gcn_token_text')){
            return new WP_Error( 'rest_invalid', esc_html__( 'Token is wrong.', 'my-text-domain' ), array( 'status' => 400 ) );
        }
        $title = sanitize_text_field($request['title']);
        $content = wp_filter_post_kses($request['content']);
        $id = sanitize_text_field($request['post_id']);

        $my_post = array();
        $my_post['ID'] = $id;
        $my_post['post_title'] = $title;
        $my_post['post_content'] = $content;

        $post_id = wp_update_post( $my_post );
        return rest_ensure_response( $post_id);
    }

    return new WP_Error( 'rest_invalid', esc_html__( 'The data parameter is required.', 'my-text-domain' ), array( 'status' => 400 ) );
}

function gcn_delete_post( $request ) {
    if ( isset( $request['token'] ) && isset( $request['post_id'] )) {
        if($request['token'] != get_option('display_gcn_token_text')){
            return new WP_Error( 'rest_invalid', esc_html__( 'Token is wrong.', 'my-text-domain' ), array( 'status' => 400 ) );
        }
        $id = sanitize_text_field($request['post_id']);

        $post_id = wp_delete_post( $id);
        return rest_ensure_response( $post_id);
    }

    return new WP_Error( 'rest_invalid', esc_html__( 'The data parameter is required.', 'my-text-domain' ), array( 'status' => 400 ) );
}

function gcn_get_menu($request){
    if ( isset( $request['token'] )) {
        if($request['token'] != get_option('display_gcn_token_text')){
            return new WP_Error( 'rest_invalid', esc_html__( 'Token is wrong.', 'my-text-domain' ), array( 'status' => 400 ) );
        }
        $menu_location = get_nav_menu_locations();
        $menus_item = wp_get_nav_menu_items($menu_location["top"]);
        return rest_ensure_response($menus_item);
    }
    return new WP_Error( 'rest_invalid', esc_html__( 'The data parameter is required.', 'my-text-domain' ), array( 'status' => 400 ) );
}
/**
 * Validate a request argument based on details registered to the route.
 *
 * @param  mixed            $value   Value of the 'filter' argument.
 * @param  WP_REST_Request  $request The current request object.
 * @param  string           $param   Key of the parameter. In this case it is 'filter'.
 * @return WP_Error|boolean
 */
function gcn_data_arg_validate_callback( $value, $request, $param ) {
    // If the 'data' argument is not a string return an error.
    if ( ! is_string( $value ) ) {
        return new WP_Error( 'rest_invalid_param', esc_html__( 'The filter argument must be a string.', 'my-text-domain' ), array( 'status' => 400 ) );
    }
}

/**
 * Sanitize a request argument based on details registered to the route.
 *
 * @param  mixed            $value   Value of the 'filter' argument.
 * @param  WP_REST_Request  $request The current request object.
 * @param  string           $param   Key of the parameter. In this case it is 'filter'.
 * @return WP_Error|boolean
 */
function gcn_data_arg_sanitize_callback( $value, $request, $param ) {
    // It is as simple as returning the sanitized value.
    return sanitize_text_field( $value );
}

function gcn_data_arg_sanitize_html_callback( $value, $request, $param ) {
    // It is as simple as returning the sanitized value.
    return wp_filter_post_kses( $value );
}

/**
 * We can use this function to contain our arguments for the endpoint.
 */
function gcn_add_post_arguments() {
    $args = array();
    // Here we are registering the schema for the filter argument.
    $args['title'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_callback',
    );
    $args['content'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_html_callback',
    );
    $args['token'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_callback',
    );
    $args['category'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_callback',
    );

    return $args;
}
function gcn_update_post_arguments() {
    $args = array();
    // Here we are registering the schema for the filter argument.
    $args['title'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_callback',
    );
    $args['content'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_html_callback',
    );
    $args['token'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_callback',
    );
    $args['post_id'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_callback',
    );
    return $args;
}
function gcn_delete_post_arguments() {
    $args = array();
    // Here we are registering the schema for the filter argument.
    $args['token'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_callback',
    );
    $args['post_id'] = array(
        // description should be a human readable description of the argument.
        'description' => esc_html__( 'The data parameter is used to be sanitized and returned in the response.', 'my-text-domain' ),
        // type specifies the type of data that the argument should be.
        'type'        => 'string',
        // Set the argument to be required for the endpoint.
        'required'    => true,
        // We are registering a basic validation callback for the data argument.
        'validate_callback' => 'gcn_data_arg_validate_callback',
        // Here we register the validation callback for the filter argument.
        'sanitize_callback' => 'gcn_data_arg_sanitize_callback',
    );
    return $args;
}
/**
 * This function is where we register our routes for our endpoint.
 */
function gcn_register_routes() {
    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    register_rest_route( 'plugin-import-posts-from-gcn/v1', '/add-post', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'POST',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'gcn_add_post',
        // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
        'args' => gcn_add_post_arguments(),
    ) );
    register_rest_route( 'plugin-import-posts-from-gcn/v1', '/update-post', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'POST',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'gcn_update_post',
        // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
        'args' => gcn_update_post_arguments(),
    ) );
    register_rest_route( 'plugin-import-posts-from-gcn/v1', '/delete-post', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'POST',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'gcn_delete_post',
        // Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
        'args' => gcn_delete_post_arguments(),
    ) );

    register_rest_route( 'plugin-import-posts-from-gcn/v1', '/get-menu', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => 'POST',
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'gcn_get_menu',
    ) );
}

add_action( 'rest_api_init', 'gcn_register_routes' );

?>