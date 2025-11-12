<?php
/**
 * BSSMS Uninstall Script
 * پلگ اِن کے ڈیلیٹ ہونے پر تمام ڈیٹا بیس ٹیبلز اور کسٹم رولز کو ختم کر دیتا ہے۔
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// اگر صارف ایڈمن نہیں ہے تو نہ چلائیں۔
if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

global $wpdb;

// 1. تمام DB Tables کو حذف کریں۔
$table_names = array(
	$wpdb->prefix . 'bssms_courses',
	$wpdb->prefix . 'bssms_admissions',
	$wpdb->prefix . 'bssms_settings',
);

foreach ( $table_names as $table_name ) {
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

// 2. تمام Custom Roles اور Capabilities کو حذف کریں۔
remove_role( 'bssms_manager' );
remove_role( 'bssms_clerk' );

$admin_role = get_role( 'administrator' );
if ( $admin_role ) {
	$admin_role->remove_cap( 'bssms_manage_admissions' );
	$admin_role->remove_cap( 'bssms_create_admission' );
}

// 3. پلگ اِن کی Options کو حذف کریں۔
delete_option( 'bssms_version' );

// ✅ Syntax verified block end
