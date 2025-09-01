////////////////////////////////////////////////////////////////////////

// перенаправляем людей кто не авторизован на страницу если они зашли на страницу с запрещенным контентом
add_action( 'template_redirect', 'redirect_if_content_contains_keywords' );
function redirect_if_content_contains_keywords() {
	// Пропускаем залогиненных пользователей и легальных ботов
	if ( is_user_logged_in() ) {
		return;
	}

	// Исключаем страницу редиректа, чтобы избежать зацикливания
	if ( strpos( $_SERVER['REQUEST_URI'], '/content-removed/' ) !== false ) {
		return;
	}

	$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
	$is_legal_bot = preg_match('/(Googlebot|Slurp|MSNBot|Teoma|ia_archiver|StackRambler|WebAlta|Aport|Mail\.Ru|Yandex|bingbot|Rambler|LinkpadBot|WebArtexBot|Web-Monitoring|YaDirectFetcher|Yahoo|YandexDirect|YandexImages|YandexMetrika|YandexWebmaster|OdklBot|YandexVideo|YandexMedia|mail|msnbot)/i', $user_agent);
	if ( $is_legal_bot ) {
		return;
	}

	// Работаем только с записями (posts)
	if ( ! is_singular('post') ) {
		return;
	}

	global $post;
	if ( ! $post ) {
		return;
	}

	$blocked_keywords = array(
		'vpn', 'впн', 'v p n', 'випиэн', 'v2ray', 'v-2-ray', 'v2 ray', 'socks', 'socks5', 'socks 5', 'vless', 'v-less', 'vmess', 'v-mess', 'trojan', 'proxy', 'прокси', 'анонимайзер', 'anonymizer', 'shadowsocks', 'shadow socks', 'openvpn', 'open vpn', 'wireguard', 'wire guard', 'blokirov', 'блокировка', 'blockirovka', 'обход', 'obhod', 'обход блокировки', 'bypass', 'байпас', 'доступ к заблокированным сайтам', 'access to blocked sites', 'разблокировка', 'unblock', 'tor', 'тор', 'i2p', 'freenet', 'zeronet', 'обход цензуры', 'censorship bypass',
		'взлом', 'хак', 'хакер', 'hacker', 'хакинг', 'hacking', 'уязвимость', 'vulnerability', 'эксплойт', 'exploit', 'киберпреступление', 'cybercrime', 'бэкдор', 'backdoor', 'руткит', 'rootkit', 'социальная инженерия', 'social engineering', 'фишинг', 'phishing', 'фишинговая атака', 'phishing attack', 'взлом пароля', 'password hack', 'взлом аккаунта', 'account hack', 'разблокировка устройства', 'device unlock', 'jailbreak', 'джейлбрейк', 'icloud bypass', 'обход icloud', 'frp bypass', 'обход frp', 'хактивация', 'hacktivation', 'взлом wi-fi', 'wifi hack', 'wi-fi hack', 'взлом вайфай', 'взлом почты', 'email hack', 'подмена сообщений', 'message spoofing', 'удаленный взлом', 'remote hack', 'брутфорс', 'bruteforce', 'кейлоггер', 'keylogger', 'троян', 'trojan', 'ботнет', 'botnet', 'ddos', 'ддос', 'sql injection', 'sql-инъекция', 'xss', 'cross-site scripting', 'csrf', 'cross-site request forgery', 'malware', 'малварь', 'шпионское по', 'spyware', 'рансомварь', 'ransomware', 'скрипт кидди', 'script kiddie', 'даркнет', 'darknet', 'дарквеб', 'darkweb', 'onion', 'онион', 'кража данных', 'data breach', 'взлом базы данных', 'database hack',
		'экстремизм', 'extremism', 'терроризм', 'terrorism', 'запрещенные материалы', 'prohibited materials', 'разжигание розни', 'incitement of hatred', 'запрещенная символика', 'prohibited symbols', 'пропаганда ненависти', 'hate propaganda', 'экстремистский контент', 'extremist content',
		'free vpn', 'бесплатный впн', 'vpn бесплатно', 'vpn free', 'vpn сервис', 'vpn service', 'vpn подключение', 'vpn connection',
		'анонимный прокси', 'anonymous proxy', 'private proxy', 'приватный прокси', 'купить прокси', 'buy proxy',
		'обход блокировок', 'bypass blocking', 'разблокировать сайт', 'unblock site', 'доступ к сайту', 'access to site',
		'взлом соцсетей', 'social network hack', 'взлом вк', 'vk hack', 'взлом одноклассников', 'odnoklassniki hack',
		'взлом инстаграм', 'instagram hack', 'взлом ватсап', 'whatsapp hack', 'взлом телеграм', 'telegram hack',
		'хакерские услуги', 'hacker services', 'заказать взлом', 'order hack', 'услуги хакера', 'hacker for hire',
		'скачать взлом', 'download hack', 'взлом программа', 'hack program', 'взлом игра', 'game hack', 'чит код', 'cheat code',
		'взлом пароля wifi', 'wifi password hack', 'взлом роутера', 'router hack', 'взлом компьютера', 'computer hack',
		'кряк', 'crack', 'кейген', 'keygen', 'серийный номер', 'serial number', 'активатор', 'activator',
		'обход авито', 'avito bypass', 'обход банка', 'bank bypass', 'обход блокировки ростелеком', 'rostelecom bypass',
		'даркнет рынок', 'darknet market', 'гидра онион', 'hydra onion', 'мега онион', 'mega onion',
		'запрещенный контент', 'prohibited content', 'нелегальный контент', 'illegal content'
	);

	$post_content = strtolower( $post->post_content );
	$post_title = strtolower( $post->post_title );
	$has_blocked_keyword = false;

	foreach ( $blocked_keywords as $keyword ) {
		if ( false !== strpos( $post_content, $keyword ) || false !== strpos( $post_title, $keyword ) ) {
			$has_blocked_keyword = true;
			break;
		}
	}

	if ( $has_blocked_keyword ) {
		$user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

		// Проверяем, что IP не локальный
		if ( ! in_array( $user_ip, array( '127.0.0.1', '::1' ) ) ) {
			$geo = json_decode( file_get_contents( "http://ip-api.com/json/{$user_ip}?fields=countryCode" ) );
			if ( isset( $geo->countryCode ) && strtolower( $geo->countryCode ) == 'ru' ) {
				wp_redirect( 'https://example.com/content-removed/', 301 );
				exit;
			}
		}
	}
}


