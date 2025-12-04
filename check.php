<?php
error_reporting(0);
header('Content-Type: application/json');

$cookie = $_POST['cookie'] ?? '';
if(empty($cookie)) die(json_encode(["status"=>"die","msg"=>"No cookie"]));

function curl($url, $cookie) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

// UID + Name
$uid = preg_match('/c_user=([^;]+)/', $cookie, $m) ? $m[1] : "N/A";
$name = "Unknown";
$pic = "https://graph.facebook.com/$uid/picture?type=large";

// Check live or die
$home = curl("https://mbasic.facebook.com/", $cookie);
if(strpos($home, 'id="logout_form"') === false && strpos($home, 'c_user') === false) {
    die(json_encode(["status"=>"die","uid"=>$uid,"name"=>$name]));
}

// Get name
preg_match('/<title>(.*?)<\/title>/', $home, $t);
$name = $t[1] ?? "Unknown";
if($name == "Facebook") $name = "Unknown";

// BM Count
$bm = curl("https://business.facebook.com/business_locations", $cookie);
$bm_count = substr_count($bm, '"id":"');

$response = [
    "status" => "live",
    "uid" => $uid,
    "name" => htmlspecialchars($name),
    "profile_pic" => $pic,
    "bm_count" => $bm_count,
    "2fa" => strpos($home, 'twofactor') !== false ? "On" : "Off",
    "token" => base64_encode($cookie) // simple token (real EAAB চাইলে আরো কোড লাগবে)
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
