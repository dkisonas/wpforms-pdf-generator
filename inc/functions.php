<?php

use classes\InvoiceData;

function extract_final_price($data): float
{
    foreach ($data as $item) {
        if ($item['label'] === 'Galutinė kaina') {
            $price = str_replace([' ', '€', ','], ['', '', '.'], $item['value']);
            return (float)$price * 100;
        }
    }
    return 0.0;
}

function filter_and_map_data($data): array
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

function map_data_to_object($data): InvoiceData
{
    $invoiceData = new InvoiceData();

    foreach ($data as $field) {
        switch ($field['label']) {
            case 'Įmonės pavadinimas':
                $invoiceData->companyName = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Įmonės kodas':
                $invoiceData->companyCode = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'PVM kodas':
                $invoiceData->pvmCode = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Mobilusis':
                $invoiceData->mobile = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Adresas':
                $invoiceData->address = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'El.Pašto adresas':
                $invoiceData->email = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Pasirinkite kategoriją':
                $invoiceData->category = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Pasirinkite stiklo paketo tipą':
                $invoiceData->glassPackageType = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Pasirinkite trijų stiklo paketo storį':
                $invoiceData->glassPackageThickness = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Pasirinkite stiklo paketo struktūrą 38mm':
                $invoiceData->glassPackageStructure = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Stiklo paketo aukštis mm.':
                $invoiceData->height = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Stiklo paketo plotis mm.':
                $invoiceData->width = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Pasirinkti stiklo paketo rėmeli':
                $invoiceData->frameType = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Transportavimas':
                $invoiceData->transport = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Imitacijos stiklo pakete':
                $invoiceData->glassImitation = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Seno stiklo paketo išvežimas':
                $invoiceData->oldGlassRemoval = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Pakeitimo darbai šarvo durys':
                $invoiceData->replacementWork = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
            case 'Galutinė kaina':
                $invoiceData->finalPrice = htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8');
                break;
        }
    }

    return $invoiceData;
}