////////////////////////////////////////////////////////////////////////////




// Вынесем список стоп-слов в отдельную функцию для повторного использования
function get_blocked_keywords() {
	return array(
		'vpn', 'впн', 'v p n', 'випиэн', 'v2ray', 'v-2-ray', 'v2 ray', 'socks', 'socks5', 'socks 5', 'vless', 'v-less', 'vmess', 'v-mess', 'trojan', 'proxy', 'прокси', 'анонимайзер', 'anonymizer', 'shadowsocks', 'shadow socks', 'openvpn', 'open vpn', 'wireguard', 'wire guard', 'blokirov', 'блокировка', 'blockirovka', 'обход', 'obhod', 'обход блокировки', 'bypass', 'байпас', 'доступ к заблокированным сайтам', 'access to blocked sites', 'разблокировка', 'unblock', 'tor', 'тор', 'i2p', 'freenet', 'zeronet', 'обход цензуры', 'censorship bypass',
		'взлом', 'хак', 'хакер', 'hacker', 'хакинг', 'hacking', 'уязвимость', 'vulnerability', 'эксплойт', 'exploit', 'киберпреступление', 'cybercrime', 'бэкдор', 'backdoor', 'руткит', 'rootkit', 'социальная инженерия', 'social engineering', 'фишинг', 'phishing', 'фишинговая атака', 'phishing attack', 'взлом пароля', 'password hack', 'взлом аккаунта', 'account hack', 'разблокировка устройства', 'device unlock', 'jailbreak', 'джейлбрейк', 'icloud bypass', 'обход icloud', 'frp bypass', 'обход frp', 'хактивация', 'hacktivation', 'взлом wi-fi', 'wifi hack', 'wi-fi hack', 'взлом вайфай', 'взлом почты', 'email hack', 'подмена сообщений', 'message spoofing', 'удаленный взлом', 'remote hack', 'брутфорс', 'bruteforce', 'кейлоггер', 'keylogger', 'троян', 'trojan', 'ботнет', 'botnet', 'ddos', 'ддос', 'sql injection', 'sql-инъекция', 'xss', 'cross-site scripting', 'csrf', 'cross-site request forgery', 'malware', 'малварь', 'шпионское по', 'spyware', 'рансомварь', 'ransomware', 'скрипт кидди', 'script kiddie', 'даркнет', 'darknet', 'дарквеб', 'darkweb', 'onion', 'онион', 'кража данных', 'data breach', 'взлом базы данных', 'database hack',
		'экстремизм', 'extremism', 'терроризм', 'terrorism', 'запрещенные материалы', 'prohibited materials', 'разжигание розни', 'incitement of hatred', 'запрещенная символика', 'prohibited symbols', 'пропаганда ненависти', 'hate propaganda', 'экстремистский контент', 'extremist content',
		// Дополнительные синонимы
		'free vpn', 'бесплатный впн', 'vpn бесплатно', 'vpn free', 'vpn сервис', 'vpn service', 'vpn подключение', 'vpn connection',
		'анонимный прокси', 'anonymous proxy', 'private proxy', 'приватный прокси', 'купить прокси', 'buy proxy',
		'обход блокировок', 'bypass blocking', 'разблокировать сайт', 'unblock site', 'доступ к сайту', 'access to site',
		'взлом соцсетей', 'social network hack', 'взлом вк', 'vk hack', 'взлом одноклассников', 'odnoklassniki hack',
		'взлом инстаграм', 'instagram hack', 'взлом ватсап', 'whatsapp hack', 'взлом телеграм', 'telegram hack',
		'хакерские услуги', 'hacker services', 'заказать взлом', 'order hack', 'услуги хакера', 'hacker for hire',
		'скачать взлом', 'download hack', 'взлом программа', 'hack program', 'взлом игра', 'game hack', 'чит код', 'cheat code',
		'взлом пароля wifi', 'wifi password hack', 'взлом роутера', 'router hack', 'взлом компьютера', 'computer hack',
		'кряк', 'crack', 'кейген', 'keygen', 'серийный номер', 'serial number', 'активатор', 'activator',
		'обход авито', 'avito bypass', 'обход банка', 'bank bypass', 'обход блокировки ростелеком', 'rostelecom bypass',
		'даркнет рынок', 'darknet market', 'гидра онион', 'hydra onion', 'мега онион', 'mega onion',
		'запрещенный контент', 'prohibited content', 'нелегальный контент', 'illegal content'
	);
}

