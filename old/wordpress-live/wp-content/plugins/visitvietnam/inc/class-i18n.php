<?php

class vvI18n {

	const TEXT_DOMAIN       = 'visitvietnam';
	const META_ADMIN_LOCALE = 'vv_admin_locale';
	const COOKIE_LOCALE     = 'vv_locale';
	const META_LOCALE_AUTO  = 'auto';
	const TABLE_APARTMENT   = 'vv_apartment_i18n';
	const OPTION_LOCALE_REGISTRY = 'vv_i18n_locales';

	/** @var string Active translation slug for the current request. */
	private static $active_slug = 'en';

	public function __construct() {
		add_action( 'init', [ $this, 'maybe_create_tables' ], 0 );
		add_action( 'init', [ $this, 'handle_locale_request' ], 1 );
		add_action( 'init', [ $this, 'bootstrap_locale' ], 2 );
		add_filter( 'gettext', [ $this, 'filter_gettext_map' ], 10, 3 );
	}

	public function maybe_create_tables() {
		global $wpdb;

		$table   = self::TABLE_APARTMENT;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			apartment_id bigint(20) unsigned NOT NULL,
			locale varchar(10) NOT NULL DEFAULT 'vi',
			name varchar(255) DEFAULT NULL,
			display_name varchar(512) DEFAULT NULL,
			description longtext DEFAULT NULL,
			about_this_short longtext DEFAULT NULL,
			about_this longtext DEFAULT NULL,
			features_description longtext DEFAULT NULL,
			house_rules longtext DEFAULT NULL,
			property_safety longtext DEFAULT NULL,
			datemodified datetime NOT NULL,
			PRIMARY KEY (ID),
			UNIQUE KEY apartment_locale (apartment_id, locale),
			KEY apartment_id (apartment_id)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		$this->maybe_add_email_template_locale_column();
	}

	private function maybe_add_email_template_locale_column() {
		global $wpdb;

		$columns = $wpdb->get_col( 'DESCRIBE vv_email_templates', 0 );
		if ( ! is_array( $columns ) || in_array( 'locale', $columns, true ) ) {
			return;
		}

		$wpdb->query( "ALTER TABLE vv_email_templates ADD COLUMN locale varchar(10) NOT NULL DEFAULT 'en' AFTER code" );
	}

	public function handle_locale_request() {
		if ( is_admin() || strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/vv-admin' ) !== false ) {
			return;
		}

		$lang = GET_Request( 'vv_lang' );
		if ( $lang === '' ) {
			return;
		}

		$locale = self::normalize_locale( $lang );
		if ( ! self::is_supported( $locale ) ) {
			return;
		}

		self::set_frontend_locale_cookie( $locale );

		if ( function_exists( 'pll_current_language' ) && function_exists( 'pll_home_url' ) ) {
			$slug = self::locale_to_slug( $locale );
			$url  = pll_home_url( $slug );
			if ( $url !== '' ) {
				wp_safe_redirect( $url );
				die();
			}
		}

		$redirect = remove_query_arg( 'vv_lang' );
		wp_safe_redirect( $redirect ? $redirect : home_url( '/' ) );
		die();
	}

	public function bootstrap_locale() {
		self::load_textdomain();

		if ( strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/vv-admin' ) !== false ) {
			$user_id = get_current_user_id();
			if ( $user_id > 0 ) {
				self::switch_locale( self::get_user_admin_locale( $user_id ) );
			}
			return;
		}

		self::switch_locale( self::current_locale() );
	}

	public static function default_locale_registry() {
		return [
			'no' => [
				'locale' => 'nb_NO',
				'label'  => 'Norwegian',
				'short'  => 'NO',
			],
			'en' => [
				'locale' => 'en_US',
				'label'  => 'English',
				'short'  => 'EN',
			],
			'vi' => [
				'locale' => 'vi',
				'label'  => 'Vietnamese',
				'short'  => 'VI',
			],
			'tl' => [
				'locale' => 'tl',
				'label'  => 'Tagalog',
				'short'  => 'TL',
			],
		];
	}

	public static function sanitize_locale_slug( $slug ) {
		$slug = strtolower( trim( (string) $slug ) );
		$slug = preg_replace( '/[^a-z0-9_]/', '', $slug );
		if ( strlen( $slug ) < 2 || strlen( $slug ) > 10 || $slug === 'auto' ) {
			return '';
		}
		return $slug;
	}

