<?php
// made by jud7 dm me on dc
if (isset($_GET['cookie'])) {
    $cookie = $_GET['cookie'];
    
    function csrf($cookie) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://auth.roblox.com/v2/login");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("{}")));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Cookie: .ROBLOSECURITY=$cookie"
        ));
        $output = curl_exec($ch);
        
        if (curl_errno($ch)) {
            die(curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 429) {
            return "ratelimited";
        }

        preg_match('/X-CSRF-TOKEN:\s*(\S+)/i', $output, $matches);
        $csrf = isset($matches[1]) ? $matches[1] : null;

        curl_close($ch);
        
        return $csrf;
    }

    function refresh($cookie) {
        $csrf = csrf($cookie);
        if ($csrf === "ratelimited") {
            return "ratelimited";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://auth.roblox.com/v1/authentication-ticket");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("{}")));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "origin: https://www.roblox.com",
            "Referer: https://www.roblox.com/games/920587237/Adopt-Me",
            "x-csrf-token: " . $csrf,
            "Cookie: .ROBLOSECURITY=$cookie"
        ));
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            die(curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 429) {
            return "ratelimited";
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $header_size);
        
        preg_match('/rbx-authentication-ticket:\s*([^\s]+)/i', $header, $matches);
        $authenticationTicket = isset($matches[1]) ? $matches[1] : null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://auth.roblox.com/v1/authentication-ticket/redeem");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("authenticationTicket" => $authenticationTicket)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "origin: https://www.roblox.com",
            "Referer: https://www.roblox.com/games/920587237/Adopt-Me",
            "x-csrf-token: " . $csrf,
            "RBXAuthenticationNegotiation: 1"
        ));
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            die(curl_error($ch));
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 429) {
            return "ratelimited";
        }
        
        if (strpos($output, ".ROBLOSECURITY=") === false) {
            return "invalid cookie";
        }

        $jewexplodevar = explode(";", explode(".ROBLOSECURITY=", $output)[1])[0];
        $cookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $jewexplodevar);
        
        return empty($jewexplodevar) ? "invalid cookie" : $cookie;
    }

    $iamlonely = refresh($cookie);
    echo $iamlonely;
}
?>


