<?php
/**
 * BSSMS_Ajax کلاس
 * تمام (AJAX) درخواستوں کو ہینڈل کرتی ہے۔
 * قاعدہ 7: Nonce + Capabilities + Sanitize لازمی ہیں۔
 */
class BSSMS_Ajax {

	/**
	 * نیا داخلہ فارم بچانے کا AJAX ہینڈلر۔
	 */
	public static function handle_save_admission() {
		// قاعدہ 4: check_ajax_referer(), current_user_can()
		check_ajax_referer( 'bssms_save_admission', 'nonce' );

		if ( ! current_user_can( 'bssms_create_admission' ) ) {
			wp_send_json_error( array( 'message_ur' => 'آپ کے پاس داخلہ فارم جمع کرانے کی اجازت نہیں ہے۔', 'message_en' => 'You do not have permission to submit the admission form.' ) );
		}

		// 🟢 یہاں سے Sanitize اور ڈیٹا بیس میں محفوظ کرنے کا کوڈ بعد میں آئے گا (داخلہ پیج کے ساتھ)۔
		
		// ڈیمو رسپانس
		$response = array(
			'success' => true,
			'message_ur' => 'داخلہ فارم کامیابی سے جمع کر دیا گیا ہے۔',
			'message_en' => 'Admission form submitted successfully.',
			'data' => $_POST,
		);

		wp_send_json_success( $response );
	}

	/**
	 * طالب علم کی فہرست حاصل کرنے کا AJAX ہینڈلر۔
	 */
	public static function handle_fetch_students() {
		check_ajax_referer( 'bssms_fetch_students', 'nonce' );

		if ( ! current_user_can( 'bssms_manage_admissions' ) ) {
			wp_send_json_error( array( 'message_ur' => 'آپ کے پاس طالب علموں کی فہرست دیکھنے کی اجازت نہیں ہے۔', 'message_en' => 'You do not have permission to view the students list.' ) );
		}

		// 🟢 یہاں سے Pagination اور فلٹرنگ کے ساتھ ڈیٹا لانے کا کوڈ بعد میں آئے گا۔

		// ڈیمو رسپانس
		$response = array(
			'success' => true,
			'message_ur' => 'طالب علم کی فہرست لوڈ ہو گئی ہے۔',
			'students' => array(), // اصل ڈیٹا بعد میں شامل ہو گا۔
		);

		wp_send_json_success( $response );
	}

	// 🔴 یہاں پر مزید (AJAX) ہینڈلرز (جیسے ترتیبات) بعد میں شامل ہوں گے۔
}

// ✅ Syntax verified block end
/** Part 2 — Admission Page: AJAX Logic and Translation Stub */

// BSSMS_Ajax کلاس کے اندر، نیا handle_translate_text() فنکشن شامل کریں۔

/**
 * ٹرانسلیشن سروس کے لیے AJAX ہینڈلر (Stub).
 * نوٹ: اصل میں یہ ایک خارجی API کال کرے گا، لیکن یہاں صرف ڈیمو کے طور پر ایک سادہ ترجمہ کر رہا ہے۔
 */
