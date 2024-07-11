<?php

use classes\InvoiceData;
use classes\PersonalData;
use classes\ProductData;

function map_data_to_object($data): InvoiceData
{
    $invoiceData = new InvoiceData();
    $personalData = new PersonalData();

    foreach ($data as $field) {
        if ($field['label'] === 'products') {
            $products = process_product_data($field['value']);
            $invoiceData->products = $products;
        } else {
            process_personal_data($personalData, $field);
        }
    }

    $invoiceData->personalData = $personalData;

    $invoiceData->finalPrice = array_reduce($invoiceData->products, function ($sum, $product) {
        return $sum + $product->totalPrice;
    }, 0.0);

    return $invoiceData;
}

function process_personal_data(PersonalData $personalData, $field): void
{
    $value = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');

    switch ($field['label']) {
        case 'Įmonės pavadinimas':
            $personalData->companyName = $value;
            break;
        case 'Įmonės kodas':
            $personalData->companyCode = $value;
            break;
        case 'PVM kodas':
            $personalData->pvmCode = $value;
            break;
        case 'Mobilusis':
            $personalData->mobile = $value;
            break;
        case 'Adresas':
            $personalData->address = $value;
            break;
        case 'El.Pašto adresas':
            $personalData->email = $value;
            break;
        case 'Vardas ir Pavardė':
            $personalData->name = $value;
            break;
    }
}

function process_product_data($productsArray): array
{
    $products = [];

    foreach ($productsArray as $productData) {
        $product = new ProductData();

        foreach ($productData as $productField) {
            $value = htmlspecialchars($productField['value'], ENT_QUOTES, 'UTF-8');
            switch ($productField['label']) {
                case 'Pasirinkite kategoriją':
                    $product->category = $value;
                    break;
                case 'Pasirinkite stiklo paketo tipą':
                    $product->glassPackageType = $value;
                    break;
                case 'Ar reikia siaurinti stiklajuoste?':
                    $product->narrowGlazing = $value;
                    break;
                case 'Stiklo paketo aukštis mm.':
                    $product->height = $value;
                    break;
                case 'Stiklo paketo plotis mm.':
                    $product->width = $value;
                    break;
                case 'Pasirinkti stiklo paketo rėmelį':
                    $product->frame = $value;
                    break;
                case 'Transportavimas':
                    $product->transport = $value;
                    break;
                case 'Imitacijos stiklo pakete':
                    $product->glassImitation = $value;
                    break;
                case 'Seno stiklo paketo išvežimas':
                    $product->oldGlassRemoval = $value;
                    break;
                case 'Galutinė kaina':
                    $product->finalPrice = (float)str_replace([' ', ','], ['', '.'], $value);
                    break;
                case 'Pasirinkite dviejų stiklo paketo storį':
                case 'Pasirinkite trijų stiklo paketo storį':
                    $product->glassThickness = $value;
                    break;
                case 'Pasirinkite stiklo paketo struktūrą':
                    $product->glassStructure = $value;
                    break;
                case 'Pakeitimo darbai':
                case 'Pakeitimo darbai klijuotos medienos':
                case 'Pakeitimo darbai šarvo durys':
                    $product->replacementWork = $value;
                    break;
                case 'Prekės':
                    $product->description = $value;
                    break;
                case 'Kiekis':
                    $product->quantity = (int)$value;
                    break;
                case 'Kaina':
                    $product->basePrice = (float)str_replace([' ', ','], ['', '.'], $value);
                    break;
            }
        }
        $product->totalPrice = $product->basePrice * $product->quantity;
        $products[] = $product;
    }

    return $products;
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
    return "Metai: $year Mėnuo: $month d.: $day";
}

function log_message($message)
{
    $log_file = plugin_dir_path(__FILE__) . 'pdf_generation.log';
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}