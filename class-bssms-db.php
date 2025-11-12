<?php
/**
 * BSSMS_DB کلاس
 * ڈیٹا بیس کے تمام محفوظ آپریشنز کو سنبھالنے کے لیے ایک ہیلپر کلاس۔
 * $wpdb->prepare() کا استعمال لازمی ہے۔
 */
class BSSMS_DB {

	// ------------------ General Settings ------------------

	/**
	 * سسٹم کی کسی بھی ترتیب کی ویلیو حاصل کریں۔
	 */
	public static function get_setting( $key, $default = '' ) {
		global $wpdb;
		$table_settings = $wpdb->prefix . 'bssms_settings';

		$sql = $wpdb->prepare(
			"SELECT setting_value FROM $table_settings WHERE setting_key = %s",
			$key
		);

		$value = $wpdb->get_var( $sql );

		return is_null( $value ) ? $default : maybe_unserialize( $value );
	}

	/**
	 * سسٹم کی کسی بھی ترتیب کی ویلیو کو شامل یا اپ ڈیٹ کریں۔
	 */
	public static function update_setting( $key, $value ) {
		global $wpdb;
		$table_settings = $wpdb->prefix . 'bssms_settings';

		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table_settings WHERE setting_key = %s",
			$key
		) );

		if ( $exists ) {
			// اپ ڈیٹ
			$result = $wpdb->update(
				$table_settings,
				array( 'setting_value' => maybe_serialize( $value ) ),
				array( 'setting_key' => $key ),
				array( '%s' ),
				array( '%s' )
			);
		} else {
			// شامل کریں (Insert)
			$result = $wpdb->insert(
				$table_settings,
				array(
					'setting_key'   => $key,
					'setting_value' => maybe_serialize( $value ),
				),
				array( '%s', '%s' )
			);
		}

		return (bool) $result;
	}
    
    /**
	 * متعدد ترتیبات کو ایک ساتھ حاصل کریں (بلک ریڈ)۔ (Part 12)
	 */
	public static function get_settings_bulk( $keys ) {
		global $wpdb;
		$table_settings = $wpdb->prefix . 'bssms_settings';
		
		$safe_keys = array_map( 'sanitize_key', $keys );
		$placeholders = implode( ', ', array_fill( 0, count( $safe_keys ), '%s' ) );
		
		$sql = "SELECT setting_key, setting_value FROM $table_settings WHERE setting_key IN ($placeholders)";
		
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $safe_keys ), ARRAY_A );
		
		$settings = array();
		foreach ($results as $row) {
			$settings[ $row['setting_key'] ] = maybe_unserialize( $row['setting_value'] );
		}
		
		// تمام مطلوبہ keys کے لیے ڈیفالٹ شامل کریں
		$defaults = [
			'academy_name' => 'بابا اے آئی اکیڈمی',
			'admin_email' => get_option('admin_email'),
			'default_currency' => 'PKR',
			'date_format' => 'd-m-Y',
			'theme_mode' => 'light',
			'logo_url' => '',
			'enable_bilingual_labels' => 'on',
			'enable_auto_urdu_translation' => 'on',
			'primary_color' => '#0073aa', // ڈیفالٹ WordPress بلیو
		];
		
		return array_merge( $defaults, $settings );
	}


	// ------------------ Courses Management ------------------

	/**
	 * تمام فعال کورسز کی فہرست حاصل کریں۔
	 */
	public static function get_all_active_courses() {
		global $wpdb;
		$table = $wpdb->prefix . 'bssms_courses';

		$sql = $wpdb->prepare( "SELECT id, course_name_en, course_name_ur, course_fee FROM $table WHERE is_active = %d ORDER BY course_fee DESC", 1 );

		return $wpdb->get_results( $sql, ARRAY_A );
	}
    
    /**
	 * کورسز کی فہرست تلاش اور فلٹرز کے ساتھ حاصل کریں۔ (Part 8)
	 */
	public static function get_all_courses_with_filters( $search = '', $status = '' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'bssms_courses';
		
		$where = 'WHERE 1=1';
		$params = array();

		// سرچ فلٹر
		if ( ! empty( $search ) ) {
			$search = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= ' AND (course_name_en LIKE %s OR course_name_ur LIKE %s)';
			$params[] = $search;
			$params[] = $search;
		}

		// حیثیت فلٹر
		if ( $status === 'active' ) {
			$where .= ' AND is_active = %d';
			$params[] = 1;
		} elseif ( $status === 'inactive' ) {
			$where .= ' AND is_active = %d';
			$params[] = 0;
		}

		$sql = "SELECT * FROM $table $where ORDER BY id DESC";
		
		$results = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return $results;
	}
    
    /**
	 * ایک نیا کورس شامل کریں یا موجودہ کو اپ ڈیٹ کریں۔ (Part 8)
	 */
	public static function save_course( $data, $id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'bssms_courses';

		// ڈیٹا کو سینیٹائز کریں
		$insert_data = array(
			'course_name_en' => sanitize_text_field( $data['course_name_en'] ),
			'course_name_ur' => sanitize_text_field( $data['course_name_ur'] ),
			'course_fee'     => absint( $data['course_fee'] ),
			'is_active'      => absint( $data['is_active'] ),
		);

		$format = array( '%s', '%s', '%d', '%d' );

		if ( $id > 0 ) {
			// اپ ڈیٹ
			$updated = $wpdb->update( $table, $insert_data, array( 'id' => $id ), $format, array( '%d' ) );
			return $updated !== false ? $id : false;
		} else {
			// شامل کریں (Insert)
			$inserted = $wpdb->insert( $table, $insert_data, $format );
			return $inserted ? $wpdb->insert_id : false;
		}
	}

	/**
	 * ایک کورس کو حذف کریں (Part 8)
	 */
	public static function delete_course( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'bssms_courses';

		// طالب علموں کے ریکارڈ کی جانچ کریں جو اس کورس پر منحصر ہیں (سیکیورٹی گارڈ)
		$tbl_admissions = $wpdb->prefix . 'bssms_admissions';
		$is_used = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $tbl_admissions WHERE course_id = %d", $id ) );

		if ($is_used > 0) {
			// اگر کورس استعمال ہو رہا ہے تو حذف کرنے کی بجائے غیر فعال (Inactive) کر دیں۔
			return $wpdb->update( $table, array( 'is_active' => 0 ), array( 'id' => $id ), array( '%d' ), array( '%d' ) ) !== false;
		}

		// اگر استعمال نہیں ہو رہا تو حذف کریں
		$deleted = $wpdb->delete(
			$table,
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);

		return (bool) $deleted;
	}

	// ------------------ Admissions Management ------------------

	/**
	 * فلٹرز کے ساتھ تمام داخلہ شدہ طالب علموں کا ڈیٹا حاصل کریں۔ (Part 4)
	 */
	public static function get_filtered_admissions( $args = array() ) {
		global $wpdb;
		$tbl_admissions = $wpdb->prefix . 'bssms_admissions';
		$tbl_courses = $wpdb->prefix . 'bssms_courses';
		
		// ڈیفالٹ دلائل
		$defaults = array(
			'per_page' => 10,
			'page'     => 1,
			'search'   => '',
			'course_id'=> 0,
			'status'   => '', // all, paid, due
			'date_from'=> '',
			'date_to'  => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$where = 'WHERE 1=1';
		$params = array();

		// 1. سرچ فلٹر
		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where .= ' AND (adm.full_name_en LIKE %s OR adm.full_name_ur LIKE %s OR adm.father_name_en LIKE %s OR adm.father_name_ur LIKE %s)';
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
		}

		// 2. کورس فلٹر
		if ( absint( $args['course_id'] ) > 0 ) {
			$where .= ' AND adm.course_id = %d';
			$params[] = absint( $args['course_id'] );
		}

		// 3. ادائیگی کی حیثیت (Status) فلٹر
		if ( ! empty( $args['status'] ) ) {
			if ( $args['status'] === 'paid' ) {
				$where .= ' AND adm.due_amount = 0';
			} elseif ( $args['status'] === 'due' ) {
				$where .= ' AND adm.due_amount > 0';
			}
		}
		
		// 4. تاریخ رینج فلٹر
		if ( ! empty( $args['date_from'] ) && ! empty( $args['date_to'] ) ) {
			$where .= ' AND DATE(adm.admission_date) BETWEEN %s AND %s';
			$params[] = sanitize_text_field( $args['date_from'] );
			$params[] = sanitize_text_field( $args['date_to'] );
		}

		// کل ریکارڈز کی گنتی
		$sql_count = "SELECT COUNT(adm.id) FROM $tbl_admissions AS adm $where";
		$total_items = $wpdb->get_var( $wpdb->prepare( $sql_count, $params ) );

		// ڈیٹا لانے کے لیے SQL
		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		
		$sql_data = "
			SELECT adm.*, c.course_name_en, c.course_name_ur, c.course_fee
			FROM $tbl_admissions AS adm
			JOIN $tbl_courses AS c ON adm.course_id = c.id
			$where
			ORDER BY adm.admission_date DESC
			LIMIT %d OFFSET %d
		";
		
		$params[] = absint( $args['per_page'] );
		$params[] = absint( $offset );

		$results = $wpdb->get_results( $wpdb->prepare( $sql_data, $params ), ARRAY_A );

		// خلاصہ (Summary) ڈیٹا
		$sql_summary = "
			SELECT 
				COUNT(adm.id) AS total_students,
				SUM(adm.total_fee) AS total_income,
				SUM(adm.paid_amount) AS total_paid,
				SUM(adm.due_amount) AS total_due
			FROM $tbl_admissions AS adm 
		";
		$summary = $wpdb->get_row( $sql_summary, ARRAY_A );

		return array(
			'items' => $results,
			'total_items' => $total_items,
			'per_page' => $args['per_page'],
			'current_page' => $args['page'],
			'summary' => $summary,
		);
	}

	/**
	 * ایک داخلہ ریکارڈ کو حذف کریں (Part 4)
	 */
	public static function delete_admission( $id ) {
		global $wpdb;
		$tbl_admissions = $wpdb->prefix . 'bssms_admissions';

		// پہلے فائل پاتھ حاصل کریں تاکہ اسے حذف کیا جا سکے
		$screenshot_url = $wpdb->get_var( $wpdb->prepare( "SELECT payment_screenshot_url FROM $tbl_admissions WHERE id = %d", $id ) );

		// ریکارڈ حذف کریں
		$deleted = $wpdb->delete(
			$tbl_admissions,
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);

        // نوٹ: فائل سسٹم سے اسکرین شاٹ کو ہٹانے کی لاجک یہاں شامل ہو گی

		return (bool) $deleted;
	}

	// ------------------ Dashboard Management ------------------

	/**
	 * ڈیش بورڈ کے اہم KPIs اور سمری ڈیٹا حاصل کریں۔ (Part 16)
	 */
	public static function get_dashboard_kpis() {
		global $wpdb;
		$tbl_admissions = $wpdb->prefix . 'bssms_admissions';
		$tbl_courses = $wpdb->prefix . 'bssms_courses';
		$current_month_start = date( 'Y-m-01 00:00:00' );
		$last_month_start = date( 'Y-m-01 00:00:00', strtotime( '-1 month' ) );

		// 1. مرکزی KPIs (Total Students, Fees)
		$kpis = $wpdb->get_row( "
			SELECT 
				COUNT(id) AS total_students_enrolled,
				SUM(total_fee) AS total_fee_collected,
				SUM(paid_amount) AS total_paid_amount,
				SUM(due_amount) AS total_due_amount
			FROM $tbl_admissions
		", ARRAY_A );

		// 2. فعال کورسز کی گنتی
		$active_courses_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM $tbl_courses WHERE is_active = %d", 1 ) );
		
		// 3. داخلہ کی شرح کا موازنہ (گزشتہ مہینے سے)
		$current_month_admissions = $wpdb->get_var( $wpdb->prepare( 
			"SELECT COUNT(id) FROM $tbl_admissions WHERE admission_date >= %s", 
			$current_month_start 
		) );
		$last_month_admissions = $wpdb->get_var( $wpdb->prepare( 
			"SELECT COUNT(id) FROM $tbl_admissions WHERE admission_date >= %s AND admission_date < %s", 
			$last_month_start, $current_month_start 
		) );
		
		// 4. فیس کی حیثیت کا بریک ڈاؤن
		$payment_breakdown = $wpdb->get_results( "
			SELECT 
				CASE 
					WHEN due_amount = 0 THEN 'fully_paid' 
					WHEN paid_amount > 0 AND due_amount > 0 THEN 'partially_paid'
					ELSE 'not_paid'
				END as payment_status,
				COUNT(id) as count
			FROM $tbl_admissions
			GROUP BY payment_status
		", ARRAY_A );
		
		// 5. تمام ڈیٹا کو اکٹھا کریں
		return array(
			'students_count' => absint( $kpis['total_students_enrolled'] ?? 0 ),
			'fee_collected' => absint( $kpis['total_paid_amount'] ?? 0 ),
			'fee_dues' => absint( $kpis['total_due_amount'] ?? 0 ),
			'active_courses' => absint( $active_courses_count ?? 0 ),
			'admissions_change' => self::calculate_percentage_change( $current_month_admissions, $last_month_admissions ),
			'payment_breakdown' => $payment_breakdown,
		);
	}

	/**
	 * داخلہ کی گرافنگ کے لیے ڈیٹا حاصل کریں (تاریخ کے مطابق)۔ (Part 16)
	 */
	public static function get_admissions_over_time( $period = '30days' ) {
		global $wpdb;
		$tbl_admissions = $wpdb->prefix . 'bssms_admissions';
		
		if ( $period === '6months' ) {
			$start_date = date( 'Y-m-01', strtotime( '-6 months' ) );
			$group_format = '%Y-%m'; // گروپنگ: سال اور مہینہ
		} else { // 30days
			$start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
			$group_format = '%Y-%m-%d'; // گروپنگ: تاریخ
		}

		$sql = $wpdb->prepare( "
			SELECT 
				DATE_FORMAT(admission_date, %s) AS period_label,
				COUNT(id) AS count
			FROM $tbl_admissions
			WHERE admission_date >= %s
			GROUP BY period_label
			ORDER BY admission_date ASC
		", $group_format, $start_date );

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * حالیہ داخلہ جات کی فہرست حاصل کریں۔ (Part 16)
	 */
	public static function get_recent_admissions( $limit = 5 ) {
		global $wpdb;
		$tbl_admissions = $wpdb->prefix . 'bssms_admissions';
		$tbl_courses = $wpdb->prefix . 'bssms_courses';
		
		$sql = $wpdb->prepare( "
			SELECT 
				adm.id, 
				adm.full_name_en, 
				adm.full_name_ur, 
				adm.admission_date, 
				adm.paid_amount,
				adm.due_amount,
				c.course_name_en
			FROM $tbl_admissions AS adm
			JOIN $tbl_courses AS c ON adm.course_id = c.id
			ORDER BY adm.admission_date DESC
			LIMIT %d
		", $limit );

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * فیصد تبدیلی کیلکولیٹ کرنے کے لیے ہیلپر فنکشن۔ (Part 16)
	 */
	private static function calculate_percentage_change( $current, $previous ) {
		if ( $previous == 0 ) {
			return $current > 0 ? 100.0 : 0.0;
		}
		return round( ( ( $current - $previous ) / $previous ) * 100, 1 );
	}
}

// ✅ Syntax verified block end
