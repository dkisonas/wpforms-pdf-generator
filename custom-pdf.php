<?php
/*
Plugin Name: Custom Number to Words
Description: A custom plugin to use the NumberToWords library.
Version: 1.3
Author: Your Name
*/

// Ensure the Composer autoload file is included
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'classes/InvoiceData.php';
require_once plugin_dir_path(__FILE__) . 'classes/ProductData.php';
require_once plugin_dir_path(__FILE__) . 'classes/PersonalData.php';
require_once plugin_dir_path(__FILE__) . 'inc/functions.php';
require_once plugin_dir_path(__FILE__) . 'inc/pdf-generation.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use NumberToWords\NumberToWords;

add_action('admin_post_generate_pdf', 'generate_pdf');
add_action('admin_post_nopriv_generate_pdf', 'generate_pdf');
add_action('wp_enqueue_scripts', 'enqueue_custom_assets');

function initialize_invoice_number() {
    if (get_option('next_invoice_number') === false) {
        add_option('next_invoice_number', 1);
    }
}
add_action('init', 'initialize_invoice_number');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Main function to generate PDF
function generate_pdf(): void
{
    try {
        log_message('generate_pdf called');

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            log_message('No data received');
            echo 'No data received';
            return;
        }

        log_message('Data received: ' . json_encode($data));

        $invoiceData = map_data_to_object($data);
        $amountInWords = convert_number_to_words($invoiceData->finalPrice);
        $html = generate_html($invoiceData, $amountInWords);

        create_and_stream_pdf($html);

    } catch (Exception $e) {
        log_message('Exception: ' . $e->getMessage());
    }
}

function enqueue_custom_assets() {
    // Register the modules
    wp_register_script_module(
        '@my-plugin/classes',
        plugin_dir_url(__FILE__) . 'js/classes.js'
    );

    wp_register_script_module(
        '@my-plugin/mapper',
        plugin_dir_url(__FILE__) . 'js/mapper.js',
        array('@my-plugin/classes')
    );

    // Register and enqueue the main script module
    wp_enqueue_script_module(
        '@my-plugin/script',
        plugin_dir_url(__FILE__) . 'js/script.js',
        array('@my-plugin/mapper', '@my-plugin/classes')
    );

    // Localize script for ajax URL
    wp_localize_script('custom-number-to-words-script', 'customNumberToWords', array(
        'generatePdfUrl' => admin_url('admin-post.php?action=generate_pdf')
    ));
}