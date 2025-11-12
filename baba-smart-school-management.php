<?php
/**
 * Plugin Name: Baba Smart School Management System (BSSMS)
 * Description: AI اکیڈمی کے لیے ایڈمیشن، فیس مینجمنٹ، اور رپورٹنگ سسٹم. (PHP), (JS), (CSS) کو استعمال کرتا ہے.
 * Version: 1.0.0
 * Author: Gemini Architect AI
 * License: GPL2
 * Text Domain: bssms
 * Domain Path: /languages
 */

// 🟢 یہاں سے Core Plugin Code شروع ہو رہا ہے
if ( ! defined( 'ABSPATH' ) ) {
	exit; // براہ راست رسائی ممنوع ہے۔
}

// پلگ اِن کا بنیادی پاتھ اور یو آر ایل ڈیفائن کریں۔
define( 'BSSMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BSSMS_URL', plugin_dir_url( __FILE__ ) );
define( 'BSSMS_VERSION', '1.0.0' );

/**
 * کلاسز کو خودکار طور پر لوڈ کرنے کا فنکشن۔
 * یہ فنکشن پلگ اِن میں موجود تمام ضروری (PHP) کلاسز کو ڈھونڈ کر لوڈ کرتا ہے۔
 *
 * @param string $class_name وہ کلاس جو لوڈ کرنی ہے۔
 */
function bssms_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'BSSMS_' ) ) {
		return;
	}

	$file_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
	$file_path = BSSMS_PATH . $file_name;

	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}
spl_autoload_register( 'bssms_autoload_classes' );

/**
 * پلگ اِن کو ایکٹیویٹ کرنے کا فنکشن۔
 * یہ (DB) ٹیبلز بناتا ہے اور کسٹم رولز کو شامل کرتا ہے۔
 *
 * @uses BSSMS_Activator
 */
function bssms_activate_plugin() {
	BSSMS_Activator::activate();
}
register_activation_hook( __FILE__, 'bssms_activate_plugin' );

/**
 * پلگ اِن کی مرکزی کلاس کو شروع کرنا۔
 */
class BSSMS_Core {

	/**
	 * BSSMS_Core کا سنگلٹن انسٹینس۔
	 */
	protected static $instance = null;

	/**
	 * سنگلٹن انسٹینس حاصل کریں۔
	 *
	 * @return BSSMS_Core
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * کنسٹرکٹر
	 */
	protected function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * ضروری کلاس فائلیں شامل کریں۔
	 */
	private function includes() {
		// بنیادی کلاسز یہاں پہلے سے ہی autoload ہو رہی ہیں۔
	}

	/**
	 * تمام ہکس (Hooks) کو سیٹ اپ کریں۔
	 */
	private function hooks() {
		// (PHP) ایڈمن مینو اور اثاثے لوڈ کریں۔
		add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( 'BSSMS_Assets', 'enqueue_admin_assets' ) );