	public static function sanitize_locale_registry( array $registry ) {
		$clean = [];
		foreach ( $registry as $slug => $info ) {
			$slug = self::sanitize_locale_slug( $slug );
			if ( $slug === '' || ! is_array( $info ) ) {
				continue;
			}

			$label = sanitize_text_field( gArrayItem( $info, 'label' ) );
			if ( $label === '' ) {
				continue;
			}

			$short = strtoupper( sanitize_text_field( gArrayItem( $info, 'short' ) ) );
			if ( $short === '' ) {
				$short = strtoupper( substr( $slug, 0, 3 ) );
			}

			$wp_locale = sanitize_text_field( gArrayItem( $info, 'locale' ) );
			if ( $wp_locale === '' ) {
				$wp_locale = $slug;
			}

			$clean[ $slug ] = [
				'locale' => $wp_locale,
				'label'  => $label,
				'short'  => substr( $short, 0, 5 ),
			];
		}

		if ( ! isset( $clean[ self::default_locale() ] ) ) {
			$defaults = self::default_locale_registry();
			$clean    = [ self::default_locale() => $defaults[ self::default_locale() ] ] + $clean;
		}

		return $clean;
	}

	public static function ensure_locale_registry() {
		$stored = get_option( self::OPTION_LOCALE_REGISTRY, null );
		if ( ! is_array( $stored ) || empty( $stored ) ) {
			$stored = self::default_locale_registry();
			update_option( self::OPTION_LOCALE_REGISTRY, $stored, false );
		}

		return self::sanitize_locale_registry( $stored );
	}

	public static function save_locale_registry( array $registry ) {
		$registry = self::sanitize_locale_registry( $registry );
		update_option( self::OPTION_LOCALE_REGISTRY, $registry, false );
		return $registry;
	}

	public static function get_locale_info( $slug ) {
		$slug     = self::sanitize_locale_slug( $slug );
		$registry = self::ensure_locale_registry();
		return isset( $registry[ $slug ] ) ? $registry[ $slug ] : null;
	}

	public static function is_protected_locale( $slug ) {
		return self::sanitize_locale_slug( $slug ) === self::default_locale();
	}

	public static function create_empty_language_file( $slug ) {
		$slug = self::sanitize_locale_slug( $slug );
		if ( $slug === '' || $slug === self::default_locale() || ! self::is_supported( $slug ) ) {
			return new WP_Error( 'invalid_locale', 'Invalid language code.' );
		}

		if ( self::language_file_exists( $slug ) ) {
			return true;
		}

		$dir = self::get_languages_dir();
		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return new WP_Error( 'mkdir_failed', 'Could not create languages directory.' );
		}

		$path    = self::get_language_file_path( $slug );
		$content = "<?php\n/**\n * {$slug} translations for visitvietnam.\n * Keys are English source strings.\n */\nreturn [];\n";
		if ( file_put_contents( $path, $content ) === false ) {
			return new WP_Error( 'write_failed', 'Could not create language file.' );
		}

