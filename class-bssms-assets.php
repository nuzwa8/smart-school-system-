<?php
/**
 * BSSMS_Assets کلاس
 * ایڈمن کے صفحات پر (CSS) اور (JavaScript) اثاثوں کو ان کی ضرورت کے مطابق لوڈ کرنے کا انتظام۔
 */
class BSSMS_Assets {

	/**
	 * تمام ایڈمن اثاثوں کو Enqueue کریں۔
	 *
	 * @param string $hook موجودہ ایڈمن صفحہ ہک۔
	 */
	public static function enqueue_admin_assets( $hook ) {

		// یہ ہک صرف ہمارے پلگ اِن کے صفحات کے لیے ہونا چاہیے۔
		if ( strpos( $hook, 'bssms-' ) === false && strpos( $hook, 'bssms_page_' ) === false ) {
			return;
		}

		// 1. (CSS) اثاثے
		wp_enqueue_style(
			'bssms-common-styles',
			BSSMS_URL . 'bssms-common.css', // قاعدہ 31: مشترکہ CSS
			array(),
			BSSMS_VERSION
		);

		// 2. (JavaScript) اثاثے
		wp_enqueue_script(
			'bssms-common-scripts',
			BSSMS_URL . 'bssms-common.js', // قاعدہ 31: مشترکہ JS
			array( 'jquery' ),
			BSSMS_VERSION,
			true // Footer میں لوڈ کریں
		);

		// 3. (Localized Data) شامل کریں (قاعدہ 4)
		self::localize_data();
	}

	/**
	 * (JavaScript) میں استعمال کے لیے ضروری ڈیٹا کو لوکلائز کریں۔
	 */
	private static function localize_data() {
		$nonce_data = array();
		
		// قاعدہ 15: تمام Slugs/Nonces کو ایک جگہ سے ریکارڈ کریں۔
		$pages = array(
			'admission' => 'bssms-admission',
			'students-list' => 'bssms-students-list',
			'courses-setup' => 'bssms-courses-setup',
			'settings' => 'bssms-settings',
		);
		
		// قاعدہ 12: Page-Link Validation (PHP ↔ JS)
		$ajax_actions = array(
			'save_admission' => 'bssms_save_admission',
			'fetch_students' => 'bssms_fetch_students',
			'save_settings' => 'bssms_save_settings',
			'fetch_courses' => 'bssms_fetch_courses',
		);
		
		// تمام Nonces کو محفوظ طریقے سے (JavaScript) میں بھیجیں (قاعدہ 4: localized data)
		foreach ( $ajax_actions as $key => $action ) {
			$nonce_data[ $key . '_nonce' ] = wp_create_nonce( $action );
		}
		
		// ضروری ڈیٹا لوکلائز کریں۔
		wp_localize_script(
			'bssms-common-scripts',
			'bssms_data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonces'   => $nonce_data,
				'pages'    => $pages,
				'actions'  => $ajax_actions,
				'current_user_id' => get_current_user_id(),
				'user_can_manage' => current_user_can( 'bssms_manage_admissions' ),
				'theme_mode' => BSSMS_DB::get_setting( 'theme_mode', 'light' ),
				'language_mode' => BSSMS_DB::get_setting( 'language', 'ur_en' ),
			)
		);
	}
}

// ✅ Syntax verified block end