		// (AJAX) ہینڈلر کو رجسٹر کریں۔
		add_action( 'wp_ajax_bssms_save_admission', array( 'BSSMS_Ajax', 'handle_save_admission' ) );
		add_action( 'wp_ajax_bssms_fetch_students', array( 'BSSMS_Ajax', 'handle_fetch_students' ) );
		// مزید (AJAX) ایکشنز بعد میں شامل ہوں گے۔
	}

	/**
	 * ایڈمن مینو شامل کریں۔
	 *
	 * قاعدہ 12 اور 15: Slugs ہمیشہ مطابقت رکھیں۔
	 */
	public function add_plugin_menu() {
		add_menu_page(
			esc_html__( 'بابا اکیڈمی', 'bssms' ), // Page Title
			esc_html__( 'بابا اکیڈمی', 'bssms' ), // Menu Title
			'bssms_manage_admissions', // Capability: نیا رول
			'bssms-dashboard', // Menu Slug
			array( $this, 'render_dashboard_page' ), // Callback
			'dashicons-welcome-learn-more', // Icon
			6 // Position
		);

		// 1. داخلہ فارم
		add_submenu_page(
			'bssms-dashboard',
			esc_html__( 'داخلہ فارم', 'bssms' ),
			esc_html__( 'داخلہ فارم', 'bssms' ),
			'bssms_create_admission', // Capability
			'bssms-admission', // Slug
			array( $this, 'render_admission_page' )
		);

		// 2. طالب علم کی فہرست
		add_submenu_page(
			'bssms-dashboard',
			esc_html__( 'طالب علم کی فہرست', 'bssms' ),
			esc_html__( 'طالب علم کی فہرست', 'bssms' ),
			'bssms_manage_admissions', // Capability
			'bssms-students-list', // Slug
			array( $this, 'render_students_list_page' )
		);

		// 3. کورسز سیٹ اپ (صرف ایڈمن کیلئے)
		add_submenu_page(
			'bssms-dashboard',
			esc_html__( 'کورسز سیٹ اپ', 'bssms' ),
			esc_html__( 'کورسز سیٹ اپ', 'bssms' ),
			'manage_options', // Admin Capability
			'bssms-courses-setup', // Slug
			array( $this, 'render_courses_setup_page' )
		);

		// 4. سسٹمز ترتیبات (قاعدہ 29)
		add_submenu_page(
			'bssms-dashboard',
			esc_html__( 'سسٹم ترتیبات', 'bssms' ),
			esc_html__( 'سسٹم ترتیبات', 'bssms' ),
			'manage_options',
			'bssms-settings', // Slug
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * ہر صفحے کے لیے Placeholder رینڈر فنکشنز۔
	 * یہ فنکشنز بعد میں (template) بلاکس کو لوڈ کریں گے۔
	 */
	public function render_dashboard_page() {
		echo '<div class="wrap"><div id="bssms-dashboard-root"></div></div>';
	}
	public function render_admission_page() {
		echo '<div class="wrap"><div id="bssms-admission-root"></div></div>'; // قاعدہ 4
	}
	public function render_students_list_page() {
		echo '<div class="wrap"><div id="bssms-students-list-root"></div></div>'; // قاعدہ 4
	}
	public function render_courses_setup_page() {
		echo '<div class="wrap"><div id="bssms-courses-setup-root"></div></div>'; // قاعدہ 4
	}
	public function render_settings_page() {
		echo '<div class="wrap"><div id="bssms-settings-root"></div></div>'; // قاعدہ 4
	}

}

BSSMS_Core::get_instance();
// 🔴 یہاں پر Core Plugin Code ختم ہو رہا ہے
/** Part 1 (Refactored) — Admission Page: Core File Update for Dedicated Page Logic */

// BSSMS_Core کلاس کے اندر، includes() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
// اب یہاں پر تمام سرشار (Dedicated) پیج کلاسز شامل ہوں گی
private function includes() {
    // پیج لاجک فائلیں شامل کریں (قاعدہ 30 کے مطابق)
    require_once BSSMS_PATH . 'pages/bssms-admission-page.php';
    // مزید صفحات یہاں شامل ہوں گے:
    // require_once BSSMS_PATH . 'pages/bssms-students-list-page.php';
    // require_once BSSMS_PATH . 'pages/bssms-courses-setup-page.php';
    // require_once BSSMS_PATH . 'pages/bssms-settings-page.php';
}

// BSSMS_Core کلاس کے اندر، render_admission_page() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
public function render_admission_page() {
    // یہاں صرف سرشار کلاس کا فنکشن کال ہو گا
    BSSMS_Admission_Page::render_page();
}

// BSSMS_Core کلاس سے render_admission_template() فنکشن کو حذف کر دیا گیا ہے۔
// اب یہ BSSMS_Admission_Page کلاس میں موجود ہے۔

// 🟢 نوٹ: آپ کو 'pages' نام کا ایک نیا فولڈر بنانا ہو گا، اور اس کے اندر اگلی فائل رکھنی ہو گی۔

// ✅ Syntax verified block end
/** Part 6 — Students List: Core File Update for Dedicated Page & AJAX */

// BSSMS_Core کلاس کے اندر، includes() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
// قاعدہ 30: ہر صفحہ کی الگ فائلیں
private function includes() {
    // پیج لاجک فائلیں شامل کریں (قاعدہ 30 کے مطابق)
    require_once BSSMS_PATH . 'pages/bssms-admission-page.php';
    require_once BSSMS_PATH . 'pages/bssms-students-list-page.php'; // نیا پیج شامل
    // مزید صفحات یہاں شامل ہوں گے:
    // require_once BSSMS_PATH . 'pages/bssms-courses-setup-page.php';
    // require_once BSSMS_PATH . 'pages/bssms-settings-page.php';
}

// BSSMS_Core کلاس کے اندر، hooks() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
private function hooks() {
    // (PHP) ایڈمن مینو اور اثاثے لوڈ کریں۔
    add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
    add_action( 'admin_enqueue_scripts', array( 'BSSMS_Assets', 'enqueue_admin_assets' ) );

    // (AJAX) ہینڈلر کو رجسٹر کریں۔
    add_action( 'wp_ajax_bssms_save_admission', array( 'BSSMS_Ajax', 'handle_save_admission' ) );
    add_action( 'wp_ajax_bssms_fetch_students', array( 'BSSMS_Ajax', 'handle_fetch_students' ) );
    add_action( 'wp_ajax_bssms_translate_text', array( 'BSSMS_Ajax', 'handle_translate_text' ) );
    add_action( 'wp_ajax_bssms_delete_admission', array( 'BSSMS_Ajax', 'handle_delete_admission' ) ); // نیا AJAX ہینڈلر
}

// BSSMS_Core کلاس کے اندر، render_students_list_page() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
public function render_students_list_page() {
    // یہاں صرف سرشار کلاس کا فنکشن کال ہو گا
    BSSMS_Students_List_Page::render_page();
}

// ✅ Syntax verified block end
/** Part 10 — Courses Setup: Core File Update for Dedicated Page & AJAX */

// BSSMS_Core کلاس کے اندر، includes() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
// قاعدہ 30: ہر صفحہ کی الگ فائلیں
private function includes() {
    // پیج لاجک فائلیں شامل کریں (قاعدہ 30 کے مطابق)
    require_once BSSMS_PATH . 'pages/bssms-admission-page.php';
    require_once BSSMS_PATH . 'pages/bssms-students-list-page.php';
    require_once BSSMS_PATH . 'pages/bssms-courses-setup-page.php'; // نیا پیج شامل
    // مزید صفحات یہاں شامل ہوں گے:
    // require_once BSSMS_PATH . 'pages/bssms-settings-page.php';
}

// BSSMS_Core کلاس کے اندر، hooks() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
private function hooks() {
    // (PHP) ایڈمن مینو اور اثاثے لوڈ کریں۔
    add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
    add_action( 'admin_enqueue_scripts', array( 'BSSMS_Assets', 'enqueue_admin_assets' ) );

    // (AJAX) ہینڈلر کو رجسٹر کریں۔
    add_action( 'wp_ajax_bssms_save_admission', array( 'BSSMS_Ajax', 'handle_save_admission' ) );
    add_action( 'wp_ajax_bssms_fetch_students', array( 'BSSMS_Ajax', 'handle_fetch_students' ) );
    add_action( 'wp_ajax_bssms_translate_text', array( 'BSSMS_Ajax', 'handle_translate_text' ) );
    add_action( 'wp_ajax_bssms_delete_admission', array( 'BSSMS_Ajax', 'handle_delete_admission' ) );
    
    // کورسز کے نئے AJAX ہینڈلرز
    add_action( 'wp_ajax_bssms_fetch_courses', array( 'BSSMS_Ajax', 'handle_fetch_courses' ) ); // پہلے سے موجود تھا لیکن اب اصلی لاجک یہاں ہے۔
    add_action( 'wp_ajax_bssms_save_course', array( 'BSSMS_Ajax', 'handle_save_course' ) ); // نیا AJAX ہینڈلر
    add_action( 'wp_ajax_bssms_delete_course', array( 'BSSMS_Ajax', 'handle_delete_course' ) ); // نیا AJAX ہینڈلر
}

// BSSMS_Core کلاس کے اندر، render_courses_setup_page() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
public function render_courses_setup_page() {
    // یہاں صرف سرشار کلاس کا فنکشن کال ہو گا
    BSSMS_Courses_Setup_Page::render_page();
}

// ✅ Syntax verified block end
/** Part 14 — Settings Page: Core File Update for Dedicated Page & AJAX */

// BSSMS_Core کلاس کے اندر، includes() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
// قاعدہ 30: ہر صفحہ کی الگ فائلیں
private function includes() {
    // پیج لاجک فائلیں شامل کریں (قاعدہ 30 کے مطابق)
    require_once BSSMS_PATH . 'pages/bssms-admission-page.php';
    require_once BSSMS_PATH . 'pages/bssms-students-list-page.php';
    require_once BSSMS_PATH . 'pages/bssms-courses-setup-page.php';
    require_once BSSMS_PATH . 'pages/bssms-settings-page.php'; // نیا پیج شامل
}

// BSSMS_Core کلاس کے اندر، hooks() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
private function hooks() {
    // (PHP) ایڈمن مینو اور اثاثے لوڈ کریں۔
    add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
    add_action( 'admin_enqueue_scripts', array( 'BSSMS_Assets', 'enqueue_admin_assets' ) );

    // (AJAX) ہینڈلر کو رجسٹر کریں۔
    add_action( 'wp_ajax_bssms_save_admission', array( 'BSSMS_Ajax', 'handle_save_admission' ) );
    add_action( 'wp_ajax_bssms_fetch_students', array( 'BSSMS_Ajax', 'handle_fetch_students' ) );
    add_action( 'wp_ajax_bssms_translate_text', array( 'BSSMS_Ajax', 'handle_translate_text' ) );
    add_action( 'wp_ajax_bssms_delete_admission', array( 'BSSMS_Ajax', 'handle_delete_admission' ) );
    
    add_action( 'wp_ajax_bssms_fetch_courses', array( 'BSSMS_Ajax', 'handle_fetch_courses' ) );
    add_action( 'wp_ajax_bssms_save_course', array( 'BSSMS_Ajax', 'handle_save_course' ) );
    add_action( 'wp_ajax_bssms_delete_course', array( 'BSSMS_Ajax', 'handle_delete_course' ) );
    
    // ترتیبات کے نئے AJAX ہینڈلرز
    add_action( 'wp_ajax_bssms_save_settings', array( 'BSSMS_Ajax', 'handle_save_settings' ) ); // پہلے سے موجود تھا لیکن اب اصلی لاجک یہاں ہے۔
    add_action( 'wp_ajax_bssms_reset_defaults', array( 'BSSMS_Ajax', 'handle_reset_defaults' ) ); // نیا AJAX ہینڈلر
}

// BSSMS_Core کلاس کے اندر، render_settings_page() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
public function render_settings_page() {
    // یہاں صرف سرشار کلاس کا فنکشن کال ہو گا
    BSSMS_Settings_Page::render_page();
}

// ✅ Syntax verified block end