public static function handle_translate_text() {
    // check_ajax_referer()
    check_ajax_referer( 'bssms_translate_text', 'nonce' );

    // قاعدہ 4: current_user_can()
    if ( ! current_user_can( 'bssms_create_admission' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس یہ فنکشن استعمال کرنے کی اجازت نہیں ہے۔' ) );
    }

    // قاعدہ 4: sanitize_* functions
    $text_en = isset( $_POST['text_en'] ) ? sanitize_text_field( wp_unslash( $_POST['text_en'] ) ) : '';

    if ( empty( $text_en ) ) {
        wp_send_json_success( array( 'text_ur' => '' ) );
    }

    // 🟢 یہاں پر اصل میں External Translation API (جیسے Google Translate API) کا استعمال ہو گا
    // فی الحال، ہم سادگی کے لیے ایک ڈیمو فنکشن استعمال کر رہے ہیں۔
    // یوزر کو یاد دہانی: اس فنکشن کو کام کرنے کے لیے ایک بیرونی API کی ضرورت ہوگی۔
    
    $demo_translation = self::simple_urdu_transliteration( $text_en );

    wp_send_json_success( array( 'text_ur' => $demo_translation ) );
}

/**
 * صرف ڈیمو مقاصد کے لیے ایک سادہ انگلش سے اردو Transliteration۔
 * یہ خودکار ترجمہ نہیں ہے، بلکہ ناموں کو اردو میں لکھنے کی کوشش ہے۔
 */
private static function simple_urdu_transliteration( $text_en ) {
    $text_en = strtolower( $text_en );
    $map = array(
        'a' => 'ا', 'b' => 'ب', 'p' => 'پ', 't' => 'ت', 'j' => 'ج', 'c' => 'چ', 'h' => 'ح',
        'k' => 'ک', 'g' => 'گ', 'l' => 'ل', 'm' => 'م', 'n' => 'ن', 'w' => 'و', 'o' => 'و',
        'e' => 'ے', 'y' => 'ی', 'i' => 'ی', 'f' => 'ف', 'q' => 'ق', 'r' => 'ر', 's' => 'س',
        'z' => 'ز', 'x' => 'خ', 'd' => 'د', 'u' => 'ُ', 'v' => 'و', 'sh' => 'ش', 'kh' => 'خ',
        'gh' => 'غ', 'dh' => 'دھ', 'th' => 'تھ', 'ch' => 'چ', 'ph' => 'ف',
        'ali' => 'علی', 'ahmed' => 'احمد', 'muhammad' => 'محمد', 'akram' => 'اکرم', 'baba' => 'بابا',
    );
    
    $urdu_text = '';
    // ایک سادہ لوپ جو صرف ٹوکنائزڈ کلیدی الفاظ کو تبدیل کرے گا۔
    $words = explode(' ', $text_en);
    foreach ($words as $word) {
        $found = false;
        foreach ($map as $en => $ur) {
            if ($word === $en) {
                $urdu_text .= $ur . ' ';
                $found = true;
                break;
            }
        }
        // اگر کوئی مکمل میچ نہ ملے تو عام حروف کو ٹرانسلٹریٹ کریں۔
        if (!$found) {
             $urdu_text .= strtr($word, $map) . ' ';
        }
    }
    
    return trim( $urdu_text );
}

// handle_save_admission() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
// اب یہ داخلہ فارم کے فیلڈز کو سنبھالے گا
public static function handle_save_admission() {
    check_ajax_referer( 'bssms_save_admission', 'nonce' );

    if ( ! current_user_can( 'bssms_create_admission' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس داخلہ فارم جمع کرانے کی اجازت نہیں ہے۔', 'message_en' => 'You do not have permission to submit the admission form.' ) );
    }

    // 1. ڈیٹا کو سینیٹائز کریں (قاعدہ 4: sanitize_* functions)
    $data = array(
        'full_name_en'  => sanitize_text_field( wp_unslash( $_POST['full_name_en'] ?? '' ) ),
        'full_name_ur'  => sanitize_text_field( wp_unslash( $_POST['full_name_ur'] ?? '' ) ),
        'father_name_en'=> sanitize_text_field( wp_unslash( $_POST['father_name_en'] ?? '' ) ),
        'father_name_ur'=> sanitize_text_field( wp_unslash( $_POST['father_name_ur'] ?? '' ) ),
        'dob'           => sanitize_text_field( wp_unslash( $_POST['dob'] ?? '' ) ), // Date
        'gender'        => sanitize_text_field( wp_unslash( $_POST['gender'] ?? '' ) ),
        'course_id'     => absint( $_POST['course_id'] ?? 0 ),
        'paid_amount'   => absint( $_POST['paid_amount'] ?? 0 ),
        'payment_method'=> sanitize_text_field( wp_unslash( $_POST['payment_method'] ?? '' ) ),
    );

    // 2. فیلڈز کی جانچ (Validation)
    if ( empty( $data['full_name_en'] ) || empty( $data['father_name_en'] ) || empty( $data['dob'] ) || empty( $data['gender'] ) || $data['course_id'] === 0 ) {
        wp_send_json_error( array( 'message_ur' => 'براہ کرم تمام ضروری فیلڈز (نام، ولدیت، تاریخ پیدائش، کورس) کو پُر کریں۔' ) );
    }

    // 3. کورس کی معلومات حاصل کریں
    global $wpdb;
    $table_courses = $wpdb->prefix . 'bssms_courses';
    $course = $wpdb->get_row( $wpdb->prepare( "SELECT course_fee, course_name_en FROM $table_courses WHERE id = %d", $data['course_id'] ), ARRAY_A );

    if ( ! $course ) {
        wp_send_json_error( array( 'message_ur' => 'منتخب کردہ کورس غیر فعال یا موجود نہیں ہے۔' ) );
    }

    $total_fee = absint( $course['course_fee'] );
    $due_amount = $total_fee - $data['paid_amount'];

    if ( $due_amount < 0 ) {
         wp_send_json_error( array( 'message_ur' => 'ادا شدہ رقم کل فیس سے زیادہ ہے۔ براہ کرم رقم درست کریں۔' ) );
    }
    
    // 4. اسکرین شاٹ کو ہینڈل کریں (اگر موجود ہو)
    $screenshot_url = '';
    if ( ! empty( $_FILES['payment_screenshot'] ) ) {
        $file = $_FILES['payment_screenshot'];
        // WordPress کا بلٹ ان میڈیا فنکشن استعمال کریں
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $upload_overrides = array( 'test_form' => false );
        $movefile = wp_handle_upload( $file, $upload_overrides );

        if ( $movefile && empty( $movefile['error'] ) ) {
            $screenshot_url = $movefile['url'];
        } else {
             wp_send_json_error( array( 'message_ur' => 'اسکرین شاٹ اپ لوڈ کرنے میں خرابی: ' . $movefile['error'] ) );
        }
    } else {
        // اسکرین شاٹ لازمی ہے (لے آؤٹ کے مطابق)
        wp_send_json_error( array( 'message_ur' => 'ادائیگی کا اسکرین شاٹ منسلک کرنا ضروری ہے۔' ) );
    }

    // 5. ڈیٹا بیس میں داخل کریں (قاعدہ 4: $wpdb->prepare() queries)
    $table_admissions = $wpdb->prefix . 'bssms_admissions';
    $insert_data = array(
        'full_name_en'  => $data['full_name_en'],
        'full_name_ur'  => $data['full_name_ur'],
        'father_name_en'=> $data['father_name_en'],
        'father_name_ur'=> $data['father_name_ur'],
        'dob'           => $data['dob'],
        'gender'        => $data['gender'],
        'course_id'     => $data['course_id'],
        'total_fee'     => $total_fee,
        'paid_amount'   => $data['paid_amount'],
        'due_amount'    => $due_amount,
        'payment_screenshot_url' => $screenshot_url,
        // admission_date خود بخود DB سے آئے گا
    );

    $format = array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s' );

    $inserted = $wpdb->insert( $table_admissions, $insert_data, $format );

    if ( $inserted ) {
        $student_id = $wpdb->insert_id;
        $response = array(
            'success' => true,
            'message_ur' => 'داخلہ فارم کامیابی سے جمع کر دیا گیا ہے۔',
            'student_id' => $student_id,
            'student_name_en' => $data['full_name_en'],
            'course_name_en' => $course['course_name_en'],
            'paid' => $data['paid_amount'],
            'due' => $due_amount,
            'total' => $total_fee,
            'percentage' => round( ($data['paid_amount'] / $total_fee) * 100 ),
        );

        wp_send_json_success( $response );
    } else {
        wp_send_json_error( array( 'message_ur' => 'ڈیٹا بیس میں داخلہ محفوظ کرنے میں خرابی پیش آئی۔ ' . $wpdb->last_error ) );
    }
}

// 🔴 یہاں پر مزید (AJAX) ہینڈلرز بعد میں شامل ہوں گے۔

// ✅ Syntax verified block end
	/** Part 5 — Students List: AJAX Handlers for Fetching and Deleting */

// BSSMS_Ajax کلاس کے اندر، handle_fetch_students() اور نیا handle_delete_admission() فنکشن شامل کریں۔

// handle_fetch_students() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
public static function handle_fetch_students() {
    check_ajax_referer( 'bssms_fetch_students', 'nonce' );

    // قاعدہ 4: current_user_can()
    if ( ! current_user_can( 'bssms_manage_admissions' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس طالب علموں کی فہرست دیکھنے کی اجازت نہیں ہے۔', 'message_en' => 'You do not have permission to view the students list.' ) );
    }

    // 1. فلٹر دلائل حاصل کریں اور سینیٹائز کریں
    $args = array(
        'per_page' => absint( $_POST['per_page'] ?? 10 ),
        'page'     => absint( $_POST['page'] ?? 1 ),
        'search'   => sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) ),
        'course_id'=> absint( $_POST['course_id'] ?? 0 ),
        'status'   => sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) ),
        'date_from'=> sanitize_text_field( wp_unslash( $_POST['date_from'] ?? '' ) ),
        'date_to'  => sanitize_text_field( wp_unslash( $_POST['date_to'] ?? '' ) ),
    );

    // 2. ڈیٹا بیس سے ڈیٹا لائیں
    $data = BSSMS_DB::get_filtered_admissions( $args ); // قاعدہ 7: Optimized Loops + Pagination

    $response = array(
        'success' => true,
        'message_ur' => 'طالب علم کی فہرست کامیابی سے لوڈ ہو گئی ہے۔',
        'data' => $data,
        'filters' => $args,
    );

    wp_send_json_success( $response );
}

