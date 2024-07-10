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
                border: 1px solid #000;
                box-sizing: border-box;
            }

            .header {
                text-align: center;
            }

            .header img {
                max-width: 100px;
            }

            .section {
                margin-top: 20px;
            }

            .section table {
                width: 100%;
                border-collapse: collapse;
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
            }

            .signature div {
                display: inline-block;
                width: 48%;
                text-align: center;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="header">
            <h1>StikloPaketai 24.lt</h1>
            <h2>Išankstinė sąskaita – faktūra
                Serija SPI Nr.: 00001
                Data: Metai Mėnuo d.
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
                                Buvienes Adresas: Luksio g. 7 Vilnius<br>
                                Tel.: +37065880875, Tel.: +37063009290<br>
                                Įmonės kodas : 306691104<br>
                                A/S NR. : LT 507300010185034804<br>
                                "Swedbank" AB
                            </td>
                            <td>
                                <?= $invoiceData->companyName; ?><br>
                                <?= $invoiceData->address; ?><br>
                                <?= $invoiceData->mobile; ?><br>
                                <?= $invoiceData->email; ?><br>
                                Įmonės kodas: <?= $invoiceData->companyCode; ?><br>
                                PVM kodas: <?= $invoiceData->pvmCode; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="section">
                    <table>
                        <tr>
                            <th>Prekes /Svoris</th>
                            <th>Kiekis</th>
                            <th>Svoris</th>
                            <th>Kaina</th>
                            <th>Suma</th>
                        </tr>
                        <tr>
                            <td>Šarvo durys - <?= $invoiceData->glassPackageStructure; ?>
                                - <?= $invoiceData->glassPackageThickness; ?> mm. <?= $invoiceData->frameType; ?></td>
                            <td>1,00</td>
                            <td></td>
                            <td><?= $invoiceData->finalPrice; ?></td>
                            <td><?= $invoiceData->finalPrice; ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align:right;">Viso suma :</td>
                            <td><?= $invoiceData->finalPrice; ?> €</td>
                        </tr>
                    </table>
                    <p>Suma žodžiais : <?= $amountInWords; ?></p>
                </div>

                <div class="signature">
                    <div>
                        <p>Pardavėjas :</p>
                        <p>Direktorius Gytis Sereika</p>
                        <p><img src="signature.png" alt="Signature" style="max-height: 50px;"></p>
                        <p>AV</p>
                    </div>
                    <div>
                        <p>Pirkėjas :</p>
                        <p></p>
                        <p></p>
                        <p></p>
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