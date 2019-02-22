<?php declare(strict_types=1);
/**
 * Parses all files in the subfolder "/input" for Crefo-IDs and email addresses.
 *
 * @author    Johannes Schäfer <mail@johannes-schaefer.de>
 * @copyright Copyright (c) 2019
 * @license   https://mit-license.org
 */

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
date_default_timezone_set('Europe/Berlin');

$path = __DIR__ . '/input/';
$pattern = "(EMAIL:</SPAN><SPAN CLASS=\"([c0-9]+)\">&nbsp;</SPAN><SPAN CLASS=\"([c0-9]+)\">([a-z0-9\._%+-@]+)</SPAN>(.*?)CREDITREFORM-NR.:  </SPAN><SPAN CLASS=\"([c0-9]+)\">([0-9]{10})</SPAN>)ims";

if( $handle = opendir($path) ) {

    $total = 0;
    $output = fopen(__DIR__ . '/output.csv', "w+");
    fwrite($output, "sep=;\nCrefo-Nummer;Email\n");

    while( false !== ($file = readdir($handle) ) ) {

        if( in_array( $file, ['.', '..', '.DS_Store'] ) ) {
            continue;
        }

        echo sprintf("%s:", $file);

        $matches = [];
        $content = file_get_contents($path . $file);

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $lines = '';

        for( $i = 0; $i < count($matches); $i++ ) {

            $id = $matches[$i][6];
            $email = mb_strtolower($matches[$i][3]);

            $lines .= sprintf("%s;%s\n", $id, $email);

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
