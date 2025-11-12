<?php
/**
 * BSSMS_Activator کلاس
 * یہ کلاس پلگ اِن کی ایکٹیویشن کے دوران ضروری کام سنبھالتی ہے،
 * مثلاً (DB) ٹیبلز بنانا اور (Custom User Roles) شامل کرنا۔
 */
class BSSMS_Activator {

	/**
	 * پلگ اِن کو ایکٹیویٹ کریں۔
	 */
	public static function activate() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;

		// 🟢 یہاں سے DB Tables شروع ہو رہے ہیں
		$charset_collate = $wpdb->get_charset_collate();

		// 1. کورسز کی فکسڈ لسٹ (bssms_courses)
		$table_courses = $wpdb->prefix . 'bssms_courses';
		$sql_courses = "CREATE TABLE $table_courses (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			course_name_en tinytext NOT NULL,
			course_name_ur tinytext NOT NULL,
			course_fee int(10) NOT NULL,
			is_active tinyint(1) DEFAULT 1 NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql_courses ); // قاعدہ 4: dbDelta()

		// 2. طالب علم اور داخلہ کی معلومات (bssms_admissions)
		$table_admissions = $wpdb->prefix . 'bssms_admissions';
		$sql_admissions = "CREATE TABLE $table_admissions (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			full_name_en tinytext NOT NULL,
			full_name_ur tinytext NOT NULL,
			father_name_en tinytext,
			father_name_ur tinytext,
			dob date NOT NULL,
			gender varchar(10) NOT NULL,
			course_id mediumint(9) NOT NULL,
			total_fee int(10) NOT NULL,
			paid_amount int(10) DEFAULT 0 NOT NULL,
			due_amount int(10) DEFAULT 0 NOT NULL,
			payment_screenshot_url varchar(255) DEFAULT '' NOT NULL,
			admission_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id),
			KEY course_id (course_id)
		) $charset_collate;";
		dbDelta( $sql_admissions );

		// 3. پلگ اِن کی ترتیبات (bssms_settings)
		$table_settings = $wpdb->prefix . 'bssms_settings';
		$sql_settings = "CREATE TABLE $table_settings (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			setting_key varchar(50) NOT NULL,
			setting_value longtext,
			PRIMARY KEY (id),
			UNIQUE KEY setting_key (setting_key)
		) $charset_collate;";
		dbDelta( $sql_settings );
		// 🔴 یہاں پر DB Tables ختم ہو رہے ہیں

		// 🟢 یہاں سے Custom User Roles شروع ہو رہے ہیں
		self::add_custom_roles_and_caps();
		// 🔴 یہاں پر Custom User Roles ختم ہو رہے ہیں

		// 🟢 یہاں سے Default Data شروع ہو رہا ہے
		self::insert_default_data(); // قاعدہ 26: Demo Data خودکار شامل
		// 🔴 یہاں پر Default Data ختم ہو رہا ہے

		update_option( 'bssms_version', BSSMS_VERSION ); // قاعدہ 4: version on activation
	}

	/**
	 * کسٹم یوزر رولز اور قابلیتیں شامل کریں۔
	 */
	private static function add_custom_roles_and_caps() {
		// 1. BSSMS-Manager Role: مکمل ایڈمیشن مینجمنٹ
		add_role(
			'bssms_manager',
			esc_html__( 'BSSMS منیجر', 'bssms' ),
			array(
				'read'                      => true,
				'edit_posts'                => false,
				'delete_posts'              => false,
				'bssms_manage_admissions'   => true, // تمام ایڈمیشنز کو دیکھنا/ایڈٹ کرنا
				'bssms_create_admission'    => true,  // نیا داخلہ بنانا
			)
		);

		// 2. BSSMS-Clerk Role: صرف نیا داخلہ شامل کرنے کی اجازت
		add_role(
			'bssms_clerk',
			esc_html__( 'BSSMS کلرک', 'bssms' ),
			array(
				'read'                      => true,
				'edit_posts'                => false,
				'delete_posts'              => false,
				'bssms_create_admission'    => true, // صرف نیا داخلہ بنانا
			)
		);

		// Administrator کو تمام قابلیتیں دیں۔
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_role->add_cap( 'bssms_manage_admissions' );
			$admin_role->add_cap( 'bssms_create_admission' );
		}
	}

	/**
	 * Default/Demo Data شامل کریں۔
	 */
	private static function insert_default_data() {
		global $wpdb;
		$table_courses = $wpdb->prefix . 'bssms_courses';

		// ڈیمو کورسز
		$courses = array(
			array( 'AI Master', 'اے آئی ماسٹر', 50000 ),
			array( 'Data Science Pro', 'ڈیٹا سائنس پرو', 40000 ),
			array( 'Machine Learning', 'مشین لرننگ', 30000 ),
			array( 'Web Development', 'ویب ڈویلپمنٹ', 20000 ),
		);

		foreach ( $courses as $course ) {
			// چیک کریں کہ کورس پہلے سے موجود نہ ہو۔
			$exists = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM $table_courses WHERE course_name_en = %s",
				$course[0]
			) );

			if ( ! $exists ) {
				$wpdb->insert(
					$table_courses,
					array(
						'course_name_en' => sanitize_text_field( $course[0] ),
						'course_name_ur' => sanitize_text_field( $course[1] ),
						'course_fee'     => absint( $course[2] ),
						'is_active'      => 1,
					),
					array( '%s', '%s', '%d', '%d' )
				);
			}
		}

		// ڈیفالٹ ترتیبات
		BSSMS_DB::update_setting( 'theme_mode', 'light' );
		BSSMS_DB::update_setting( 'language', 'ur_en' ); // اردو/انگلش موڈ
        BSSMS_DB::update_setting( 'primary_color', '#0073aa' );
	}
}

// ✅ Syntax verified block end
