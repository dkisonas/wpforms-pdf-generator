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

// Main function to generate PDF
function generate_pdf(): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo 'No data received';
        return;
    }

    $filteredData = filter_and_map_data($data);
    $invoiceData = map_data_to_object($filteredData);
    $finalPrice = extract_final_price($filteredData);
    $amountInWords = convert_number_to_words($finalPrice);
    $html = generate_html($invoiceData, $amountInWords);

    create_and_stream_pdf($html);
}

// Enqueue custom CSS and JavaScript
function enqueue_custom_assets(): void
{
    wp_enqueue_script('custom-number-to-words-script', plugin_dir_url(__FILE__) . 'js/script.js', array(), filemtime(plugin_dir_path(__FILE__) . 'js/script.js'), true);
    wp_localize_script('custom-number-to-words-script', 'customNumberToWords', array(
        'generatePdfUrl' => admin_url('admin-post.php?action=generate_pdf')
    ));
}