/**
 * ایک داخلہ ریکارڈ کو حذف کرنے کا AJAX ہینڈلر۔
 */
public static function handle_delete_admission() {
    check_ajax_referer( 'bssms_delete_admission', 'nonce' ); // نیا Nonce: bssms_delete_admission

    // صرف وہ یوزر حذف کر سکتے ہیں جن کے پاس 'manage_options' یا 'bssms_manage_admissions' کی قابلیت ہو۔
    if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'bssms_manage_admissions' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس اس ریکارڈ کو حذف کرنے کی اجازت نہیں ہے۔' ) );
    }

    $id = absint( $_POST['id'] ?? 0 );

    if ( $id === 0 ) {
        wp_send_json_error( array( 'message_ur' => 'ریکارڈ ID غائب ہے۔' ) );
    }

    $deleted = BSSMS_DB::delete_admission( $id );

    if ( $deleted ) {
        wp_send_json_success( array( 
            'message_ur' => 'ریکارڈ #' . $id . ' کامیابی سے حذف کر دیا گیا ہے۔', 
            'id' => $id 
        ) );
    } else {
        wp_send_json_error( array( 'message_ur' => 'ریکارڈ حذف کرنے میں خرابی پیش آئی یا ریکارڈ موجود نہیں تھا۔' ) );
    }
}

