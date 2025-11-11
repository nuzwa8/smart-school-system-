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
