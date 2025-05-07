<?php
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
        preg_match('/X-CSRF-TOKEN:\s*(\S+)/i', $output, $matches);
        $csrf = isset($matches[1]) ? $matches[1] : null;

        curl_close($ch);
        
        return $csrf;
    }

    function refresh($cookie) {
        $csrf = csrf($cookie);

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

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 429) {
            return ["success" => true, "cookie" => $cookie];//fallback
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
            return ["success" => true, "cookie" => $cookie]; //fallback to normal cookie on
        }
        
        if (strpos($output, ".ROBLOSECURITY=") === false) {
            return ["success" => false, "cookie" => null];
        }

        $jewexplodevar = explode(";", explode(".ROBLOSECURITY=", $output)[1])[0];
        $cookie = str_replace('_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_', '', $jewexplodevar);
        
        return ["success" => !empty($jewexplodevar), "cookie" => $cookie];
    }

    $iamlonely = refresh($cookie);
    echo $iamlonely;


    function refresh($cookie) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://auth.roblox.com/v1/authentication-ticket",
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
            CURLOPT_URL => "https://auth.roblox.com/v1/authentication-ticket",
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
            CURLOPT_URL => "https://auth.roblox.com/v1/authentication-ticket/redeem",
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
        return ["success" => !empty($cookie), "cookie" => $cookie];
    };
}
?>
