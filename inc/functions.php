<?php

use classes\InvoiceData;
use classes\PersonalData;
use classes\ProductData;
use NumberToWords\NumberToWords;

function map_data_to_objects($data): InvoiceData
{
    $invoiceData = new InvoiceData();
    $invoiceData->personalData = map_personal_data($data['personalData'] ?? []);
    $invoiceData->finalPrice = $data['finalPrice'] ?? 0.0;
    $invoiceData->products = map_products($data['products'] ?? []);
    return $invoiceData;
}

function map_products(array $products): array
{
    $mappedProducts = [];
    foreach ($products as $product) {
        $mappedProduct = new ProductData();
        $mappedProduct->category = $product['category'] ?? '';
        $mappedProduct->glassPackageType = $product['glassPackageType'] ?? '';
        $mappedProduct->isNarrowGlazingNeeded = $product['isNarrowGlazingNeeded'] ?? '';
        $mappedProduct->height = $product['height'] ?? '';
        $mappedProduct->width = $product['width'] ?? '';
        $mappedProduct->frameType = $product['frameType'] ?? '';
        $mappedProduct->isTransportNeeded = $product['isTransportNeeded'] ?? '';
        $mappedProduct->hasGlassImitation = $product['hasGlassImitation'] ?? '';
        $mappedProduct->isOldGlassRemovalNeeded = $product['isOldGlassRemovalNeeded'] ?? '';
        $mappedProduct->finalPrice = $product['finalPrice'] ?? 0.0;
        $mappedProduct->glassThickness = $product['glassThickness'] ?? '';
        $mappedProduct->glassStructure = $product['glassStructure'] ?? '';
        $mappedProduct->isReplacementWorkNeeded = $product['isReplacementWorkNeeded'] ?? '';
        $mappedProduct->basePrice = $product['basePrice'] ?? 0.0;
        $mappedProduct->quantity = $product['quantity'] ?? 0;
        $mappedProduct->totalPrice = $product['totalPrice'] ?? 0.0;
        $mappedProduct->description = $mappedProduct->category;
        $mappedProducts[] = $mappedProduct;
    }
    return $mappedProducts;
}

function map_personal_data(array $personalData): PersonalData
{
    $mappedPersonalData = new PersonalData();
    $mappedPersonalData->companyName = $personalData['companyName'] ?? '';
    $mappedPersonalData->companyCode = $personalData['companyCode'] ?? '';
    $mappedPersonalData->pvmCode = $personalData['pvmCode'] ?? '';
    $mappedPersonalData->mobile = $personalData['mobile'] ?? '';
    $mappedPersonalData->address = $personalData['address'] ?? '';
    $mappedPersonalData->email = $personalData['email'] ?? '';
    $mappedPersonalData->name = $personalData['name'] ?? '';
    return $mappedPersonalData;
}

function get_next_invoice_number(): string
{
    $invoice_number = get_option('next_invoice_number', 1); // Get the current invoice number from the database, default to 1 if not set
    update_option('next_invoice_number', $invoice_number + 1); // Increment the invoice number and save it back to the database
    return str_pad($invoice_number, 5, '0', STR_PAD_LEFT); // Pad the number with zeros to make it 5 digits
}

function get_today_date_formatted(): string
{
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    return "Metai: $year Mėnuo: $month $day d.";
}

function convert_number_to_words($number): string
{
    $numberToWords = new NumberToWords();
    $currencyTransformer = $numberToWords->getCurrencyTransformer('lt');
    $price = (float)$number * 100;
    return $currencyTransformer->toWords($price, 'EUR');
}

function log_message($message): void
{
    if (WP_DEBUG_LOG) {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        $timestamp = date("Y-m-d H:i:s");
        file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}

function upload_pdf_to_wordpress(?string $output): string
{
    $upload_dir = wp_upload_dir();
    $unique_filename = 'generated_pdf_' . uniqid() . '.pdf';
    $pdf_path = $upload_dir['path'] . '/' . $unique_filename;
    file_put_contents($pdf_path, $output);
    return $pdf_path;
}

function get_company_name(): string
{
    return "Stiklopaketai24.lt";
}

function get_company_email(): string
{
    return "domkisonas@gmail.com";
}