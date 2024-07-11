<?php

use classes\InvoiceData;
use Dompdf\Dompdf;
use Dompdf\Options;

function create_and_stream_pdf($html): string
{
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isFontSubsettingEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4');
    $dompdf->render();
    $dompdf->stream("generated_pdf", array("Attachment" => 1));

    $output = $dompdf->output();

    return upload_pdf_to_wordpress($output);
}

function generate_html(InvoiceData $invoiceData): bool|string
{
    $company_name = get_company_name();
    $invoice_number = get_next_invoice_number();
    $today_date = get_today_date_formatted();
    $amountInWords = convert_number_to_words($invoiceData->finalPrice);

    ob_start();
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
            <h1><?= $company_name ?></h1>
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
                        <?php if (!empty($invoiceData->personalData->name)) { ?>
                            Vardas pavardė: <?= $invoiceData->personalData->name; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->personalData->companyName)) { ?>
                            Įmonės pavadinimas: <?= $invoiceData->personalData->companyName; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->personalData->address)) { ?>
                            Adresas: <?= $invoiceData->personalData->address; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->personalData->mobile)) { ?>
                            Tel.: <?= $invoiceData->personalData->mobile; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->personalData->email)) { ?>
                            El. paštas: <?= $invoiceData->personalData->email; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->personalData->companyCode)) { ?>
                            Įmonės kodas: <?= $invoiceData->personalData->companyCode; ?><br>
                        <?php } ?>
                        <?php if (!empty($invoiceData->personalData->pvmCode)) { ?>
                            PVM kodas: <?= $invoiceData->personalData->pvmCode; ?>
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
                <?php foreach ($invoiceData->products as $product) { ?>
                    <tr>
                        <td><?= $product->getDescription() ?></td>
                        <td><?= $product->quantity ?></td>
                        <td><?= $product->basePrice ?> €</td>
                        <td><?= $product->totalPrice ?> €</td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="3" style="text-align:right;">Viso suma :</td>
                    <td><?= $invoiceData->finalPrice ?> €</td>
                </tr>
            </table>
        </div>

        <p class="total">Suma žodžiais : <?= $amountInWords; ?></p>
        <div class="signature">
            <div class="sig-col">
                <p>Pardavėjas :</p>
                <p>Direktorius Gytis Sereika</p>
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
    $html = ob_get_clean();
    return $html;
}