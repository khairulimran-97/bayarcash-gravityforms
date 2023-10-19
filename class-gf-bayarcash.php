<?php

defined( 'ABSPATH' ) || die();

GFForms::include_payment_addon_framework();

class GF_Bayarcash extends GFPaymentAddOn {

  private static $_instance = null;
  protected $_slug = 'gravityformsbayarcash';
  protected $_title = 'Bayarcash for Gravity Forms';

  protected $_short_title = 'Bayarcash';
  protected $_supports_callbacks = true;

  protected $_capabilities = array( 'gravityforms_bayarcash', 'gravityforms_bayarcash_uninstall' );

  protected $_capabilities_settings_page = 'gravityforms_bayarcash';
  protected $_capabilities_form_settings = 'gravityforms_bayarcash';
  protected $_capabilities_uninstall = 'gravityforms_bayarcash_uninstall';

  public function __construct()
  {
    parent::__construct();
    // based on: update_option( 'gravityformsaddon_' . $this->_slug . '_settings', $settings );
    add_action( 'update_option_gravityformsaddon_gravityformsbayarcash_settings', array($this, 'global_validate_keys'), 10, 3);
  }
  
  // Add Bayarcash Feed Setting Page 
  public static function get_instance() {

    if ( self::$_instance == null ) {
      self::$_instance = new GF_Bayarcash();
    }

    return self::$_instance;
  }
  
   public function pre_init() {
   // inspired by gravityformsstripe
   add_action( 'wp', array( $this, 'maybe_thankyou_page' ), 5 );
   parent::pre_init();
    }
  
  public function init() {
    add_filter( 'gform_disable_post_creation', array( $this, 'disable_post_creation' ), 10, 3 );

    $this->add_delayed_payment_support(
      array(
        'option_label' => esc_html__( 'Create post only when payment is received.', 'gravityformsbayarcash' )
      )
    );
    parent::init();
  }
  
  public function get_post_payment_actions_config( $feed_slug ) {
    // if ($feed_slug != $this->_slug) {
    //   return array();
    // }

    // $form = $this->get_current_form();

    // if ( GFCommon::has_post_field( $form['fields'] ) ) {
      return array(
        'position' => 'before',
        'setting'  => 'conditionalLogic',
      );
    // }

    // return array();
  }
  
  public function supported_currencies( $currencies ) {
    return array('MYR' => $currencies['MYR']);
  }

  public function get_menu_icon() {
    return plugins_url("assets/logo.svg", __FILE__);
  }
  
   public function plugin_settings_fields() {
    $configuration = array(
      array(
        'title'       => esc_html__( 'Bayarcash', 'gravityformsbayarcash' ),
        'description' => $this->get_description(),
        'fields'      => $this->global_keys_fields(),
      ),
    );


    return apply_filters('gf_bayarcash_plugin_settings_fields', $configuration);
  }

  
  
  public function get_description() {
    ob_start(); ?>
    <p>
      <?php
      printf(
        // translators: $1$s opens a link tag, %2$s closes link tag.
        esc_html__(
          'Bayarcash - Platform Pembayaran Online (Aggregator). %1$sLearn more%2$s. %3$s%3$sThis is a global configuration and it is not mandatory to set. You can still configure on per form basis.',
          'gravityformsbayarcash'
        ),
        '<a href="https://bayarcash.com//" target="_blank">',
        '</a>',
        '<br>'
      );
      ?>
    </p>
    <?php

    return ob_get_clean();
  }

  public function global_keys_fields() {
    return array(
      array(
        'name'     => 'pat_key',
        'label'    => esc_html__( 'Personal Access Token (PAT)', 'gravityformsbayarcash' ),
        'type'     => 'textarea',
        'required' => true,
        'tooltip'  => '<h6>' . esc_html__( 'Personal Access Token (PAT)', 'gravityformsbayarcash' ) . '</h6>' . esc_html__( 'You can get PAT from your email after successful register account', 'gravityformsbayarcash' )
      ),
      array(
        'name'     => 'fpx_portal_key',
        'label'    => esc_html__( 'Portal Key', 'gravityformsbayarcash' ),
        'type'     => 'text',
        'required' => true,
        'tooltip'  => '<h6>' . esc_html__( 'Portal Key', 'gravityformsbayarcash' ) . '</h6>' . esc_html__( 'Change this portal key with yours. Login to Bayarcash console > Portal.', 'gravityformsbayarcash' )
      ),

      

    );
  }
  
  
  public function global_account_status_description() {
    //Validate PAT & Portal Key
  }
  
