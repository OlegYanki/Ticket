<?php
ini_set('display_errors', 1);
$connection = mysql_connect("localhost", "pkskrakow_4", "Dond33st4S!");
$db = mysql_select_db("pkskrakow_4");
mysql_query("SET NAMES 'utf8'");


if (!$connection || !$db) {
    exit(mysql_error());
}
$select = "SELECT t.* FROM transactions t left JOIN userdata u on u.idUser = t.user_id WHERE t.id =275";

$query = mysql_query($select);
$transaction = mysql_fetch_assoc($query);

generatePDF($transaction);

function generatePDF($transaction)
{
    include_once('libs/mpdf50/mpdf.php');

    session_start();
    $mpdf = new mPDF('utf-8', '', 0, 'ebrima', 10, 10, 7, 7, 10, 10, "P");
    $mpdf->charset_in = 'utf-8';

    $mpdf->WriteHTML(file_get_contents('./css/ticket.css'), 1);
    $mpdf->WriteHTML(renderTicketPdf($transaction), 2);
    $mpdf->Output("testpdf.pdf", 'I');
}

function renderTicketPdf($transaction, $ticketNumber = null)
{
    include_once('d-line_engine/php/Ticket.php');

    $t = new Ticket($transaction);
    $tickets = json_decode($transaction['tickets'], true);
    $html = '';

    foreach ($tickets as $ticket) {

        for ($i = 0; $i < 3; $i++) {

            if ($ticketNumber && ($ticketNumber != $ticket['course_id'])) continue;

            $total_price = number_format(($ticket['current_price'] * $ticket['count']['count']), 2);
            $full_price = number_format($ticket['fullPrice'], 2);
            $net_price = number_format(($ticket['fullPrice'] - $ticket['price_vat']), 2);
            $vat_price = number_format(floatval($ticket['price_vat']), 2);
            $discount = number_format(($ticket['fullPrice'] - $ticket['current_price']), 2);

            $html .= '
<table border="1" style="width: 100%;">
    <tr style="display: none;">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="1" style="text-align: center; border: 1px solid black;height: 40px;"><img src="./img/logo_pdf_98x33.png" /></td>
        <td colspan="5" style="text-align: center; border: 1px solid black;"><span style="text-align: center"><small>Nr biletu</small><p><b class="sm-f">EL ' . $ticket['course_id'] . '</b></p></span></td>
        <td colspan="4" style="text-align: center; border: 1px solid black;"><span style="text-align: center"><small>Sposób sprzedaży</small><p><b class="sm-f">Internet</b></p></span></td>
        <td colspan="4" style="text-align: center; border: 1px solid black;"><span style="text-align: center"><small>Data sprzedaży</small><p><b class="sm-f">' . date('Y-m-d') . '</b></p></span></td>
        <td colspan="4" style="text-align: center; border: 1px solid black;"><span style="text-align: center"><small>Nr paragonu fiskalnego</small><p><b class="sm-f">' . $ticket['course_id'] . '</b></p></span></td>
    </tr>

    <tr>
        <td colspan="3" style="border-left:1px solid black;">
            <small>Sprzedawca (Przewoźnik)</small>
        </td>
        <td colspan="5"></td>
        <td colspan="2"><small class="vsm-f">NIP: ' . $ticket['carrier']['vat_number'] . '</small></td>
        <td style="border-left:1px solid black" colspan="3">
        <small class="vsm-f">Nabywca (Pasażer)</small></td>
        <td style="border-right: 1px solid black" colspan="5"></td>
    </tr>
    
    <tr>
        <td colspan="8" style="border-left:1px solid black;"><b>' . $ticket['carrier']['description'] . '</b></td>
        <td colspan="2"></td>
        <td  style="border-left:1px solid black" colspan="6"><b style="font-size:11px">' . $ticket['name'] . '  ' . $ticket['surName'] . '</b></td>
        <td style="border-right: 1px solid black" colspan="2"></td>
    </tr>
    
    <tr>
        <td colspan="8" style="border-left:1px solid black"><small class="vsm-f">' . $ticket['carrier']['address'] . '</small></td>
        <td colspan="2"></td>
        <td style="border-left:1px solid black" colspan="6"><small class="vsm-f">' . $ticket['address'] . '</small></td>
        <td style="border-right: 1px solid black" colspan="2"></td>
    </tr>
    
    <tr>
        <td colspan="3" style="border-left:1px solid black"><small>Tel: ' . $ticket['carrier']['phone'] . '</small></td>
        <td colspan="5"></td>
        <td colspan="2"></td>
        <td style="border-left:1px solid black" colspan="3"><small>Tel: ' . $ticket['phone'] . '</small></td>
        <td style="border-right: 1px solid black" colspan="5"></td>
    </tr>
    
    <tr>
        <td colspan="10" style="border-left:1px solid black; border-bottom:1px solid black; border-top:1px solid black">
        <small>Oferent:</small>
        </td>
        <td style="border:1px solid black;border-right: 0px" colspan="4">
            <b style="font-size: 8px;">Telefon do kierowcy:</b>
        </td>
        <td style="border-top:1px solid black;border-right: 1px solid black" align="right" colspan="4"><small>+48146371777,+48146371777</small></td>
    </tr>
    
    <tr>
        <div>
    <tr>
        <td class="gray no-borders-gray" style="border-top:1px solid black;border-right: 1px solid black" colspan="15" align="center">
            <small style="font-size:9px">Nazwa linii(kursu): </small>
            <b>'. $ticket["begin_station_locality"] .' - '. $ticket["end_station_locality"] .'</b>
        </td>
        <td style="border-right: 1px solid black" align="right" colspan="3" rowspan="1"><small>+48146371777,+48146371777</small></td>
    </tr>
    
    <tr style="padding:0px">
        <td class="gray no-borders-gray" style="border-left:1px solid black;" colspan="2"><small class="vsm-f">Data</small></td>
        <td class="gray no-borders-gray" colspan="2" align="center"><small class="vsm-f">Godzina</small></td>
        <td colspan="2" class="gray no-borders-gray"></td>
        <td class="gray no-borders-gray" colspan="4" style="padding:0"><small class="vsm-f">Miejscowość</small></td>
        <td class="gray no-borders-gray" colspan="2" align="right"><small class="vsm-f">Długość trasy </small></td>
        <td class="gray no-borders-gray" colspan="1" align="right"></td>
        <td style="border-right: 1px solid black" class="gray no-borders-gray" colspan="2" align="right"><b>' . $ticket['distance'] . ' km</b></td>
        <td colspan="3" style="border-right: 1px solid black"></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" class="gray no-borders-gray" colspan="2"><b>' . explode(' ', $ticket['departure'])[0] . '</b></td>
        <td class="gray no-borders-gray" colspan="2"><b>' . date_create_from_format('Y.m.d H:i:s', $ticket['departure'])->format('H:i') . '</b></td>
        <td colspan="2" class="gray no-borders-gray"></td>
        <td class="gray no-borders-gray" colspan="4"><b>' . $ticket['locality_from'] . '</b></td>
        <td style="border-right: 1px solid black" class="gray no-borders-gray" colspan="5"></td>
        <td style="border:1px solid black;" colspan="3" rowspan="14"><img style="padding-left: 4px; padding-bottom: 10px;" src="' . getQrCode($ticket) . '"></td>

    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" class="gray no-borders-gray" colspan="2"></td>
        <td class="gray no-borders-gray" colspan="2"></td>
        <td colspan="2" class="gray no-borders-gray"></td>
        <td class="gray no-borders-gray" colspan="4"><small>' . $ticket['station_from'] . '</small></td>
        <td class="gray no-borders-gray" colspan="5"></td>
        <td style="border-right: 1px solid black" colspan="3"></td>

    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" class="gray no-borders-gray" colspan="2"><b>' . explode(' ', $ticket['arrival'])[0] . '</b></td>
        <td class="gray no-borders-gray" colspan="2"><b>' . date_create_from_format('Y.m.d H:i:s', $ticket['arrival'])->format('H:i') . '</b></td>
        <td colspan="2" class="gray no-borders-gray"></td>
        <td class="gray no-borders-gray" colspan="7"><b>' . $ticket['locality_to'] . '	</b></td>
        <td class="gray no-borders-gray" colspan="2"></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black; border-bottom: 1px solid black" class="gray no-borders-gray" colspan="2"></td>
        <td style="border-bottom:1px solid black;" class="gray no-borders-gray" colspan="2"></td>
        <td style="border-bottom:1px solid black;" colspan="2" class="gray no-borders-gray"></td>
        <td style="border-bottom:1px solid bottom;" class="gray no-borders-gray" colspan="7"><small>' . $ticket['station_to'] . '</small></td>
        <td style="border-bottom:1px solid bottom;" class="gray no-borders-gray" colspan="2"></td>
    </tr>

    </div>
    
    </tr>

    <tr>
        <td style="border-left:1px solid black;" colspan="3"><small class="vsm-f">Rodzaj biletu</small></td>
        <td colspan="4"></td>
        <td colspan="2"><small class="vsm-f">Ilość pasażerów</small></td>
        <td style="border:1px solid black" rowspan="2" width="5px" align="center"><b>' . $ticket['count']['count'] . '</b></td>
        <td style="border-left:1px solid black" colspan="5"><small class="vsm-f">Cena:</small></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black;border-bottom: 1px solid black" ><b class="vsm-f">Międzynarodowy</b></td>
        <td style="border-bottom:1px solid black" ><b class="vsm-f">Normalny</b></td>
        <td style="border-bottom:1px solid black" colspan="8"></td>
        <td style="border-left:1px solid black" colspan="5"></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" colspan="4"><small class="vsm-f">Uwagi</small></td>
        <td colspan="1"></td>
        <td colspan="2"></td>
        <td colspan="2"></td>
        <td colspan="1"></td>
        <td style="border-left:1px solid black" colspan="1"><small class="vsm-f">Netto PL</small></td>
        <td colspan="3"></td>
        <td colspan="1" align="right"><b class="vsm-f">' . $net_price . '</b></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" colspan="4"></td>
        <td colspan="1"></td>
        <td colspan="2"></td>
        <td colspan="2"></td>
        <td colspan="1"></td>
        <td style="border-left:1px solid black" colspan="1"><small class="vsm-f">Vat PL</small></td>
        <td colspan="3"></td>
        <td colspan="1" align="right"><b class="vsm-f" style="font-weight: bold">' . $vat_price . ' zł</span></b></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black; border-bottom: 1px solid black" colspan="4"></td>
        <td style="border-bottom: 1px solid black" colspan="1"></td>
        <td style="border-bottom: 1px solid black" colspan="2"></td>
        <td style="border-bottom: 1px solid black" colspan="2"></td>
        <td style="border-bottom: 1px solid black" colspan="1"></td>
        <td style="border-left:1px solid black" colspan="1"><small class="vsm-f">Brutto zagr.</small></td>
        <td colspan="3"></td>
        <td colspan="1" align="right"><b class="vsm-f">' . $full_price . ' zł</b></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" colspan="4"><small class="vsm-f">Wystawca (Agent)</small></td>
        <td colspan="1"></td>
        <td colspan="2"><small class="vsm-f">NIP:</small></td>
        <td colspan="2"></td>
        <td colspan="1"></td>
        <td style="border-left:1px solid black" colspan="1"></td>
        <td colspan="3"></td>
        <td colspan="1"></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" colspan="4"><b class="vsm-f">Małopolskie Dworce Autobusowe S.A.</b></td>
        <td colspan="1"></td>
        <td colspan="2"></td>
        <td colspan="2"></td>
        <td colspan="1"></td>
        <td style="border-left:1px solid black" colspan="1"></td>
        <td colspan="3"></td>
        <td colspan="1"></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" colspan="4"><small class="vsm-f"> Kraków, al. Beliny-Prażmowskiego 6A/6</small></td>
        <td colspan="1"></td>
        <td colspan="2"></td>
        <td colspan="2"></td>
        <td colspan="1"></td>
        <td style="border-left:1px solid black" colspan="1"></td>
        <td colspan="3"></td>
        <td colspan="1"></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black;" colspan="4"><small class="vsm-f"> sprzedaży</small></td>
        <td colspan="1"></td>
        <td colspan="2"><small class="vsm-f">Tel: +48 146 371 777</small></td>
        <td colspan="1"></td>
        <td colspan="2"></td>
        <td style="border-left:1px solid black" colspan="1"></td>
        <td colspan="3"></td>
        <td colspan="1"></td>
    </tr>
    
    <tr>
        <td style="border-left:1px solid black; border-bottom: 1px solid black" colspan="4"><small class="vsm-f">31-505 Kraków, ul. Bosacka</small></td>
        <td style="border-bottom: 1px solid black" colspan="1"></td>
        <td style="border-bottom: 1px solid black" colspan="2"></td>
        <td style="border-bottom: 1px solid black" colspan="2"></td>
        <td style="border-bottom: 1px solid black" colspan="1"></td>
        <td style="border-left:1px solid black;border-bottom: 1px solid black" colspan="2"><b style="font-size: 11px">Razem</b></td>
        <td style="border-bottom: 1px solid black" colspan="2"></td>
        <td style="border-bottom: 1px solid black" colspan="1" align="right"><b style="font-size:11px">' . $total_price . ' zł</b></td>
    </tr>
</table>
<br>
        ';
        }
    }

    return $html;
}

function getQrCode($ticket)
{
    include_once('libs/phpqrcode/qrlib.php');

    // how to save PNG codes to server

    $tempDir = 'qrcodes/';

    $codeContents =
        'fullPrice - ' . $ticket['fullPrice'] .
        'currentPrice= ' . $ticket['current_price'] .
        'course_id=' . $ticket['course_id'] .
        'station_from=' . $ticket['station_from'] .
        'station_to=' . $ticket['station_to'];

    // we need to generate filename somehow,
    // with md5 or with database ID used to obtains $codeContents...
    $fileName = $ticket['course_id'] . '.png';

    $pngAbsoluteFilePath = $tempDir . $fileName;

    QRcode::png($codeContents, $pngAbsoluteFilePath);

    return $pngAbsoluteFilePath;
}