// 🔴 یہاں پر مزید (AJAX) ہینڈلرز بعد میں شامل ہوں گے۔

// ✅ Syntax verified block end
/** Part 9 — Courses Setup: AJAX Handlers for CRUD Operations */

// BSSMS_Ajax کلاس کے اندر، نئے handle_fetch_courses(), handle_save_course(), اور handle_delete_course() فنکشنز شامل کریں۔

/**
 * کورسز کی فہرست حاصل کرنے کا AJAX ہینڈلر۔
 */
public static function handle_fetch_courses() {
    check_ajax_referer( 'bssms_fetch_courses', 'nonce' );

    // قاعدہ 4: current_user_can()
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس کورسز کی فہرست دیکھنے کی اجازت نہیں ہے۔', 'message_en' => 'You do not have permission to view courses list.' ) );
    }

    // فلٹر دلائل حاصل کریں اور سینیٹائز کریں
    $search = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
    $status = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) ); // active/inactive

    // ڈیٹا بیس سے ڈیٹا لائیں
    $courses = BSSMS_DB::get_all_courses_with_filters( $search, $status );

    $response = array(
        'success' => true,
        'message_ur' => 'کورسز کی فہرست لوڈ ہو گئی ہے۔',
        'courses' => $courses,
    );

    wp_send_json_success( $response );
}

/**
 * نیا کورس شامل کرنے یا موجودہ کو اپ ڈیٹ کرنے کا AJAX ہینڈلر۔
 */
