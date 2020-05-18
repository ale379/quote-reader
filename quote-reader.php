<?php
/**
 * Plugin Name: Quote Reader
 * Description:  initiate and locate to a plugin title’s variable.
 * Version: 1.010
 * Author: Alessia Pota
 *
 */

/* define std stuff */

defined( 'ABSPATH' ) or die( 'Bye' );

if(!class_exists('QR')) require_once 'qr-init.php';
new QR();

/*set API routing */
add_action('rest_api_init', function () {
  register_rest_route( 'quote-reader/v1', 'quotes/',array( 
        'methods'  => 'GET',
        'callback' => 'quotes'
    ));
});

function quotes() {
  global $wpdb;
  $table_name = $wpdb->prefix . "quotereader";
  $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name" );

  return $retrieve_data;
}

function installer(){
    
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . "quotereader";

  if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
    $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(64) NOT NULL,
            surname varchar(64) NOT NULL,
            quote text NOT NULL,
            topic varchar(64) NOT NULL,
            UNIQUE KEY id (id)
          ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    $data = [
                [
                  'name' => 'Jeff',
                  'surname' => 'Buckley',  
                  'quote' => 'I just want to live completely adventurous, passionate, weird life',
                  'topic' => 'life'
                ],
                [
                  'name' => 'Jeff', 
                  'surname' => 'Buckley', 
                  'quote' => 'Somebody asked me what I wanted to do. I just said I wanted to…just to give back to it what it’s given me. And to meet all the other people that are doing it…just to be in the world, really.', 
                  'topic' => 'life'
                ],
                [
                    'name' => 'William H.', 
                  'surname' => 'McRaven', 
                  'quote' => 'If you want to change the world… be your very best in the darkest moments', 
                  'topic' => 'life'
                ],
                [
                  'name' => 'Jeff', 
                  'surname' => 'Lindsay', 
                  'quote' => 'You&apos; re driving me NORMAL!', 
                  'topic' => 'romance'
                ],
                [
                    'name' => 'Jeff', 
                  'surname' => 'Kinney', 
                  'quote' => 'Monkeys can\'t talk, stupid!', 
                  'topic' => 'science'
                ],
                [
                  'name' => 'William', 
                  'surname' => 'Shakespeare', 
                  'quote' => 'Love is a smoke made with the fume of sighs.', 
                  'topic' => 'romance'
                ],
                [
                  'name' => 'Kate', 
                  'surname' => 'Moore', 
                  'quote' => 'You fight and you fall and you get up and fight some more. But there will always come a day when you cannot fight another minute more.', 
                  'topic' => 'life'
                ]
              ];

    foreach( $data as $row) {
      $wpdb->insert( 
        $table_name, 
        $row
      );
    }
  }
}
register_activation_hook( __FILE__, 'installer' );



/*function filter($by) {
  global $wpdb;
  $table_name = $wpdb->prefix . "quotereader";
  $retrieve_data = $wpdb->get_results( "SELECT DISTINCT $by FROM $table_name" );

  return $retrieve_data;
}*/