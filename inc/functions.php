<?php

use classes\InvoiceData;
use classes\PersonalData;
use classes\ProductData;

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
        $mappedProduct->narrowGlazing = $product['narrowGlazing'] ?? '';
        $mappedProduct->height = $product['height'] ?? '';
        $mappedProduct->width = $product['width'] ?? '';
        $mappedProduct->frame = $product['frame'] ?? '';
        $mappedProduct->transport = $product['transport'] ?? '';
        $mappedProduct->glassImitation = $product['glassImitation'] ?? '';
        $mappedProduct->oldGlassRemoval = $product['oldGlassRemoval'] ?? '';
        $mappedProduct->finalPrice = $product['finalPrice'] ?? 0.0;
        $mappedProduct->glassThickness = $product['glassThickness'] ?? '';
        $mappedProduct->glassStructure = $product['glassStructure'] ?? '';
        $mappedProduct->replacementWork = $product['replacementWork'] ?? '';
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

function log_message($message)
{
    $log_file = plugin_dir_path(__FILE__) . 'pdf_generation.log';
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}