		return true;
	}

	public static function add_language( $slug, $label, $short = '', $wp_locale = '' ) {
		if ( ! self::can_manage_language_files() ) {
			return new WP_Error( 'forbidden', 'You do not have permission to manage languages.' );
		}

		$slug = self::sanitize_locale_slug( $slug );
		if ( $slug === '' ) {
			return new WP_Error( 'invalid_slug', 'Language code must be 2–10 lowercase letters or numbers.' );
		}
		if ( $slug === self::default_locale() ) {
			return new WP_Error( 'invalid_slug', 'English is already the default language.' );
		}

		$registry = self::ensure_locale_registry();
		if ( isset( $registry[ $slug ] ) ) {
			return new WP_Error( 'exists', 'A language with this code already exists.' );
		}

		$label = sanitize_text_field( $label );
		if ( $label === '' ) {
			return new WP_Error( 'invalid_label', 'Language name is required.' );
		}

		$short = $short !== '' ? strtoupper( substr( sanitize_text_field( $short ), 0, 5 ) ) : strtoupper( substr( $slug, 0, 3 ) );
		$wp_locale = $wp_locale !== '' ? sanitize_text_field( $wp_locale ) : $slug;

		$registry[ $slug ] = [
			'locale' => $wp_locale,
			'label'  => $label,
			'short'  => $short,
		];
		self::save_locale_registry( $registry );

		$file_result = self::create_empty_language_file( $slug );
		if ( is_wp_error( $file_result ) ) {
			unset( $registry[ $slug ] );
			self::save_locale_registry( $registry );
			return $file_result;
		}

		return $slug;
	}

	public static function update_language( $slug, $label, $short = '', $wp_locale = '' ) {
		if ( ! self::can_manage_language_files() ) {
			return new WP_Error( 'forbidden', 'You do not have permission to manage languages.' );
		}

		$slug = self::sanitize_locale_slug( $slug );
		$registry = self::ensure_locale_registry();
		if ( ! isset( $registry[ $slug ] ) ) {
			return new WP_Error( 'not_found', 'Language not found.' );
		}

		$label = sanitize_text_field( $label );
		if ( $label === '' ) {
			return new WP_Error( 'invalid_label', 'Language name is required.' );
		}

		$registry[ $slug ]['label'] = $label;
		if ( $short !== '' ) {
			$registry[ $slug ]['short'] = strtoupper( substr( sanitize_text_field( $short ), 0, 5 ) );
		}
		if ( $wp_locale !== '' ) {
			$registry[ $slug ]['locale'] = sanitize_text_field( $wp_locale );
		}

		self::save_locale_registry( $registry );
		return true;
	}

	public static function delete_language( $slug ) {
		if ( ! self::can_manage_language_files() ) {
			return new WP_Error( 'forbidden', 'You do not have permission to manage languages.' );
		}

		$slug = self::sanitize_locale_slug( $slug );
		if ( $slug === '' || self::is_protected_locale( $slug ) ) {
			return new WP_Error( 'protected', 'The default English language cannot be deleted.' );
		}

		$registry = self::ensure_locale_registry();
		if ( ! isset( $registry[ $slug ] ) ) {
			return new WP_Error( 'not_found', 'Language not found.' );
		}

		unset( $registry[ $slug ] );
		self::save_locale_registry( $registry );

		$path = self::get_language_file_path( $slug );
		if ( $path !== '' && file_exists( $path ) ) {
			@unlink( $path );
		}
		foreach ( glob( self::get_languages_dir() . '/' . $slug . '-admin*.php' ) as $admin_file ) {
			@unlink( $admin_file );
		}

		self::clear_translation_cache( $slug );
		return true;
	}

	public static function supported_locales() {
		return self::ensure_locale_registry();
	}

	public static function default_locale() {
		return 'en';
	}

	public static function default_locale_full() {
		return 'en_US';
	}

	public static function is_supported( $locale ) {
		$slug = self::locale_to_slug( $locale );
		return isset( self::supported_locales()[ $slug ] );
	}

	public static function normalize_locale( $locale ) {
		$locale = strtolower( trim( (string) $locale ) );
		if ( $locale === '' ) {
			return self::default_locale();
		}
		if ( $locale === 'en_us' || $locale === 'en-us' || $locale === 'english' ) {
			return 'en';
		}
		if ( in_array( $locale, [ 'no', 'nb', 'nb_no', 'nn', 'nn_no', 'norwegian', 'norsk', 'bokmal', 'bokmål', 'nynorsk' ], true ) || strpos( $locale, 'nb' ) === 0 || strpos( $locale, 'nn' ) === 0 ) {
			return 'no';
		}
		if ( $locale === 'vietnamese' || strpos( $locale, 'vi' ) === 0 ) {
			return 'vi';
		}
		if ( in_array( $locale, [ 'tl', 'fil', 'fil_ph', 'tagalog', 'filipino' ], true ) ) {
			return 'tl';
		}
		return $locale;
	}

	public static function locale_to_slug( $locale ) {
		$locale = self::normalize_locale( $locale );
		if ( $locale === 'en_us' ) {
			return 'en';
		}
		return $locale;
	}

	public static function locale_to_wp_locale( $locale ) {
		$slug = self::locale_to_slug( $locale );
		$map  = self::supported_locales();
		if ( isset( $map[ $slug ]['locale'] ) ) {
			return $map[ $slug ]['locale'];
		}
		return self::default_locale_full();
	}

	public static function locale_label( $locale ) {
		$slug = self::locale_to_slug( $locale );
		$map  = self::supported_locales();
		return isset( $map[ $slug ]['label'] ) ? $map[ $slug ]['label'] : strtoupper( $slug );
	}

	public static function locale_short( $locale ) {
		$slug = self::locale_to_slug( $locale );
		$map  = self::supported_locales();
		return isset( $map[ $slug ]['short'] ) ? $map[ $slug ]['short'] : strtoupper( $slug );
	}

	public static function get_client_ip() {
		foreach ( [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ] as $key ) {
			if ( empty( $_SERVER[ $key ] ) ) {
				continue;
			}
			$parts = explode( ',', (string) wp_unslash( $_SERVER[ $key ] ) );
			$ip    = trim( $parts[0] );
			if ( $ip !== '' ) {
				return $ip;
			}
		}
		return '';
	}

	public static function detect_locale_from_ip() {
		static $cached = null;
		if ( $cached !== null ) {
			return $cached;
		}

		if ( ! empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
			$country = strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) );
			if ( $country === 'VN' ) {
				$cached = 'vi';
				return $cached;
			}
			if ( $country === 'PH' ) {
				$cached = 'tl';
				return $cached;
			}
			if ( $country === 'NO' ) {
				$cached = 'no';
				return $cached;
			}
			$cached = self::default_locale();
			return $cached;
		}

		$accept = gArrayItem( $_SERVER, 'HTTP_ACCEPT_LANGUAGE' );
		if ( $accept !== '' ) {
			$accept_lower = strtolower( $accept );
			if ( preg_match( '/\bnb\b|\bno\b|\bnorsk\b|\bnorwegian\b|\bbokm[aå]l\b|\bnynorsk\b/', $accept_lower ) ) {
				$cached = 'no';
				return $cached;
			}
			if ( strpos( $accept_lower, 'vi' ) === 0 || preg_match( '/[,;]\s*vi\b/', $accept_lower ) ) {
				$cached = 'vi';
				return $cached;
			}
			if ( preg_match( '/\btl\b|\bfil\b|\btagalog\b|\bfilipino\b/', $accept_lower ) ) {
				$cached = 'tl';
				return $cached;
			}
		}

		$ip = self::get_client_ip();
		if ( $ip !== '' && ! in_array( $ip, [ '127.0.0.1', '::1' ], true ) ) {
			$transient_key = 'vv_geo_' . md5( $ip );
			$country       = get_transient( $transient_key );
			if ( $country === false ) {
				$country  = '';
				$response = wp_remote_get(
					'http://ip-api.com/json/' . rawurlencode( $ip ) . '?fields=countryCode',
					[ 'timeout' => 2 ]
				);
				if ( ! is_wp_error( $response ) ) {
					$body = json_decode( wp_remote_retrieve_body( $response ), true );
					if ( is_array( $body ) ) {
						$country = sanitize_text_field( gArrayItem( $body, 'countryCode' ) );
					}
				}
				set_transient( $transient_key, $country, DAY_IN_SECONDS );
			}
			if ( $country === 'VN' ) {
				$cached = 'vi';
				return $cached;
			}
			if ( $country === 'PH' ) {
				$cached = 'tl';
				return $cached;
			}
			if ( $country === 'NO' ) {
				$cached = 'no';
				return $cached;
			}
		}

		$cached = self::default_locale();
		return $cached;
	}

	public static function get_active_slug() {
		return self::$active_slug;
	}

	public static function get_languages_dir() {
		return dirname( __DIR__ ) . '/languages';
	}

	public static function get_language_file_path( $locale ) {
		$slug = self::locale_to_slug( $locale );
		if ( $slug === self::default_locale() ) {
			return '';
		}
		return self::get_languages_dir() . '/' . $slug . '.php';
	}

	public static function language_file_exists( $locale ) {
		$path = self::get_language_file_path( $locale );
		return $path !== '' && file_exists( $path );
	}

	public static function clear_translation_cache( $locale = null ) {
		self::get_translation_map( $locale, true );
	}

	public static function get_translation_map( $locale = null, $force_reload = false ) {
		static $cache = [];

		if ( $locale === null ) {
			$locale = self::$active_slug !== '' ? self::$active_slug : self::locale_to_slug( determine_locale() );
		}
		$locale = self::locale_to_slug( $locale );

		if ( $force_reload ) {
			unset( $cache[ $locale ] );
		}

		if ( isset( $cache[ $locale ] ) ) {
			return $cache[ $locale ];
		}

		$file = self::get_language_file_path( $locale );
		if ( $file === '' || ! file_exists( $file ) ) {
			$cache[ $locale ] = [];
		} else {
			$map = include $file;
			$cache[ $locale ] = is_array( $map ) ? $map : [];
		}

		foreach ( glob( self::get_languages_dir() . '/' . $locale . '-admin*.php' ) as $admin_file ) {
			$admin_map = include $admin_file;
			if ( is_array( $admin_map ) ) {
				$cache[ $locale ] = array_merge( $cache[ $locale ], $admin_map );
			}
		}

		return $cache[ $locale ];
	}

	public function filter_gettext_map( $translated, $text, $domain ) {
		if ( $domain !== self::TEXT_DOMAIN ) {
			return $translated;
		}

		$slug = self::locale_to_slug( self::$active_slug );
		if ( $slug === self::default_locale() ) {
			return $translated;
		}

		$map = self::get_translation_map( $slug );
		if ( isset( $map[ $text ] ) && $map[ $text ] !== '' ) {
			return $map[ $text ];
		}

		return $translated;
	}

	public static function get_uploadable_locales() {
		$locales = self::supported_locales();
		unset( $locales[ self::default_locale() ] );
		return $locales;
	}

	public static function parse_translation_payload( $raw ) {
		$raw = trim( (string) $raw );
		if ( $raw === '' ) {
			return new WP_Error( 'empty_payload', 'Translation file is empty.' );
		}

		if ( $raw[0] === '{' || $raw[0] === '[' ) {
			$data = json_decode( $raw, true );
			if ( ! is_array( $data ) ) {
				return new WP_Error( 'invalid_json', 'Invalid JSON translation file.' );
			}
			return self::normalize_translation_map( $data );
		}

		$tmp = self::get_languages_dir() . '/.vv-upload-' . wp_generate_password( 12, false ) . '.php';
		if ( file_put_contents( $tmp, $raw ) === false ) {
			return new WP_Error( 'write_failed', 'Could not process uploaded file.' );
		}

		$data = include $tmp;
		@unlink( $tmp );

		if ( ! is_array( $data ) ) {
			return new WP_Error( 'invalid_php', 'PHP language file must return an array of translations.' );
		}

		return self::normalize_translation_map( $data );
	}

	public static function normalize_translation_map( array $data ) {
		$map = [];
		foreach ( $data as $source => $translation ) {
			$source = (string) $source;
			if ( $source === '' ) {
				continue;
			}
			$map[ $source ] = (string) $translation;
		}
		return $map;
	}

	public static function write_language_file( $locale, array $map ) {
		$slug = self::locale_to_slug( $locale );
		if ( $slug === self::default_locale() || ! self::is_supported( $slug ) ) {
			return new WP_Error( 'invalid_locale', 'Invalid language code.' );
		}

		$dir = self::get_languages_dir();
		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return new WP_Error( 'mkdir_failed', 'Could not create languages directory.' );
		}

		$path    = self::get_language_file_path( $slug );
		$export  = var_export( self::normalize_translation_map( $map ), true );
		$content = "<?php\n/**\n * {$slug} translations for visitvietnam.\n * Keys are English source strings.\n */\nreturn {$export};\n";

		if ( file_put_contents( $path, $content ) === false ) {
			return new WP_Error( 'write_failed', 'Could not save language file.' );
		}

		self::get_translation_map( $slug, true );
		return true;
	}

	public static function can_manage_language_files() {
		global $logged_user_role;
		return $logged_user_role === 'administrator' || current_user_can( 'manage_options' );
	}

	public static function handle_admin_post( $action = '' ) {
		if ( strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/vv-admin' ) === false ) {
			return;
		}

		if ( $action === 'upload_language_file' ) {
			self::upload_language_file();
		} elseif ( $action === 'save_language_locale' ) {
			self::save_language_locale();
		} elseif ( $action === 'add_language' ) {
			self::add_language_request();
		} elseif ( $action === 'delete_language' ) {
			self::delete_language_request();
		}
	}

	public static function handle_admin_get( $action = '' ) {
		if ( strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/vv-admin' ) === false ) {
			return;
		}

		if ( $action === 'download_language_file' ) {
			self::download_language_file();
		} elseif ( $action === 'download_language_template' ) {
			self::download_language_template();
		}
	}

	public static function save_language_locale() {
		if ( ! self::can_manage_language_files() ) {
			setErrorMsg( 'You do not have permission to manage languages.' );
			wp_redirect( vv_admin_url( 'settings/languages' ) );
			die();
		}

		$slug = self::sanitize_locale_slug( POST_Request( 'locale' ) );
		if ( $slug === '' || ! self::is_supported( $slug ) ) {
			setErrorMsg( 'Choose a valid language.' );
			wp_redirect( vv_admin_url( 'settings/languages' ) );
			die();
		}

		$result = self::update_language(
			$slug,
			POST_Request( 'language_label' ),
			POST_Request( 'language_short' ),
			POST_Request( 'language_wp_locale' )
		);

		if ( is_wp_error( $result ) ) {
			setErrorMsg( $result->get_error_message() );
		} else {
			setSuccessMsg( sprintf( 'Language settings saved for %s.', self::locale_label( $slug ) ) );
		}

		wp_redirect( vv_admin_url( 'settings/languages' ) . '?locale=' . rawurlencode( $slug ) );
		die();
	}

	public static function add_language_request() {
		if ( ! self::can_manage_language_files() ) {
			setErrorMsg( 'You do not have permission to manage languages.' );
			wp_redirect( vv_admin_url( 'settings/languages' ) );
			die();
		}

		$result = self::add_language(
			POST_Request( 'language_code' ),
			POST_Request( 'language_label' ),
			POST_Request( 'language_short' ),
			POST_Request( 'language_wp_locale' )
		);

		if ( is_wp_error( $result ) ) {
			setErrorMsg( $result->get_error_message() );
			wp_redirect( vv_admin_url( 'settings/languages' ) );
			die();
		}

		setSuccessMsg( sprintf( 'Language %s added. Upload or paste translations below.', self::locale_label( $result ) ) );
		wp_redirect( vv_admin_url( 'settings/languages' ) . '?locale=' . rawurlencode( $result ) );
		die();
	}

	public static function delete_language_request() {
		if ( ! self::can_manage_language_files() ) {
			setErrorMsg( 'You do not have permission to manage languages.' );
			wp_redirect( vv_admin_url( 'settings/languages' ) );
			die();
		}

		$slug = self::sanitize_locale_slug( POST_Request( 'locale' ) );
		$result = self::delete_language( $slug );

		if ( is_wp_error( $result ) ) {
			setErrorMsg( $result->get_error_message() );
			wp_redirect( vv_admin_url( 'settings/languages' ) . ( $slug !== '' ? '?locale=' . rawurlencode( $slug ) : '' ) );
			die();
		}

		setSuccessMsg( 'Language deleted.' );
		wp_redirect( vv_admin_url( 'settings/languages' ) );
		die();
	}

	public static function upload_language_file() {
		if ( ! self::can_manage_language_files() ) {
			setErrorMsg( 'You do not have permission to upload language files.' );
			wp_redirect( vv_admin_url( 'settings/languages' ) );
			die();
		}

		$locale = self::normalize_locale( POST_Request( 'locale' ) );
		if ( $locale === self::default_locale() || ! self::is_supported( $locale ) ) {
			setErrorMsg( 'Choose a valid language.' );
			wp_redirect( vv_admin_url( 'settings/languages' ) );
			die();
		}

		$raw  = '';
		$file = gArrayItem( $_FILES, 'language_file' );
		if ( is_array( $file ) && intval( gArrayItem( $file, 'error' ) ) === UPLOAD_ERR_OK ) {
			$raw = file_get_contents( gArrayItem( $file, 'tmp_name' ) );
		} elseif ( trim( POST_Request( 'language_json' ) ) !== '' ) {
			$raw = wp_unslash( POST_Request( 'language_json' ) );
		}

		$map = self::parse_translation_payload( $raw );
		if ( is_wp_error( $map ) ) {
			setErrorMsg( $map->get_error_message() );
			wp_redirect( vv_admin_url( 'settings/languages' ) . '?locale=' . rawurlencode( $locale ) );
			die();
		}

		$result = self::write_language_file( $locale, $map );
		if ( is_wp_error( $result ) ) {
			setErrorMsg( $result->get_error_message() );
		} else {
			setSuccessMsg( sprintf( 'Language file updated for %s (%d strings).', self::locale_label( $locale ), count( $map ) ) );
		}

		wp_redirect( vv_admin_url( 'settings/languages' ) . '?locale=' . rawurlencode( $locale ) );
		die();
	}

	public static function download_language_file() {
		if ( ! self::can_manage_language_files() ) {
			wp_die( 'Forbidden' );
		}

		$locale = self::normalize_locale( GET_Request( 'locale' ) );
		if ( ! self::is_supported( $locale ) || $locale === self::default_locale() ) {
			wp_die( 'Invalid language.' );
		}

		$map = self::get_translation_map( $locale );
		$json = wp_json_encode( $map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . self::locale_to_slug( $locale ) . '.json"' );
		echo $json;
		die();
	}

	public static function download_language_template() {
		if ( ! self::can_manage_language_files() ) {
			wp_die( 'Forbidden' );
		}

		$map  = self::get_source_string_template();
		$json = wp_json_encode( $map, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="visitvietnam-translation-template.json"' );
		echo $json;
		die();
	}

	public static function get_source_string_template() {
		$map = self::get_translation_map( 'vi' );
		if ( empty( $map ) ) {
			$map = [
				'Dashboard' => '',
				'Booking' => '',
				'Apartments' => '',
				'Account Settings' => '',
				'Language' => '',
				'Save' => '',
			];
		} else {
			foreach ( $map as $key => $value ) {
				$map[ $key ] = '';
			}
		}
		return $map;
	}

	public static function get_language_file_info( $locale ) {
		$slug = self::locale_to_slug( $locale );
		$path = self::get_language_file_path( $slug );
		$info = [
			'slug'       => $slug,
			'label'      => self::locale_label( $slug ),
			'exists'     => false,
			'path'       => $path,
			'strings'    => 0,
			'modified'   => '',
		];

		if ( $path !== '' && file_exists( $path ) ) {
			$map = self::get_translation_map( $slug );
			$info['exists']   = true;
			$info['strings']  = count( $map );
			$info['modified'] = date( 'Y-m-d H:i', filemtime( $path ) );
		}

		return $info;
	}

	public static function can_edit_user_locale( $user_id = 0 ) {
		global $logged_user_role;

		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return false;
		}
		if ( get_current_user_id() === $user_id ) {
			return true;
		}
		return $logged_user_role === 'administrator';
	}

	public static function save_user_locale_preference( $user_id, $locale ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 || ! self::can_edit_user_locale( $user_id ) ) {
			return false;
		}

		$locale = strtolower( trim( (string) $locale ) );
		if ( $locale === '' || $locale === self::META_LOCALE_AUTO ) {
			delete_user_meta( $user_id, self::META_ADMIN_LOCALE );
			return true;
		}

		return self::set_user_admin_locale( $user_id, self::normalize_locale( $locale ) );
	}

	public static function get_user_locale_preference( $user_id = 0 ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return self::META_LOCALE_AUTO;
		}

		$stored = get_user_meta( $user_id, self::META_ADMIN_LOCALE, true );
		if ( $stored === '' || $stored === self::META_LOCALE_AUTO ) {
			return self::META_LOCALE_AUTO;
		}

		$stored = self::normalize_locale( $stored );
		return self::is_supported( $stored ) ? $stored : self::META_LOCALE_AUTO;
	}

	public static function resolve_locale( $locale ) {
		$locale = strtolower( trim( (string) $locale ) );
		if ( $locale === '' || $locale === self::META_LOCALE_AUTO ) {
			return self::detect_locale_from_ip();
		}
		$locale = self::normalize_locale( $locale );
		return self::is_supported( $locale ) ? $locale : self::detect_locale_from_ip();
	}

	public static function current_locale() {
		if ( function_exists( 'pll_current_language' ) ) {
			$slug = pll_current_language( 'slug' );
			if ( $slug !== '' && self::is_supported( $slug ) ) {
				return self::normalize_locale( $slug );
			}
		}

		if ( isset( $_COOKIE[ self::COOKIE_LOCALE ] ) ) {
			$cookie = self::normalize_locale( wp_unslash( $_COOKIE[ self::COOKIE_LOCALE ] ) );
			if ( self::is_supported( $cookie ) ) {
				return $cookie;
			}
		}

		if ( is_user_logged_in() ) {
			return self::get_user_admin_locale( get_current_user_id() );
		}

		return self::detect_locale_from_ip();
	}

	public static function get_user_admin_locale( $user_id = 0 ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			$user_id = get_current_user_id();
		}
		if ( $user_id <= 0 ) {
			return self::detect_locale_from_ip();
		}

		return self::resolve_locale( get_user_meta( $user_id, self::META_ADMIN_LOCALE, true ) );
	}

	public static function set_user_admin_locale( $user_id, $locale ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return false;
		}
		$locale = self::normalize_locale( $locale );
		if ( ! self::is_supported( $locale ) ) {
			$locale = self::default_locale();
		}
		update_user_meta( $user_id, self::META_ADMIN_LOCALE, $locale );
		return true;
	}

	public static function switch_locale( $locale ) {
		self::$active_slug = self::locale_to_slug( $locale );
		$wp_locale         = self::locale_to_wp_locale( self::$active_slug );
		switch_to_locale( $wp_locale );
		self::load_textdomain();
	}

	public static function load_textdomain() {
		load_plugin_textdomain(
			self::TEXT_DOMAIN,
			false,
			basename( dirname( __DIR__ ) ) . '/languages'
		);
	}

	public static function set_frontend_locale_cookie( $locale ) {
		$locale = self::normalize_locale( $locale );
		if ( ! self::is_supported( $locale ) ) {
			return;
		}
		$path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
		setcookie( self::COOKIE_LOCALE, $locale, time() + YEAR_IN_SECONDS, $path, $domain, is_ssl(), true );
		$_COOKIE[ self::COOKIE_LOCALE ] = $locale;
	}

	public static function translation_locales() {
		$locales = self::supported_locales();
		unset( $locales[ self::default_locale() ] );
		return $locales;
	}

	public static function apartment_i18n_fields() {
		return [
			'name',
			'display_name',
			'description',
			'about_this_short',
			'about_this',
			'features_description',
			'house_rules',
			'property_safety',
		];
	}

	public static function get_apartment_i18n( $apartment_id, $locale ) {
		global $wpdb;

		$apartment_id = intval( $apartment_id );
		$locale       = self::normalize_locale( $locale );
		if ( $apartment_id <= 0 || $locale === self::default_locale() ) {
			return [];
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE_APARTMENT . ' WHERE apartment_id = %d AND locale = %s',
				$apartment_id,
				$locale
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : [];
	}

	public static function save_apartment_i18n( $apartment_id, $locale, array $fields ) {
		global $wpdb;

		$apartment_id = intval( $apartment_id );
		$locale       = self::normalize_locale( $locale );
		if ( $apartment_id <= 0 || $locale === self::default_locale() ) {
			return;
		}

		$data = [ 'datemodified' => current_time( 'mysql' ) ];
		foreach ( self::apartment_i18n_fields() as $field ) {
			if ( array_key_exists( $field, $fields ) ) {
				$data[ $field ] = $fields[ $field ];
			}
		}

		$existing = self::get_apartment_i18n( $apartment_id, $locale );
		if ( ! empty( $existing['ID'] ) ) {
			$wpdb->update( self::TABLE_APARTMENT, $data, [ 'ID' => intval( $existing['ID'] ) ] );
		} else {
			$data['apartment_id'] = $apartment_id;
			$data['locale']       = $locale;
			$wpdb->insert( self::TABLE_APARTMENT, $data );
		}
	}

	public static function merge_apartment_localized( array $apartment, $locale = null ) {
		if ( ! is_array( $apartment ) || intval( gArrayItem( $apartment, 'ID' ) ) <= 0 ) {
			return $apartment;
		}

		if ( $locale === null ) {
			$locale = self::current_locale();
		}
		$locale = self::normalize_locale( $locale );
		if ( $locale === self::default_locale() ) {
			return $apartment;
		}

		$i18n = self::get_apartment_i18n( intval( $apartment['ID'] ), $locale );
		if ( empty( $i18n ) ) {
			return $apartment;
		}

		foreach ( self::apartment_i18n_fields() as $field ) {
			$value = gArrayItem( $i18n, $field );
			if ( trim( (string) $value ) !== '' ) {
				$apartment[ $field ] = stripslashes( $value );
			}
		}

		return $apartment;
	}

	public static function language_switcher_url( $locale ) {
		$locale = self::normalize_locale( $locale );
		$slug   = self::locale_to_slug( $locale );

		if ( function_exists( 'pll_home_url' ) ) {
			return pll_home_url( $slug );
		}

		return add_query_arg( 'vv_lang', $slug, home_url( add_query_arg( [] ) ) );
	}

	public static function render_language_switcher( $args = [] ) {
		$current = self::current_locale();
		$current = self::locale_to_slug( $current );
		$html    = '<div class="lang vv-lang-switcher">';

		foreach ( self::supported_locales() as $slug => $info ) {
			$active = ( $slug === $current ) ? ' active' : '';
			$url    = esc_url( self::language_switcher_url( $slug ) );
			$label  = esc_html( gArrayItem( $info, 'short', strtoupper( $slug ) ) );
			$html  .= '<a href="' . $url . '" class="lang_btn' . $active . '" hreflang="' . esc_attr( $slug ) . '">' . $label . '</a>';
		}

		$html .= '</div>';
		return $html;
	}
}

new vvI18n();

function vv__( $text ) {
	return __( $text, vvI18n::TEXT_DOMAIN );
}

function vv_e( $text ) {
	echo esc_html( vv__( $text ) );
}

function vv_get_apartment_localized( $id = 0, $locale = null ) {
	$apartment = vv_get_apartment( $id );
	return vvI18n::merge_apartment_localized( is_array( $apartment ) ? $apartment : [], $locale );
}

function vv_language_switcher() {
	echo vvI18n::render_language_switcher();
}