// Функция для замены стоп-слов в заголовках
function replace_keywords_in_titles( $title, $id = null ) {
	// Пропускаем залогиненных пользователей
	if ( is_user_logged_in() ) {
		return $title;
	}

	// Пропускаем легальных ботов
	$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
	$is_legal_bot = preg_match('/(Googlebot|Slurp|MSNBot|Teoma|ia_archiver|StackRambler|WebAlta|Aport|Mail\.Ru|Yandex|bingbot|Rambler|LinkpadBot|WebArtexBot|Web-Monitoring|YaDirectFetcher|Yahoo|YandexDirect|YandexImages|YandexMetrika|YandexWebmaster|OdklBot|YandexVideo|YandexMedia|mail|msnbot)/i', $user_agent);
	if ( $is_legal_bot ) {
		return $title;
	}

	// Работаем только на архивных страницах, главной и страницах пагинации
	if ( ! ( is_archive() || is_home() || is_front_page() || is_paged() ) ) {
		return $title;
	}

	// Работаем только с записями
	if ( $id && 'post' !== get_post_type( $id ) ) {
		return $title;
	}

	$blocked_keywords = get_blocked_keywords();
	$original_title = $title;

	foreach ( $blocked_keywords as $keyword ) {
		// Используем регулярное выражение для поиска слова с любыми символами между буквами
		$pattern = '/\b' . preg_quote( $keyword, '/' ) . '\b/iu';
		$title = preg_replace_callback( $pattern, function( $matches ) {
			return str_repeat( '*', mb_strlen( $matches[0] ) );
		}, $title );
	}

	return $title;
}
add_filter( 'the_title', 'replace_keywords_in_titles', 10, 2 );
