<?php declare(strict_types=1);
/**
 * Parses all files in the subfolder "/input" for Crefo-IDs and balance information.
 *
 * @author    Johannes Schäfer <mail@johannes-schaefer.de>
 * @copyright Copyright (c) 2019
 * @license   https://mit-license.org
 */

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
date_default_timezone_set('Europe/Berlin');

$path = __DIR__ . '/input/';

$pattern = "(AKTIVA(?:.*?)<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\">&nbsp;</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">(?:(?:([0-9]+) in TEUR)|-)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">(?:(?:([0-9]+) in TEUR)|-)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">(?:(?:([0-9]+) in TEUR)|-)</SPAN></P>
</TD></TR>(.*?)PASSIVA(.*?)BILANZKENNZAHLEN(?:.*?)CREDITREFORM-NR.:\s*</SPAN><SPAN CLASS=\"c[0-9]+\">([0-9]{10})</SPAN>)ims";

$subpatternAssets = "((?:<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Anlageverm&ouml;gen</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?(?:
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Umlaufverm&ouml;gen</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?(?:
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Forderungen aus Lieferungen und Leistungen</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?(?:
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Forderungen und sonstige Verm&ouml;gensgegenst&auml;nde</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?(?:
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Kassenbestand, Bundesbankguthaben, Guthaben bei Kreditinstituten und Schecks</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Summe Aktiva</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)ims";

$subpatternLiabilities = "((?:<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Eigenkapital</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?(?:
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">R&uuml;ckstellungen</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?(?:
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Verbindlichkeiten aus Lieferungen und Leistungen</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?(?:
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Verbindlichkeiten</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?(?:
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Verbindlichkeiten gegen&uuml;ber Kreditinstituten</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)?
<TR><TD WIDTH=\"40.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">Summe Passiva</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD><TD WIDTH=\"20.0%\" CLASS=\"c[0-9]+\"><P CLASS=\"c[0-9]+\"><SPAN CLASS=\"c[0-9]+\">([0-9-\.]+)</SPAN></P>
</TD></TR>)ims";

if( $handle = opendir($path) ) {

    $total = 0;
    $output = fopen(sprintf('%s/%s-crefo-output.csv', __DIR__, date('YmdHi')), "w+");
    fwrite($output, "sep=;\nCrefo-Nummer;Bilanzjahr 1;Anlagevermoegen;Umlaufvermoegen;Forderungen aus Lieferungen und Leistungen;Forderungen und sonstige Vermoegensgegenstaende;Kassenbestand, Bundesbankguthaben, Guthaben bei Kreditinstituten und Schecks;Summe Aktiva;Eigenkapital;Rueckstellungen;Verbindlichkeiten aus Lieferungen und Leistungen;Verbindlichkeiten;Verbindlichkeiten gegenueber Kreditinstituten;Summe Passiva;Bilanzjahr 2;Anlagevermoegen;Umlaufvermoegen;Forderungen aus Lieferungen und Leistungen;Forderungen und sonstige Vermoegensgegenstaende;Kassenbestand, Bundesbankguthaben, Guthaben bei Kreditinstituten und Schecks;Summe Aktiva;Eigenkapital;Rueckstellungen;Verbindlichkeiten aus Lieferungen und Leistungen;Verbindlichkeiten;Verbindlichkeiten gegenueber Kreditinstituten;Summe Passiva;Bilanzjahr 3;Anlagevermoegen;Umlaufvermoegen;Forderungen aus Lieferungen und Leistungen;Forderungen und sonstige Vermoegensgegenstaende;Kassenbestand, Bundesbankguthaben, Guthaben bei Kreditinstituten und Schecks;Summe Aktiva;Eigenkapital;Rueckstellungen;Verbindlichkeiten aus Lieferungen und Leistungen;Verbindlichkeiten;Verbindlichkeiten gegenueber Kreditinstituten;Summe Passiva\n");

    while( false !== ($file = readdir($handle) ) ) {

        if( in_array( $file, ['.', '..', '.DS_Store'] ) ) {
            continue;
        }

        echo sprintf("%s:", $file);

        $matches = [];
        $content = file_get_contents($path . $file);

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $lines = '';
        $year = [];

        for( $i = 0; $i < count($matches); $i++ ) {

            $id = $matches[$i][6];
            $year[1] = $matches[$i][1];
            $year[2] = $matches[$i][2];
            $year[3] = $matches[$i][3];

            $assetList = [];
            $liabilityList = [];

            preg_match($subpatternAssets, $matches[$i][4], $assetList);
            preg_match($subpatternLiabilities, $matches[$i][5], $liabilityList);

            $assets = [];
            $liabilities = [];

            for( $j = 0; $j < 3; $j++ ) {

                if(empty($year[$j+1])) {
                    $assets[$j+1] = ';;;;;';
                    $liabilities[$j+1] = ';;;;;';
                } else {
                    $asset = sprintf("%s;%s;%s;%s;%s;%s", $assetList[1+$j], $assetList[4+$j], $assetList[7+$j], $assetList[10+$j], $assetList[13+$j], $assetList[16+$j]);
                    $assets[$j+1] = str_replace(['.', '-'], '', $asset);

                    $liability = sprintf("%s;%s;%s;%s;%s;%s", $liabilityList[1+$j], $liabilityList[4+$j], $liabilityList[7+$j], $liabilityList[10+$j], $liabilityList[13+$j], $liabilityList[16+$j]);
                    $liabilities[$j+1] = str_replace(['.', '-'], '', $liability);
                }

            }

            $lines .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n", $id, $year[1], $assets[1], $liabilities[1], $year[2], $assets[2], $liabilities[2], $year[3], $assets[3], $liabilities[3]);

        }

        $total += $i;
        fwrite($output, $lines);
        unset($matches);

        echo sprintf(" %s matches\n", number_format($i));

    }

    closedir($handle);
    fclose($output);

    echo sprintf("Finished! %s matches in total\n", number_format($total));

}
