<?php 
/*
Plugin Name: CF7 to Custom Post Type
Plugin URI: https://www.ahmedev.com
Description: Create custom post type from CF7 submission
Author: Ahmed Benali
Version: 1.0.0
Author URI: https://www.ahmedev.com
*/

add_action( 'init', 'aba_cf7_to_cpt_init');
add_action( 'admin_menu', 'cf7cpt_custom_meta' );
add_action( 'save_post', 'cf7cpt_meta_save' );
function aba_cf7_to_cpt_init() {
           $labels = array(
        'name'                => _x( 'Litiges', 'Post Type General Name', 'cf7cpt' ),
        'singular_name'       => _x( 'Litige', 'Post Type Singular Name', 'cf7cpt' ),
        'menu_name'           => __( 'Litiges', 'cf7cpt' ),
        'all_items'           => __( 'Toutes les Litiges', 'cf7cpt' ),
        'view_item'           => __( 'Afficher Litige', 'cf7cpt' ),
        'add_new_item'        => __( 'Ajouter Nouvelle Litige', 'cf7cpt' ),
        'add_new'             => __( 'Ajouter Nouveau', 'cf7cpt' ),
        'edit_item'           => __( 'Editer Litige', 'cf7cpt' ),
        'update_item'         => __( 'Mettre à jour litiges', 'cf7cpt' ),
        'search_items'        => __( 'Rechercher Litige', 'cf7cpt' ),
        'not_found'           => __( 'Aucune litige', 'cf7cpt' ),
        'not_found_in_trash'  => __( 'Aucune Litige dans la Corbeille', 'cf7cpt' ),
    );
          
    $args = array(
        'label'               => __( 'litige', 'cf7cpt' ),
        'description'         => __( 'Les litiges d\'un consommateur', 'cf7cpt' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor' ),
        'hierarchical'        => false,
        'public'              => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'litige' ),
        'capability_type'     => 'page',
        'exclude_from_search' => true,
    );
     
    register_post_type( 'litige', $args );

    }
    
    /**
 * Adds a meta box to the post editing screen
 */
function cf7cpt_custom_meta() {
	add_meta_box( 'cf7cpt_meta', __( 'Données de la litige', 'cf7cpt' ), 'cf7cpt_meta_callback', 'litige' );
}
/**
 * Outputs the content of the meta box
 */
function cf7cpt_meta_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'cf7cpt_nonce' );
	$cf7cpt_stored_meta = get_post_meta( $post->ID );
	?>

    <p>
		<label for="emailco" class="cf7cpt-row-title"><strong><?php _e( 'E-mail', 'cf7cpt' )?></strong></label><br>
		<input type="email" name="emailco" id="emailco" value="<?php if ( isset ( $cf7cpt_stored_meta['emailco'] ) ) echo $cf7cpt_stored_meta['emailco'][0]; ?>" />
        <p><i>l'e-mail du déclarant.</i></p>
	</p>
    <p>
		<label for="teleco" class="cf7cpt-row-title"><strong><?php _e( 'Téléphone', 'cf7cpt' )?></strong></label><br>
		<input type="text" name="teleco" id="teleco" value="<?php if ( isset ( $cf7cpt_stored_meta['teleco'] ) ) echo $cf7cpt_stored_meta['teleco'][0]; ?>" />
        <p><i>le téléphone du déclarant.</i></p>
	</p>
    <p>
		<label for="ip" class="cf7cpt-row-title"><strong><?php _e( 'Adresse IP soumission', 'cf7cpt' )?></strong></label><br>
		<input type="text" name="ip" id="ip" value="<?php if ( isset ( $cf7cpt_stored_meta['ip'] ) ) echo $cf7cpt_stored_meta['ip'][0]; ?>" />
        <p><i>L'adresse ip lors de la soumission.</i></p>
	</p>
	<?php
}
/**
 * Saves the custom meta input
 */
function cf7cpt_meta_save( $post_id ) {
 
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'cf7cpt_nonce' ] ) && wp_verify_nonce( $_POST[ 'cf7cpt_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}
    if ( !current_user_can( 'edit_page', $post_id ) ){
                print 'Sorry, can\'t edit.';
    }
	if( isset( $_POST[ 'emailco' ] ) ) {
		update_post_meta( $post_id, 'emailco', sanitize_text_field( $_POST[ 'emailco' ] ) );
	}
	if( isset( $_POST[ 'teleco' ] ) ) {
		update_post_meta( $post_id, 'teleco', sanitize_text_field( $_POST[ 'teleco' ] ) );
	}
	if( isset( $_POST[ 'ip' ] ) ) {
		update_post_meta( $post_id, 'ip', $_POST[ 'ip' ] );
	}
}
add_filter( 'wpcf7_verify_nonce', '__return_true' );
add_filter( 'wpcf7_load_js', '__return_true' );
add_action('wpcf7_mail_sent','save_my_form_data_to_my_cpt');

function save_my_form_data_to_my_cpt($contact_form){
    $submission = WPCF7_Submission::get_instance();
    if (!$submission){
        return;
    }
	$wpcf7 = WPCF7_ContactForm::get_current();
	if ($wpcf7->id() == 4917){
    $posted_data = $submission->get_posted_data();
    //The Sent Fields are now in an array
    //Let's say you got 4 Fields in your Contact Form
    //my-email, my-name, my-subject and my-message
    //you can now access them with $posted_data['my-email']
    //Do whatever you want like:
    $new_post = array();
    if(isset($posted_data['your-name']) && !empty($posted_data['your-name'])){
        $new_post['post_title'] = 'litige de '.$posted_data['your-name'];
    } else {
        $new_post['post_title'] = 'Litige anonyme';
    }
    $new_post['post_type'] = 'litige'; //insert here your CPT
    if(isset($posted_data['your-message'])){
        $new_post['post_content'] = $posted_data['your-message'];
    } else {
        $new_post['post_content'] = 'Aucune litige soumise';
    }
    $new_post['post_status'] = 'publish';
    //you can also build your post_content from all of the fields of the form, or you can save them into some meta fields
    if(isset($posted_data['your-email']) && !empty($posted_data['your-email'])){
        $new_post['meta_input']['emailco'] = $posted_data['your-email'];
    }
    if(isset($posted_data['tel-17']) && !empty($posted_data['tel-17'])){
        $new_post['meta_input']['teleco'] = $posted_data['tel-17'];
    }
	$new_post['meta_input']['ip'] = $_SERVER["REMOTE_ADDR"];
    //When everything is prepared, insert the post into your Wordpress Database
    $post_id = wp_insert_post($new_post);
	return;
	}
}