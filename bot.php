<?php
session_start();

function IPnya() {
    // Fungsi untuk mendapatkan alamat IP di sini...
}

if ($_POST['url_form']) {
    $urls = trim(htmlspecialchars($_POST['url_form']));
    $urls = explode("\n", $urls);
    $urls = array_filter($urls, 'trim');
}
if (!$urls) {
    exit;
}

?>

<div style="margin: auto; width: 525px; min-width: 525px">
    <table width="525" cellpadding="5" cellspacing="5" style="text-position: left">
        <thead style="text-align: left">
            <tr><th>ID</th><th>URL</th><th>DA</th><th>PA</th><th>MR</th><th>EL</th></tr>
        </thead>
        <tbody>
            <?php
            $hitung = 0;
            $urlx = array();
            $verif_url = array_chunk($urls, 80);
            foreach ($verif_url as $chunk) {
                sleep(2);
                unset($url);
                $url = $chunk;
                $seo = API_MOZ($url);
                if ($seo['error'] != '') {
                    echo "Error[SEOMoz]: " . $seo['error'] . "<br>";
                } else {
                    foreach ($seo as $index => $data) {
                        $urls['pa'] = number_format($data['pa'], 0, '.', '');
                        $urls['url'] = $data['url'];
                        $urls['da'] = number_format($data['da'], 0, '.', '');
                        $urls['title'] = $data['title'];
                        $urls['external_links'] = $data['external_links'];
                        $urls['mozrank'] = number_format($data['mozrank'], 2, '.', '');
                        $hitung++;
                        echo "<tr><td>";
                        echo $hitung;
                        echo "</td><td>";
                        echo str_replace("http://", "", $urls['url']);
                        echo "</td><td>";
                        echo $urls['da'];
                        echo "</td><td>";
                        echo $urls['pa'];
                        echo "</td><td>";
                        echo $urls['mozrank'];
                        echo "</td><td>";
                        echo $urls['external_links'];
                        echo "</td>";
                        echo "</tr>";
                        $urlx[] = $urls;
                    }
                }
            }
            ?>
        </tbody>
    </table>
    <br><br>
    <center>
        Want to check Alexa Rank to?<a href="https://tools.jakartaparanoid.com/seo/alexacheck/"> Click Here!</a>
        <?php
        $ipaddress = $_SERVER['REMOTE_ADDR'];
        echo "<center>";
        echo "<p>Your IP:<br>";
        echo IPnya();

        $_SESSION['urlx'] = htmlspecialchars($urlx);
        if (!empty($urlx)) { }
        ?>
    </center>
</div>

<?php

function API_MOZ($objectURL) {
    // cek https://moz.com/products/api/keys untuk mendapatkan accessID dan secretKey nya
    // your accessID
    $accessID = "mozscape-cf4fbc25ee";
    // your secretKey
    $secretKey = "1118df43a392ca46d24c692e428ebf73";
    $expires = time() + 600;
    $stringToSign = $accessID . "\n" . $expires;
    $binarySignature = hash_hmac('sha1', $stringToSign, $secretKey, true);
    $urlSafeSignature = urlencode(base64_encode($binarySignature));
    $cols = 68719476736 + 34359738368 + 536870912 + 32768 + 16384 + 2048 + 32 + 4;
    $requestUrl = "http://lsapi.seomoz.com/linkscape/url-metrics/?Cols=" . $cols . "&AccessID=" . $accessID . "&Expires=" . $expires . "&Signature=" . $urlSafeSignature;
    $batchedDomains = $objectURL;
    $encodedDomains = json_encode($batchedDomains);
    $options = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_POSTFIELDS => $encodedDomains);
    $ch = curl_init($requestUrl);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($content, true);
    $count = 0;
    if (isset($response['error_message'])) {
        $list = array('error' => htmlspecialchars($response['error_message']));
    } else {
        foreach ($response as $metric) {
            $list[$count]['url'] = $objectURL[$count];
            $list[$count]['subdomain'] = $metric['ufq'];
            $list[$count]['domain'] = $metric['upl'];
            $list[$count]['pa'] = $metric['upa'];
            $list[$count]['da'] = $metric['pda'];
            $list[$count]['mozrank'] = $metric['umrp'];
            $list[$count]['title'] = $metric['ut'];
            $list[$count]['external_links'] = $metric['ueid'];
            $count++;
        }
    }
    return $list;
}

?>