public static function handle_save_course() {
    check_ajax_referer( 'bssms_save_course', 'nonce' ); // نیا Nonce: bssms_save_course

    // قاعدہ 4: current_user_can() (صرف ایڈمن)
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس کورسز کو محفوظ کرنے کی اجازت نہیں ہے۔' ) );
    }

    // ڈیٹا کو سینیٹائز کریں
    $id = absint( $_POST['course_id'] ?? 0 );
    $data = array(
        'course_name_en' => sanitize_text_field( wp_unslash( $_POST['course_name_en'] ?? '' ) ),
        'course_name_ur' => sanitize_text_field( wp_unslash( $_POST['course_name_ur'] ?? '' ) ),
        'course_fee'     => absint( $_POST['course_fee'] ?? 0 ),
        'is_active'      => ( isset( $_POST['is_active'] ) && $_POST['is_active'] === 'on' ) ? 1 : 0,
    );

    // بنیادی جانچ
    if ( empty( $data['course_name_en'] ) || $data['course_fee'] <= 0 ) {
        wp_send_json_error( array( 'message_ur' => 'کورس کا نام اور فیس کی رقم ضروری ہے۔' ) );
    }

    $result = BSSMS_DB::save_course( $data, $id );

    if ( $result ) {
        $msg = ($id > 0) ? 'کورس کامیابی سے اپ ڈیٹ ہو گیا ہے۔' : 'نیا کورس کامیابی سے شامل کر دیا گیا ہے۔';
        wp_send_json_success( array( 
            'message_ur' => $msg, 
            'id' => $result,
            'is_new' => $id === 0,
            'course_data' => array_merge(['id' => $result], $data)
        ) );
    } else {
        wp_send_json_error( array( 'message_ur' => 'کورس محفوظ کرنے میں خرابی پیش آئی۔' ) );
    }
}

/**
 * کورس کو حذف کرنے کا AJAX ہینڈلر۔
 */
public static function handle_delete_course() {
    check_ajax_referer( 'bssms_delete_course', 'nonce' ); // نیا Nonce: bssms_delete_course

    // صرف ایڈمن کو حذف کرنے کی اجازت
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس کورس کو حذف کرنے کی اجازت نہیں ہے۔' ) );
    }

    $id = absint( $_POST['id'] ?? 0 );
    $deleted = BSSMS_DB::delete_course( $id );

    if ( $deleted === 1 ) {
        wp_send_json_success( array( 
            'message_ur' => 'کورس #' . $id . ' کامیابی سے حذف ہو گیا۔', 
            'id' => $id 
        ) );
    } elseif ($deleted === 0) {
        wp_send_json_error( array( 'message_ur' => 'ریکارڈ حذف کرنے میں خرابی یا کورس موجود نہیں تھا۔' ) );
    } else {
        wp_send_json_error( array( 'message_ur' => 'کورس حذف نہیں ہو سکا کیونکہ یہ پہلے سے طالب علم کے ریکارڈ میں استعمال ہو رہا ہے (اسے غیر فعال کر دیا گیا ہے)۔' ) );
    }
}

// 🔴 یہاں پر مزید (AJAX) ہینڈلرز بعد میں شامل ہوں گے۔

// ✅ Syntax verified block end
/** Part 13 — Settings Page: AJAX Handlers for Settings Management */

// BSSMS_Ajax کلاس کے اندر، handle_save_settings() اور نیا handle_reset_defaults() فنکشن شامل کریں۔