  public function global_validate_keys($old_value, $new_value, $option_name){
    //Validate PAT & Portal Key
  }
  
  public function feed_settings_fields() {
    $feed_settings_fields = parent::feed_settings_fields();
    $feed_settings_fields[0]['description'] = esc_html__( 'Configuration payment BayarCash for Gravity Forms.', 'gravityformsbayarcash' );
    
    // Remove subscription option from Transaction type
    unset( $feed_settings_fields[0]['fields'][1]['choices'][2] );

    // Ensure transaction type mandatory
    $feed_settings_fields[0]['fields'][1]['required'] = true;

    // Temporarily remove transaction type section
    $transaction_type_array = $feed_settings_fields[0]['fields'][1];
    unset( $feed_settings_fields[0]['fields'][1] );

    // Temporarily remove product and services section
    $product_and_services = $feed_settings_fields[2];
    $other_settings = $feed_settings_fields[3];
    unset( $feed_settings_fields[2] );
    unset( $feed_settings_fields[3] );

    // // Add Bayarcash configuration settings
    // $feed_settings_fields[0]['fields'][] = array(
    //   'name'     => 'bayarcashConfigurationType',
    //   'label'    => esc_html__( 'Configuration Type', 'gravityformsbayarcash' ),
    //   'type'     => 'select',
    //   'required' => true,
    //   'onchange' => "jQuery(this).parents('form').submit();",
    //   'choices'  => array(
    //     array(
    //       'label' => esc_html__( 'Select configuration type', 'gravityformsbayarcash' ),
    //       'value' => ''
    //     ),
    //     array(
    //       'label' => esc_html__( 'Global Configuration', 'gravityformsbayarcash' ),
    //       'value' => 'global'
    //     ),
    //     array(
    //       'label' => esc_html__( 'Form Configuration', 'gravityformsbayarcash' ),
    //       'value' => 'form'
    //     ),
    //   ),
    //   'tooltip'  => '<h6>' . esc_html__( 'Configuration Type', 'gravityformsbayarcash' ) . '</h6>' . esc_html__( 'Select a configuration type. If you want to configure Bayarcash on form basis, you may use Form Configuration. If you want to use globally set keys, choose Global Configuration.', 'gravityformsbayarcash' )
    // );

    $post_titles = get_posts( array(
      'post_type'      => 'bayarcash_account',
      'posts_per_page' => -1,
      'fields'         => 'ids', // Retrieve only post IDs to optimize performance
    ) );

    $choices = array();
    foreach ( $post_titles as $post_id ) {
    $choices[] = array(
        'label' => get_the_title( $post_id ),
        'value' => $post_id,
    ); }

$feed_settings_fields[0]['fields'][] = array(
          'type'    => 'select',
          'name'    => 'bayarcash_account',
          'label'   => esc_html__( 'Select BayarCash Account', 'bayarcash' ),
          'choices' => $choices, // Use the dynamically generated choices array
     
);

    // $feed_settings_fields[] = array(
    //   'title'      => esc_html__( 'BayarCash Form Settings', 'gravityformsbayarcash' ),
    //   'dependency' => array(
    //     'field'  => 'bayarcashConfigurationType',
    //     'values' => array( 'form' )
    //   ),
    //   'description' => esc_html__('Set Personal Access Token (PAT) and Portal Key From Dashboard Bayarcash for this forms only', 'gravityformsbayarcash'),
    //   'fields'     => array(
    //     array(
    //       'name'     => 'pat_key',
    //       'label'    => esc_html__( 'Personal Access Token (PAT)', 'gravityformsbayarcash' ),
    //       'type'     => 'textarea',
    //       'class'    => 'medium',
    //       'required' => true,
    //       'tooltip'  => '<h6>' . esc_html__( 'Personal Access Token (PAT)', 'gravityformsbayarcash' ) . '</h6>' . esc_html__( 'You can get PAT from your email after successful register account account.', 'gravityformsbayarcash' )
    //     ),
    //     array(
    //       'name'     => 'fpx_portal_key',
    //       'label'    => esc_html__( 'Portal Key', 'gravityformsbayarcash' ),
    //       'type'     => 'text',
    //       'class'    => 'medium',
    //       'required' => true,
    //       'tooltip'  => '<h6>' . esc_html__( 'Portal Key', 'gravityformsbayarcash' ) . '</h6>' . esc_html__( 'Change this portal key with yours. Login to Bayarcash console > Portal.', 'gravityformsbayarcash' )
    //     ),
    //   )
    // );

    // Readd transaction type section
    $feed_settings_fields[0]['fields'][] = $transaction_type_array;

    // Readd product and services section
    $feed_settings_fields[] = $product_and_services;
    $feed_settings_fields[] = $other_settings;

    return apply_filters( 'gf_bayarcash_feed_settings_fields', $feed_settings_fields );
  }
  
