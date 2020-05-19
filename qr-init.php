<?php

class QR {

  public function __construct() {
    add_action( 'admin_menu', [ $this, 'create_menu' ] );
    //add_action( 'admin_init', [ $this, 'backend_settings_page' ] );
    add_action( 'admin_init', [$this, 'add_acf_variables'] );
    add_filter( 'acf/settings/show_admin', '__return_false' ); //hide ACF once setup is completed
    
    // add ACF to quickly handle custom field
    include_once( plugin_dir_path( __FILE__ ) . 'vendor/advanced-custom-fields/acf.php' );
    add_filter( 'acf/settings/path', array( $this, 'update_acf_settings_path' ) );
    add_filter( 'acf/settings/dir', array( $this, 'update_acf_settings_dir' ) );

    // handle custom field
    add_filter('acf/location/rule_types', array( $this, 'acf_location_rules_types') );
    add_filter('acf/location/rule_values/options_page', array( $this, 'acf_location_rule_values_option_page') );
    $this->setup_options();

    /* enable preview */
    if(isset($_GET['qr_preview']) && $_GET['qr_preview'] == 'true'){
      add_action( 'template_redirect', array($this,'render_page'));
    }
  }

  public function create_menu() {
    $page_title = 'Set title for Quote Reader';
    $menu_title = 'Quote Reader';
    $capability = 'edit_posts'; 
    $menu_slug  = 'quote-reader'; 
    $callback   = [ $this, 'backend_settings_page' ];
    $icon = plugin_dir_url(__FILE__ ) . '/assets/img/quotes.png';
    $pos = 2;
    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $icon, $pos );
  }

  /* handle AFC */
  public function update_acf_settings_path( $path ) {
    $path = plugin_dir_path( __FILE__ ) . 'vendor/advanced-custom-fields/';
    return $path;
  }

  public function update_acf_settings_dir( $dir ) {
    $dir = plugin_dir_url( __FILE__ ) . 'vendor/advanced-custom-fields/';
    return $dir;
  }

  /* add page_option to ACF */
  public function acf_location_rules_types( $choices ) {
    $choices['Admin']['options_page'] = 'options_page';
    return $choices;
  }
  public function acf_location_rule_values_option_page( $choices ) {
    $choices['quote-reader'] = 'Quote Reader';
    return $choices;
  } 

  /* register field group for admin page */
  public function setup_options() {
    if( function_exists('acf_add_local_field_group') ):

      register_field_group(array(
        'key' => 'group_5ebea9207ac71',
        'title' => 'custom title group',
        'fields' => array(
          array(
            'key' => 'field_5ebea92d7d87f',
            'label' => 'Set Custom Title',
            'name' => 'custom_title',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '',
              'class' => '',
              'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
          ),
        ),
        'location' => array(
          array(
            array(
              'param' => 'options_page',
              'operator' => '==',
              'value' => 'quote-reader',
            ),
          ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
      ));
      endif;
  }

  public function add_acf_variables() {
    acf_form_head();
  }

  public function backend_settings_page() { 
    do_action('acf/input/admin_head'); // Add ACF admin head hooks
    do_action('acf/input/admin_enqueue_scripts'); // Add ACF scripts 
    ?>
<div class="wrap">
  <h1>Quote Reader</h1>
  <?php
    $options = array(
        'id' => 'acf-form',
        'post_id' => 'options',
        'new_post' => false,
        'field_groups' => array( 'group_5ebea9207ac71' ),
        'return' => admin_url('admin.php?page=quote-reader'),
        'submit_value' => 'Save',
    );
    acf_form( $options );
    ?>
  <hr />
  Check out your <a target="_blank" href="<?= home_url()?>?qr_preview=true">Preview</a>
</div><?php
  }

  public function render_page() {
    $this->get_style();
    $this->get_script();
    get_header();
    $file= $this->get_skeleton();
    $template_tags = array(
      '{HTML}' => $this->get_quotes_container(),
    );
    echo strtr($file, $template_tags);
    get_footer();
    exit;
  }

  public function render_plugin() {
    $this->get_style();
    $this->get_script();
    echo $this->get_quotes_container();
  }

  public function get_skeleton() {
    $html = '<div class="preview-container"><div class="container">
        {HTML}
      </div></div>';
    return $html;
  }
  
  public function get_quotes_container() {
    ob_start();
    ?>
<div class="title-container">
  <h1><?= get_field( 'custom_title', 'option' ); ?></h1>
</div>
<div class="filter-container">
  <div class="row mr-0 ml-0">
    <div class="col col-md-4">
      <div class="form-group">
        <label for="filterName">Filter by author</label>
        <select class="form-control" id="filterName">
          <option value="">- </option>
        </select>
      </div>
    </div>
    <div class="col col-md-4">
      <div class="form-group">
        <label for="filterTopic">Filter by topic</label>
        <select class="form-control" id="filterTopic">
          <option value="">- </option>
        </select>
      </div>
    </div>
    <div class="col col-md-4">
      <label for="search">Search</label>
      <div class="input-group">
        <input type="text" class="form-control" id="search" value="">
        <div class="input-group-append">
          <button class="btn btn-outline-custom" id="launch">Go!</button>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="qr-grid">
  <div class="loading">
    <span class="loader loader-bars"><span></span></span>
    Loading...
  </div>
</div> <?php
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
    }

  public function get_style() {
    wp_enqueue_style('quote-reader-bs', plugin_dir_url(__FILE__).'template/bootstrap.min.css', array());
    wp_enqueue_style('quote-reader-custom', plugin_dir_url(__FILE__).'template/style.css', array( 'quote-reader-bs' ));
  }

  public function get_script() {
    wp_enqueue_script('quote-reader-bs', 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
    wp_enqueue_script('quote-reader-masonry', 'https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js');
    wp_enqueue_script('quote-reader-custom', plugin_dir_url(__FILE__).'template/script.js');
  }
}
?>