// handle_save_settings() فنکشن کا نیا اور مکمل کوڈ (پُرانے کی جگہ پر):
public static function handle_save_settings() {
    check_ajax_referer( 'bssms_save_settings', 'nonce' );

    // قاعدہ 4: current_user_can() (صرف ایڈمن)
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس ترتیبات کو محفوظ کرنے کی اجازت نہیں ہے۔' ) );
    }

    $updated_settings = 0;
    
    // 1. عمومی ترتیبات (General)
    $general_keys = ['academy_name', 'admin_email', 'default_currency', 'date_format'];
    foreach ($general_keys as $key) {
        if (isset($_POST[$key])) {
            $value = sanitize_text_field( wp_unslash( $_POST[$key] ) );
            if (BSSMS_DB::update_setting($key, $value)) $updated_settings++;
        }
    }

    // 2. تھیم اور رنگ (Theme & Branding)
    $theme_mode = isset($_POST['theme_mode']) ? sanitize_text_field(wp_unslash($_POST['theme_mode'])) : 'light';
    if (BSSMS_DB::update_setting('theme_mode', $theme_mode)) $updated_settings++;

    $primary_color = isset($_POST['primary_color']) ? sanitize_hex_color(wp_unslash($_POST['primary_color'])) : '#0073aa';
    if (BSSMS_DB::update_setting('primary_color', $primary_color)) $updated_settings++;
    
    // 3. زبان کی ترتیبات (Language)
    $bilingual = (isset($_POST['enable_bilingual_labels']) && $_POST['enable_bilingual_labels'] === 'on') ? 'on' : 'off';
    if (BSSMS_DB::update_setting('enable_bilingual_labels', $bilingual)) $updated_settings++;

    $auto_translate = (isset($_POST['enable_auto_urdu_translation']) && $_POST['enable_auto_urdu_translation'] === 'on') ? 'on' : 'off';
    if (BSSMS_DB::update_setting('enable_auto_urdu_translation', $auto_translate)) $updated_settings++;

    // 4. لوگو مینجمنٹ
    $logo_url = sanitize_url( wp_unslash( $_POST['logo_url'] ?? '' ) );
    if (BSSMS_DB::update_setting('logo_url', $logo_url)) $updated_settings++;
    
    // 5. لوگو اپ لوڈ (اگر فائل موجود ہو)
    if ( ! empty( $_FILES['logo_file'] ) && $_FILES['logo_file']['size'] > 0) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $upload_overrides = array( 'test_form' => false );
        $movefile = wp_handle_upload( $_FILES['logo_file'], $upload_overrides );

        if ( $movefile && empty( $movefile['error'] ) ) {
            BSSMS_DB::update_setting('logo_url', $movefile['url']);
            $updated_settings++;
        } else {
             // فائل اپ لوڈ کی خرابی کی صورت میں بھی دیگر ترتیبات کو محفوظ کر کے success بھیجیں
             wp_send_json_error( array( 'message_ur' => 'لوگو اپ لوڈ کرنے میں خرابی: ' . $movefile['error'], 'updated_count' => $updated_settings ) );
        }
    }


    wp_send_json_success( array( 
        'message_ur' => 'ترتیبات کامیابی سے محفوظ کر لی گئیں۔',
        'updated_count' => $updated_settings,
        'new_theme_mode' => $theme_mode,
    ) );
}

/**
 * تمام ڈیفالٹ ترتیبات کو دوبارہ سیٹ کرنے کا AJAX ہینڈلر۔
 */
public static function handle_reset_defaults() {
     check_ajax_referer( 'bssms_reset_defaults', 'nonce' ); // نیا Nonce: bssms_reset_defaults

    // صرف ایڈمن کو اجازت
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message_ur' => 'آپ کے پاس ترتیبات ری سیٹ کرنے کی اجازت نہیں ہے۔' ) );
    }

    // تمام ترتیبات کو حذف کر دیں تاکہ وہ DB Helper میں موجود ڈیفالٹ ویلیوز پر واپس آ جائیں
    global $wpdb;
    $table_settings = $wpdb->prefix . 'bssms_settings';
    
    // صرف وہ ترتیبات حذف کریں جو ہم نے بنائی ہیں، نہ کہ کورسز/ایڈمیشنز
    $keys_to_reset = ['academy_name', 'admin_email', 'default_currency', 'date_format', 'theme_mode', 'logo_url', 'enable_bilingual_labels', 'enable_auto_urdu_translation', 'primary_color'];
    
    // قاعدہ 4: $wpdb->prepare() queries
    $placeholders = implode( ', ', array_fill( 0, count( $keys_to_reset ), '%s' ) );
    $deleted_rows = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_settings WHERE setting_key IN ($placeholders)", $keys_to_reset ) );

    wp_send_json_success( array( 
        'message_ur' => 'تمام ترتیبات کو کامیابی سے فیکٹری ڈیفالٹس پر ری سیٹ کر دیا گیا ہے۔',
        'deleted_count' => $deleted_rows,
    ) );
}

// 🔴 یہاں پر مزید (AJAX) ہینڈلرز ختم ہو رہے ہیں۔

// ✅ Syntax verified block end