   public function other_settings_fields() {
    $other_settings_fields                 = parent::other_settings_fields();
    $other_settings_fields[0]['name']      = 'clientInformation';
    $other_settings_fields[0]['label']     = esc_html__( 'Client Information.', 'gravityformsbayarcash' );
    $other_settings_fields[0]['field_map'] = $this->client_info_fields();
    $other_settings_fields[0]['tooltip']   = '<h6>' . esc_html__( 'Client Information', 'gravityformsbayarcash' ) . '</h6>' . esc_html__( 'Map your Form Fields to the available listed fields. Only email are required to be set', 'gravityformsbayarcash' );

    $conditional_logic = $other_settings_fields[1];
    unset($other_settings_fields[1]);

    $other_settings_fields[] = array(
      'name'      => 'purchaseInformation',
      'label'     => esc_html__( 'Purhase Description', 'gravityformsbayarcash' ),
      'type'      => 'field_map',
      'field_map' => $this->purchase_info_fields(),
      'tooltip'   => '<h6>' . esc_html__( 'Purchase Description', 'gravityformsbayarcash' ) . '</h6>' . esc_html__( 'Map your Form Fields to the available listed fields.', 'gravityformsbayarcash' )
    );


    $other_settings_fields[] = array(
      'name'        => 'cancelUrl',
      'label'       => esc_html__( 'Cancel URL', 'gravityformsbayarcash' ),
      'type'        => 'text',
      'placeholder' => 'https://example.com/pages',
      'tooltip'     => '<h6>' . esc_html__( 'Cancel URL', 'gravityformsbayarcash' ) . '</h6>' . esc_html__( 'Redirect to custom URL in the event of cancellation. Leaving blank will redirect back to form page in the event of cancellation. Note: You can set success behavior by setting confirmation redirect.', 'gravityformsbayarcash' )
    );

    $other_settings_fields[] = $conditional_logic;

    return $other_settings_fields;
  }
  
  // This method must return empty array to prevent option from showing in feeds settings
  public function option_choices() {
    return array();
  }
  
   public function client_info_fields() {

    $client_info_fields = array(
      array( 'name' => 'email',     'label' => esc_html__( 'Email', 'gravityformsbayarcash' ), 'required' => true ),
      array( 'name' => 'full_name', 'label' => esc_html__( 'Full Name', 'gravityformsbayarcash' ), 'required' => false ),
    );

    return apply_filters( 'gf_bayarcash_client_info_fields', $client_info_fields );
  }

  public function purchase_info_fields() {
    $purchase_info_fields = array(
      array( 'name' => 'notes', 'label' => esc_html__( 'Purchase Note', 'gravityformsbayarcash' ), 'required' => false ),
    );

    return apply_filters( 'gf_bayarcash_purchase_info_fields', $purchase_info_fields );
  }
  
