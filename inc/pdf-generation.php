<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use NumberToWords\NumberToWords;

function create_and_stream_pdf($html): void
{
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isFontSubsettingEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4');
    $dompdf->render();
    $dompdf->stream("generated_pdf", array("Attachment" => 1));
}

function convert_number_to_words($number): string
{
    $numberToWords = new NumberToWords();
    $currencyTransformer = $numberToWords->getCurrencyTransformer('lt');
    return $currencyTransformer->toWords((float)$number, 'EUR');
}

function generate_html($invoiceData, $amountInWords): bool|string
{
    $invoice_number = get_next_invoice_number();
    $today_date = get_today_date_formatted();

    // Relative paths for the images
    $logo_path = 'assets/logo.jpeg';
    $signature_path = 'assets/signature.jpeg';

    // Convert images to base64
    $logo_base64 = get_base64_image($logo_path);
    $signature_base64 = get_base64_image($signature_path);

    ob_start();
    // Start of template content
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice</title>
        <style>
            * {
                font-family: DejaVu Sans, sans-serif;
            }

            body {
                font-size: 10px;
                line-height: 1.6;
            }

            .container {
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                text-align: center;
            }

            .header {
                text-align: center;
                position: relative;
            }

            .header img {
                max-width: 100px;
                position: absolute;
                top: 0;
                left: 0;
            }

            .header h1 {
                margin: 0;
                font-size: 14px;
            }

            .header h2, .header h3, .header h4 {
                margin: 0;
                font-size: 12px;
            }

            .section {
                margin-top: 20px;
            }

            .section table {
                width: 100%;
                border-collapse: collapse;
                margin: 0 auto;
            }

            .section table, .section th, .section td {
                border: 1px solid #000;
            }

            .section th, .section td {
                padding: 10px;
                text-align: left;
            }

            .signature {
                margin-top: 40px;
                text-align: left;
                display: table;
                width: 100%;
            }

            .signature .sig-col {
                display: table-cell;
                width: 48%;
                vertical-align: top;
                text-align: left;
            }

            .signature img {
                max-height: 70px;
            }

            .total {
                margin-top: 20px;
                text-align: left;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="header">
            <img src="<?= $logo_base64 ?>" alt="Logo">
            <h2>Išankstinė sąskaita – faktūra</h2>
            <h3>Serija SPI Nr.: <?= $invoice_number ?></h3>
            <h4>Data: <?= $today_date ?></h4>
        </div>

        <div class="section">
            <table>
                <tr>
                    <th>Seller/Pardavėjas</th>
                    <th>Customer/Pirkėjas</th>
                </tr>
                <tr>
                    <td>
                        UAB "Elnisa"<br>
                        Reg. adresas: Kontininkų g. 3B K8 Palanga<br>
                        Buveinės adresas: Lukšio g. 7 Vilnius<br>
                        Tel.: +37065880875, Tel.: +37063009290<br>
                        Įmonės kodas : 306691104<br>
                        A/S NR.: LT 507300010185034804<br>
                        "Swedbank" AB
                    </td>
                    <td>
                        <?php if (!empty($invoiceData->companyName)) { ?>
                            Įmonės pavadinimas: <?= $invoiceData->companyName; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->address)) { ?>
                            Adresas: <?= $invoiceData->address; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->mobile)) { ?>
                            Tel.: <?= $invoiceData->mobile; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->email)) { ?>
                            El. paštas: <?= $invoiceData->email; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->companyCode)) { ?>
                            Įmonės kodas: <?= $invoiceData->companyCode; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->pvmCode)) { ?>
                            PVM kodas: <?= $invoiceData->pvmCode; ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <table>
                <tr>
                    <th>Prekės</th>
                    <th>Kiekis</th>
                    <th>Kaina</th>
                    <th>Suma</th>
                </tr>
                <tr>
                    <td><?= $invoiceData->glassPackageStructure; ?> - <?= $invoiceData->glassPackageThickness; ?>
                        mm. <?= $invoiceData->frameType; ?></td>
                    <td><?= $invoiceData->quantity; ?></td>
                    <td><?= $invoiceData->finalPrice; ?></td>
                    <td><?= (float) $invoiceData->finalPrice * $invoiceData->quantity; ?> €</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right;">Viso suma :</td>
                    <td><?= $invoiceData->finalPrice; ?> €</td>
                </tr>
            </table>
        </div>

        <p class="total">Suma žodžiais : <?= $amountInWords; ?></p>
        <div class="signature">
            <div class="sig-col">
                <p>Pardavėjas :</p>
                <p>Direktorius Gytis Sereika</p>
                <img src="<?= $signature_base64 ?>" alt="Signature">
                <p>AV</p>
            </div>
            <div class="sig-col">
                <p>Pirkėjas :</p>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    // End of template content
    $html = ob_get_clean();

    return $html;
}