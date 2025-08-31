<?php
/*
 P l*ugin Name: WP Content Restrictor
 Description: Restricts access to content containing specific keywords for non-logged-in users from Russia, redirecting them to a specified page. Checks URLs, post titles, and content. Originally developed for rucore.net, uses example.com for demonstration.
 Version: 1.0.0
 Author: vsemke
 License: MIT
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'template_redirect', 'wp_content_restrictor_redirect' );
function wp_content_restrictor_redirect() {
    // Skip logged-in users
    if ( is_user_logged_in() ) {
        return;
    }

    // Skip legal bots
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $is_legal_bot = preg_match('/(Googlebot|Slurp|MSNBot|Teoma|ia_archiver|StackRambler|WebAlta|Aport|Mail.Ru|Yandex|bingbot|Rambler|LinkpadBot|WebArtexBot|Web-Monitoring|YaDirectFetcher|Yahoo|YandexDirect|YandexImages|YandexMetrika|YandexWebmaster|OdklBot|YandexVideo|YandexMedia|Yandex|mail|msnbot)/i', $user_agent);
    if ( $is_legal_bot ) {
        return;
    }

    // Comprehensive list of blocked keywords
    $blocked_keywords = array_map('strtolower', array(
        // VPN and bypass
        'vpn', 'впн', 'v p n', 'випиэн', 'v2ray', 'v-2-ray', 'v2 ray', 'socks', 'socks5', 'socks 5', 'vless', 'v-less', 'vmess', 'v-mess', 'trojan', 'proxy', 'прокси', 'анонимайзер', 'anonymizer', 'shadowsocks', 'shadow socks', 'openvpn', 'open vpn', 'wireguard', 'wire guard', 'blokirov', 'блокировка', 'blockirovka', 'обход', 'obhod', 'обход блокировки', 'bypass', 'байпас', 'доступ к заблокированным сайтам', 'access to blocked sites', 'разблокировка', 'unblock', 'tor', 'тор', 'i2p', 'freenet', 'zeronet', 'обход цензуры', 'censorship bypass',
        // Hacking and vulnerabilities
        'взлом', 'хак', 'хакер', 'hacker', 'хакинг', 'hacking', 'уязвимость', 'vulnerability', 'эксплойт', 'exploit', 'киберпреступление', 'cybercrime', 'бэкдор', 'backdoor', 'руткит', 'rootkit', 'социальная инженерия', 'social engineering', 'фишинг', 'phishing', 'фишинговая атака', 'phishing attack', 'взлом пароля', 'password hack', 'взлом аккаунта', 'account hack', 'разблокировка устройства', 'device unlock', 'jailbreak', 'джейлбрейк', 'icloud bypass', 'обход icloud', 'frp bypass', 'обход frp', 'хактивация', 'hacktivation', 'взлом wi-fi', 'wifi hack', 'wi-fi hack', 'взлом вайфай', 'взлом почты', 'email hack', 'подмена сообщений', 'message spoofing', 'удаленный взлом', 'remote hack', 'брутфорс', 'bruteforce', 'кейлоггер', 'keylogger', 'троян', 'trojan', 'ботнет', 'botnet', 'ddos', 'ддос', 'sql injection', 'sql-инъекция', 'xss', 'cross-site scripting', 'csrf', 'cross-site request forgery', 'malware', 'малварь', 'шпионское по', 'spyware', 'рансомварь', 'ransomware', 'скрипт кидди', 'script kiddie', 'даркнет', 'darknet', 'дар Gson darkweb', 'onion', 'онион', 'кража данных', 'data breach', 'взлом базы данных', 'database hack',
        // Extremism
        'экстремизм', 'extremism', 'терроризм', 'terrorism', 'запрещенные материалы', 'prohibited materials', 'разжигание розни', 'incitement of hatred', 'запрещенная символика', 'prohibited symbols', 'пропаганда ненависти', 'hate propaganda', 'экстремистский контент', 'extremist content'
    ));

    // Check URL
    $request_uri = $_SERVER['REQUEST_URI'];
    $has_blocked_keyword = false;
    foreach ( $blocked_keywords as $keyword ) {
        if ( stripos( $request_uri, $keyword ) !== false ) {
            $has_blocked_keyword = true;
            break;
        }
    }

    // Check post title and content
    if ( is_singular() ) {
        global $post;
        if ( $post ) {
            $post_title = $post->post_title;
            $post_content = $post->post_content;

            foreach ( $blocked_keywords as $keyword ) {
                if ( stripos( $post_title, $keyword ) !== false || stripos( $post_content, $keyword ) !== false ) {
                    $has_blocked_keyword = true;
                    break;
                }
            }
        }
    }

    // Check geolocation and redirect
    if ( $has_blocked_keyword ) {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $geo = @json_decode( @file_get_contents( "http://ip-api.com/json/{$user_ip}" ) );
        if ( $geo && strtolower( $geo->countryCode ) == 'ru' ) {
            wp_redirect( 'https://example.com/content-removed/', 301 );
            exit;
        }
    }
}
?>