  public function redirect_url($feed, $submission_data, $form, $entry) {
      
    $entry_id = $entry['id'];
    
    $this->log_debug( __METHOD__ . "(): Started for entry id: #" . $entry_id );
    
    $configuration_type = rgars( $feed, 'meta/bayarcashConfigurationType', 'global' );

    $bayarcash_account = rgars( $feed, 'meta/bayarcash_account');

    $payment_amount_location = rgars( $feed, 'meta/paymentAmount'); // location for payment amount
    $name_location           = rgars( $feed, 'meta/clientInformation_full_name'); // location for buyer name
    $email_location          = rgars( $feed, 'meta/clientInformation_email'); // location for buyer email address
    $notes_location          = rgars( $feed, 'meta/purchaseInformation_notes'); // location for purchase notes
    $reference_location      = rgars( $feed, 'meta/miscellaneous_reference'); // location for reference

    $full_name_location_array = array();

    foreach ( $form['fields'] as $field ) {
      if ( $field->type == 'name' ) {
        if ($name_location != $field->id) {
          continue;
        }

        $full_name_location_array[$field->id] = array();
        foreach($field->inputs as $input) {
          $full_name_location_array[$field->id][] = $input['id'];
        }
      }
    }

    // This if the total amount choose to form total
    if ($payment_amount_location == 'form_total'){
      $amount       = rgar( $submission_data, 'payment_amount' );
      $product_name = rgar( $form, 'title' );
      $product_qty  = '1';
    } else {
      // This if the total amount choose to specific product.
      $items = rgar( $submission_data, 'line_items');
      foreach ($items as $item){
        if ($item['id'] == $payment_amount_location){
          $amount       = $item['unit_price'];
          $product_name = $item['name'];
          $product_qty  = $item['quantity'];
          break;
        }
      }
    }

    $currency  = rgar( $entry, 'currency' );
    $email     = rgar( $entry, $email_location );
    $notes     = rgar( $entry, $notes_location );
    $reference = rgar( $entry, $reference_location );
    $full_name = rgar( $entry, $name_location, '' );

    if ( !empty($full_name_location_array) ) {
      if ( array_key_exists( $name_location, $full_name_location_array ) ) {
        foreach( $full_name_location_array[$name_location] as $full_name_location ) {
          $full_name .= ' ' . rgar( $entry, $full_name_location );
        }
        $full_name = trim( $full_name );
      }
    }

    // if ( $gf_global_settings = get_option( 'gravityformsaddon_gravityformsbayarcash_settings' ) ) {
    //   $fpx_portal_key   = rgars( $gf_global_settings, 'fpx_portal_key' );
    //   $pat_key     = rgars( $gf_global_settings, 'pat_key' );
    // }

    if ($bayarcash_account) {
        
      $pat_key = get_post_meta($bayarcash_account, '_pat_key', true);
      $fpx_portal_key = get_post_meta($bayarcash_account, '_postal_key', true);

      }
    
    // if ($configuration_type == 'form'){
    //   $fpx_portal_key   = rgars( $feed, 'meta/fpx_portal_key' );
    //   $pat_key     = rgars( $feed, 'meta/pat_key' );
    // }
    
    $bayarcash = GFBayarcashAPI::get_instance( $fpx_portal_key, $pat_key );
    
    $redirect_url_args = array(
      'callback' => $this->_slug,
      'entry_id' => $entry_id,
    );

    // Define the POST data
    $postData = [
        'order_no' => $entry_id,
        'order_amount' => $amount,
        'buyer_name' => substr( $full_name, 0, 30 ),
        'buyer_email' => $email,
        'payment_gateway' => '1',
        'return_url' => $this->get_redirect_url( $redirect_url_args ),
        'portal_key' => $fpx_portal_key,
        'bearer_token' => $pat_key,
        
    ];
    
    
     $this->log_debug( __METHOD__ . "(): Params keys " . print_r( $postData, true ) );

    // Perform any necessary processing or validation on the submission data, form, or entry
    
    $payment = $bayarcash->create_payment( $postData );

}

