<?php
/*
Plugin Name: Custom Number to Words
Description: A custom plugin to use the NumberToWords library.
Version: 1.2
Author: Your Name
*/

// Ensure the Composer autoload file is included
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use NumberToWords\NumberToWords;
use Dompdf\Dompdf;
use Dompdf\Options;

add_action('admin_post_generate_pdf', 'generate_pdf');
add_action('admin_post_nopriv_generate_pdf', 'generate_pdf');
add_action('wp_enqueue_scripts', 'enqueue_custom_assets');

// Main function to generate PDF
function generate_pdf()
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo 'No data received';
        return;
    }

    $filteredData = filter_and_map_data($data);
    $finalPrice = extract_final_price($filteredData);
    $amountInWords = convert_number_to_words($finalPrice);
    $html = generate_html($filteredData, $amountInWords);

    create_and_stream_pdf($html);
}

// Extract final price from the data
function extract_final_price($data)
{
    return array_reduce($data, function ($carry, $item) {
            return $item['label'] === 'Galutinė kaina' ? str_replace([' ', '€', ','], ['', '', '.'], $item['value']) : $carry;
        }, 0) * 100;
}

// Convert number to words
function convert_number_to_words($number)
{
    $numberToWords = new NumberToWords();
    $currencyTransformer = $numberToWords->getCurrencyTransformer('lt');
    return $currencyTransformer->toWords((float)$number, 'EUR');
}

// Generate HTML content for the PDF
function generate_html($data, $amountInWords)
{
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            * { font-family: DejaVu Sans, sans-serif; }
        </style>
    </head>
    <body>
        <h1>Invoice</h1>';

    foreach ($data as $field) {
        $html .= '<p>' . htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8') . '</p>';
    }

    $html .= '<p>Price in Words: ' . htmlspecialchars($amountInWords, ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '
    </body>
    </html>';

    return $html;
}

// Create and stream the PDF
function create_and_stream_pdf($html)
{
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isFontSubsettingEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("generated_pdf", array("Attachment" => 1));
}

// Enqueue custom CSS and JavaScript
function enqueue_custom_assets()
{
    wp_enqueue_script('custom-number-to-words-script', plugin_dir_url(__FILE__) . 'js/script.js', array(), filemtime(plugin_dir_path(__FILE__) . 'js/script.js'), true);
    wp_localize_script('custom-number-to-words-script', 'customNumberToWords', array(
        'generatePdfUrl' => admin_url('admin-post.php?action=generate_pdf')
    ));
}

// Filter and map data
function filter_and_map_data($data)
{
    $excludedLabels = ['Layout'];
    $labelMappings = [
        'Original Label' => 'Mapped Label'
        // Add more mappings as needed
    ];

    // Remove duplicates and filter out '*'
    $filteredData = [];
    $seenLabels = [];

    foreach ($data as $item) {
        if (empty($item['label']) || empty($item['value']) || in_array($item['label'], $excludedLabels)) {
            continue;
        }
        $item['label'] = str_replace('*', '', $item['label']);

        // Map labels
        $item['label'] = $labelMappings[$item['label']] ?? $item['label'];

        // Remove duplicates
        $uniqueKey = $item['label'] . '|' . $item['value'];
        if (!isset($seenLabels[$uniqueKey])) {
            $filteredData[] = $item;
            $seenLabels[$uniqueKey] = count($filteredData) - 1;
        }
    }

    return $filteredData;
}