 public function get_redirect_url($args = array()) {
    return add_query_arg(
      $args, 
      home_url( '/' ) 
    );
  }
  
  public function get_timezone(){
    if ( preg_match( '/^[A-z]+\/[A-z\_\/\-]+$/', wp_timezone_string() ) ) {
      return wp_timezone_string();
    }

    return 'UTC';
  }
  
 public function note_avatar() {
    return plugins_url("assets/logo.svg", __FILE__);
}


  
  public function callback() {
    global $wpdb;
    $entry_id = intval( rgget( 'entry_id' ) );
    //$this->log_debug( 'Started ' . __METHOD__ . "(): for entry id #" . $entry_id );

    $entry           = GFAPI::get_entry( $entry_id );
    $submission_feed = $this->get_payment_feed( $entry );
    $bayarcash_payment_id = gform_get_meta( $entry_id, 'bayarcash_payment_id' );
    
    //$this->log_debug( __METHOD__ . "(): Entry ID #$entry_id is set to Feed ID #" . $submission_feed['id'] );

   if (isset($_POST['fpx_pre_transaction_data'])) {
    $post_data = [
        'order_ref_no' => $_POST['fpx_pre_transaction_data']['fpx_exchange_order_number'],
        'order_no'     => $_POST['fpx_pre_transaction_data']['fpx_order_number'],
    ];

    if (empty($post_data['order_ref_no'])) {
        return;
    }
    
     // Store Bayarcash payment id
    $order_ref_no = $post_data['order_ref_no']; // Get the value of order_ref_no
    
    gform_update_meta($entry_id, 'bayarcash_payment_id', $order_ref_no, rgar($form, 'id'));
    $this->log_debug('Bayarcash ID ' . __METHOD__ . "(): for bayarcash id #" . $order_ref_no);
    $note2 = esc_html__( 'Payment Form Entry ID: ', 'gravityformsbayarcash' ). $entry_id;
    $note = esc_html__( 'Exchange Reference Number:  ', 'gravityformsbayarcash' ). $order_ref_no;
    $this->add_note( $entry['id'], $note2, 'success' );
    $this->add_note( $entry['id'], $note, 'success' );
    
    }


    if (isset($_POST['fpx_data'])) {
     $is_portal_key_valid = $this->check_portal_key_valid($submission_feed);

    if (!$is_portal_key_valid) {
        exit('Mismatched data.');
    }
   
   $post_data = [
        'order_ref_no'                   => $_POST['order_ref_no'],
        'order_no'                       => $_POST['order_no'],
        'transaction_currency'           => $_POST['transaction_currency'],
        'order_amount'                   => $_POST['order_amount'],
        'buyer_name'                     => $_POST['buyer_name'],
        'buyer_email'                    => $_POST['buyer_email'],
        'buyer_bank_name'                => $_POST['buyer_bank_name'],
        'transaction_status'             => $_POST['transaction_status'],
        'transaction_status_description' => $_POST['transaction_status_description'],
        'transaction_datetime'           => $_POST['transaction_datetime'],
        'transaction_gateway_id'         => $_POST['transaction_gateway_id'],
    ];
   
    $payment_status = $this->get_payment_status_name($post_data['transaction_status']);
    $this->handlePayment($payment_status, $post_data);
    
    }
    
    $payment_status_bank = gform_get_meta( $entry_id, 'payment_status' );
    $order_amount = gform_get_meta( $entry_id, 'order_amount' );
    $transaction_status_description = gform_get_meta( $entry_id, 'transaction_status_description' );
    
    if ($payment_status_bank == 'Successful') {
        $payment_status_message = 'Payment is successful, handle successful payment from here.';
        $type = 'complete_payment';
    }

    if ($payment_status_bank == 'Unsuccessful') {
        $payment_status_message = 'Payment is unsuccessful, handle unsuccessful payment from here.';
        $type = 'fail_payment';
    }

    $action = array(
      'id'             => $bayarcash_payment_id,
      'type'           => $type,
      'transaction_id' => $bayarcash_payment_id,
      'entry_id'       => $entry_id,
      'payment_method' => 'FPX',
      'amount'         => $order_amount,
    );

    // Acquire lock to prevent concurrency
    $GLOBALS['wpdb']->get_results(
     "SELECT GET_LOCK('bayarcash_gf_payment', 15);"
   );

   if ( $this->is_duplicate_callback( $bayarcash_payment_id ) ) {
     $action['abort_callback'] = 'true';
   }

   $this->log_debug( 'End of ' . __METHOD__ . "(): params return value: " . print_r( $action, true ) );

    return $action;

}

public function post_callback( $callback_action, $result ) {
    
    $this->log_debug( 'Start of ' . __METHOD__ . "(): for entry id: #" . $callback_action['entry_id'] );

    // Release lock to enable concurrency
    $GLOBALS['wpdb']->get_results(
    "SELECT RELEASE_LOCK('bayarcash_gf_payment');"
  );

    $entry_id = $callback_action['entry_id'];
    $entry    = GFAPI::get_entry( $entry_id );
    $url      = rgar( $entry, 'source_url' );
    $message  = __( '. Payment failed. ', 'gravityformsbayarcash' );

    if ( $callback_action['type'] == 'complete_payment' ) {
      $entry_id = $callback_action['entry_id'];
      $form_id  = $entry['form_id'];
            
      $message = __( '. Payment successful. ', 'gravityformsbayarcash' );
      $url     = $this->get_confirmation_url( $entry, $form_id );
    } else {
      $submission_feed = $this->get_payment_feed($entry);
      $cancel_url      = rgars( $submission_feed, 'meta/cancelUrl' );

      if ( $cancel_url AND filter_var( $cancel_url, FILTER_VALIDATE_URL ) ) {
        $url = $cancel_url;
      }
    }

    // Output payment status
    echo esc_html( $message );

    // Output redirection link
    printf(
      '<a href="%1$s">%2$s</a>%3$s', esc_url( $url ), esc_html__( 'Click here', 'gravityformsbayarcash' ), esc_html__( ' to redirect confirmation page', 'gravityformsbayarcash' )
    );

    // Redirect user automatically
    echo '<script>window.location.replace(\''. esc_url_raw($url) . '\')</script>';
    $this->log_debug( 'End of ' . __METHOD__ . "(): for entry id: #" . $callback_action['entry_id'] );
  }
  
  // This method inspired by gravityformsstripe plugin
  public function get_confirmation_url( $entry, $form_id ) {
    $redirect_url_args = array(
      'gf_bayarcash_success' => 'true',
      'entry_id'        => $entry['id'],
      'form_id'         => $form_id
    );

    $redirect_url_args['hash'] = wp_hash( implode( $redirect_url_args ) );
    
    return add_query_arg(
      $redirect_url_args,
      rgar( $entry, 'source_url' )
    );
  }
  
  // This method inspired by gravityformsstripe plugin
  public function maybe_thankyou_page() {
    if ( !rgget( 'gf_bayarcash_success' ) OR !rgget( 'entry_id' ) OR !rgget( 'form_id' ) ) {
      return;
    }

    $entry_id = sanitize_key( rgget( 'entry_id' ) );
    $form_id  = sanitize_key( rgget( 'form_id' ) );
    $this->log_debug( __METHOD__ . "(): confirmation page for entry id: #" . $entry_id );

    if (wp_hash( 'true' . $entry_id . $form_id ) != rgget('hash')){
      $this->log_debug( __METHOD__ . "(): wp_hash failure for entry id: #" . $entry_id );
      return;
    }

    $form  = GFAPI::get_form( $form_id );
    $entry = GFAPI::get_entry( $entry_id );

    if ( ! class_exists( 'GFFormDisplay' ) ) {
      require_once( GFCommon::get_base_path() . '/form_display.php' );
    }

    $confirmation = GFFormDisplay::handle_confirmation( $form, $entry, false );

    if ( is_array( $confirmation ) && isset( $confirmation['redirect'] ) ) {
      $this->log_debug( __METHOD__ . "(): confirmation is redirect type for entry id: #" . $entry_id );
      header( "Location: {$confirmation['redirect']}" );
      exit;
    }

    GFFormDisplay::$submission[ $form_id ] = array(
      'is_confirmation'      => true,
      'confirmation_message' => $confirmation,
      'form'                 => $form,
      'lead'                 => $entry,
    );

    $this->log_debug( __METHOD__ . "(): confirmation is non redirect type for entry id: #" . $entry_id );
  }
  
  
  public function handlePayment($payment_status, $post_data )
{
    $post_response = print_r($post_data, true);

    $payment_status = $this->get_payment_status_name($post_data['transaction_status']);

    $order_ref_no = $post_data['order_ref_no'];
    
    $order_amount = $post_data['order_amount'];
    $entry_id = $post_data['order_no'];
    $transaction_gateway_id = $post_data['transaction_gateway_id'];
    $transaction_datetime = $post_data['transaction_datetime'];
    $transaction_status_description = $post_data['transaction_status_description'];
    $buyer_bank_name = $post_data['buyer_bank_name'];
    
    $this->log_debug('Bayarcash Status Description ' . __METHOD__ . "(): " . $transaction_status_description);
    $note3 = esc_html__( 'Bayarcash Status Description:  ', 'gravityformsbayarcash' ). $transaction_status_description;
    $this->add_note( $entry_id, $note3, 'success' );
    
    gform_update_meta($entry_id, 'order_amount', $order_amount);
    gform_update_meta($entry_id, 'transaction_gateway_id', $transaction_gateway_id);
    gform_update_meta($entry_id, 'transaction_datetime', $transaction_datetime);
    gform_update_meta($entry_id, 'transaction_status_description', $transaction_status_description);
    gform_update_meta($entry_id, 'buyer_bank_name', $buyer_bank_name);
    gform_update_meta($entry_id, 'payment_status', $payment_status);
}

public function check_portal_key_valid($submission_feed) {
    $configuration_type = rgars($submission_feed, 'meta/bayarcashConfigurationType', 'global');
    $bayarcash_account = rgars( $submission_feed, 'meta/bayarcash_account');

    // if ($gf_global_settings = get_option('gravityformsaddon_gravityformsbayarcash_settings')) {
    //     $fpx_portal_key = rgar($gf_global_settings, 'fpx_portal_key');
    //     $pat_key        = rgar($gf_global_settings, 'pat_key');
    // }

    // if ($configuration_type == 'form') {
    //     $fpx_portal_key = rgars($submission_feed, 'meta/fpx_portal_key');
    //     $pat_key        = rgars($submission_feed, 'meta/pat_key');
    // }

    if ($bayarcash_account) {
        
      $pat_key = get_post_meta($bayarcash_account, '_pat_key', true);
      $fpx_portal_key = get_post_meta($bayarcash_account, '_postal_key', true);

      }

    $fpx_hashed_data_from_portal = $_POST['fpx_data']; // Create a variable alias since we are going to remove $_POST['fpx_data'].

    unset($_POST['fpx_data']); // Remove this POST parameter since we are going to construct a source string and compare it with MD5 hashed data sent from the portal.

    $fpx_hashed_data_to_compare = md5($fpx_portal_key . json_encode($_POST)); // Construct the source string same as defined at the portal.

    return $fpx_hashed_data_to_compare == $fpx_hashed_data_from_portal;
}

public function get_payment_status_name($payment_status_code) {
    $payment_status_name_list = [
        'New',
        'Pending',
        'Unsuccessful',
        'Successful',
        'Cancelled',
    ];

    $is_Id = array_key_exists($payment_status_code, $payment_status_name_list);

    if (!$is_Id) {
        return;
    }

    return $payment_status_name_list[$payment_status_code];
}
  public function uninstall() {
    $option_names = array(
      'gf_bayarcash_global_key_validation',
      'gf_bayarcash_global_error_code'
    );
    
    foreach( $option_names as $option_name ){
      delete_option( $option_name );
    }

    parent::uninstall();
  }

}