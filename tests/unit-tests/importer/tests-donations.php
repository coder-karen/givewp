<?php
/**
 * Meta
 * @package Give\Tests\Importer
 */


class WC_Tests_Give_Import_Donations extends Give_Unit_Test_Case {
	/**
	 * Test CSV file path.
	 *
	 * @var string
	 */
	protected $csv_file = '';

	protected $importer_class = '';

	protected $raw_data = '';

	protected $raw_key = '';

	protected $import_setting = '';

	protected $total = '';

	/**
	 * Set it up.
	 */
	function setUp() {

		// check if import-donation file is include or not to check we are checking for a functions that is being declared in that file.
		$this->assertTrue( function_exists( 'give_save_import_donation_to_db' ) );

		require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/import/class-give-import-donations.php';
		$this->assertTrue( class_exists( 'Give_Import_Donations' ) );


		$this->importer_class = Give_Import_Donations::get_instance();

		// sample CSV file
		$this->csv_file = dirname( __FILE__ ) . '/sample.csv';

		$this->raw_data = give_get_raw_data_from_file( $this->csv_file, 1, 25, ',' );

		$this->raw_key = give_get_raw_data_from_file( $this->csv_file, 0, 0, ',' );

		$this->import_setting = $this->get_import_setting();

		$this->total = $this->importer_class->get_csv_data_from_file_dir( $this->csv_file );

		parent::setUp();
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Get CSV mapped items.
	 *
	 * @since 2.1
	 * @return array
	 */
	private function get_csv_mapped_items() {
		return array(
			'form_title',
			'amount',
			'currency',
			'form_level',
			'post_date',
			'first_name',
			'last_name',
			'company_name',
			'email',
			'mode',
			'post_status',
			'gateway',
			'notes',
			'line1',
			'line2',
			'city',
			'zip',
			'state',
			'country',
		);
	}

	/**
	 * Get import setting
	 *
	 * @return array
	 */
	private function get_import_setting() {

		return $import_setting = array(
			'delimiter'   => 'csv',
			'mode'        => 0,
			'create_user' => 1,
			'delete_csv'  => 0,
			'per_page'    => 25,
			'raw_key'     => $this->get_csv_mapped_items(),
		);
	}

	/**
	 * Get the total number of row from the CSV and count
	 * there are total 11 row 10 donation and 1st one is Donation key name
	 */
	public function test_get_csv_data_from_file_dir() {
		$this->assertEquals( 11, $this->total );
	}

	public function import_donation_in_dry_run() {
		$import_setting = $this->import_setting;

		$raw_key = $import_setting['raw_key'];

		// data from CSV
		$raw_data = $this->raw_data;

		// donation meta key name
		$main_key = $this->main_key;

		// first add donation in dry run mode
		$import_setting['dry_run'] = 1;

		if ( ! empty( $import_setting['dry_run'] ) ) {
			$import_setting['csv_raw_data'] = $raw_data;

			$import_setting['donors_list'] = Give()->donors->get_donors( array(
				'number' => - 1,
				'fields' => array( 'id', 'user_id', 'email' ),
			) );
		}

		$current_key = 1;
		foreach ( $raw_data as $row_data ) {
			$import_setting['donation_key'] = $current_key;
			$payment_id                     = give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
			$current_key ++;
		}
	}

	/**
	 * Test by uploading live donation from CSV
	 *
	 * @since 2.1
	 */
	public function import_donation_in_live( $import_setting = array() ) {
		give_import_donation_report_reset();

		if ( empty( $import_setting ) ) {
			$import_setting = $this->get_import_setting();
		}

		$raw_key = $import_setting['raw_key'];

		$file_dir = $this->csv_file;

		// get the total number of rom from CSV
		$total = $this->importer_class->get_csv_data_from_file_dir( $file_dir );

		// get data from CSV
		$raw_data = give_get_raw_data_from_file( $file_dir, 1, $total, ',' );
		$main_key = give_get_raw_data_from_file( $file_dir, 0, 1, ',' );

		$current_key = 1;
		foreach ( $raw_data as $row_data ) {
			$import_setting['donation_key'] = $current_key;
			$payment_id                     = give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
			$current_key ++;
		}
	}


	/**
	 * Test by uploading live donation from CSV
	 *
	 * @since 2.1
	 */
	public function test_for_live() {

		parent::tearDown();

		give_import_donation_report_reset();
		$import_setting = $this->get_import_setting();
		$raw_key        = $import_setting['raw_key'];

		$file_dir = $this->csv_file;

		// get the total number of rom from CSV
		$total = $this->importer_class->get_csv_data_from_file_dir( $file_dir );

		// get data from CSV
		$raw_data = give_get_raw_data_from_file( $file_dir, 1, $total, ',' );
		$main_key = give_get_raw_data_from_file( $file_dir, 0, 1, ',' );

		$current_key = 1;
		foreach ( $raw_data as $row_data ) {
			$import_setting['donation_key'] = $current_key;
			$payment_id                     = give_save_import_donation_to_db( $raw_key, $row_data, $main_key, $import_setting );
			$current_key ++;
		}

		$report = give_import_donation_report();

		$this->assertEquals( 10, $report['create_donor'] );
		$this->assertEquals( 5, $report['create_form'] );
		$this->assertEquals( 10, $report['create_donation'] );
		$this->assertEquals( 5, $report['duplicate_form'] );
	}

	/**
	 * To test if dry run is working or not perfectly
	 *
	 * @since 2.1
	 */
	public function test_for_dry_run() {

		parent::tearDown();

		give_import_donation_report_reset();

		$this->import_donation_in_dry_run();
		$dry_run_report = give_import_donation_report();

		$this->import_donation_in_live();
		$live_run_report = give_import_donation_report();

		// compared dry run and live run summery
		$this->assertEquals( true, serialize( $dry_run_report ) === serialize( $live_run_report ) );
	}


	/**
	 * To test to check is WP user is created
	 *
	 * @since 2.1
	 */
	public function test_to_check_wp_user_is_created() {

		parent::tearDown();

		give_import_donation_report_reset();

		$this->import_donation_in_live();

		$donor_data = get_user_by( 'email', 'vbranwhite0@desdev.cn' );

		$this->assertTrue( ! empty( $donor_data->ID ) );

		$donor_data = get_user_by( 'email', 'enormansell6@youtu.be' );
		$this->assertTrue( ! empty( $donor_data->ID ) );

		$donor_data = get_user_by( 'email', 'nodonorexists@youtu.be' );
		$this->assertTrue( empty( $donor_data->ID ) );
	}

	/**
	 * To test to check is WP user is not getting created
	 *
	 * @since 2.1
	 */
	public function test_to_check_wp_user_not_created() {

		parent::tearDown();

		give_import_donation_report_reset();

		$import_setting                = $this->get_import_setting();
		$import_setting['create_user'] = 0;

		$this->import_donation_in_live( $import_setting );

		$donor_data = get_user_by( 'email', 'vbranwhite0@desdev.cn' );

		$this->assertTrue( empty( $donor_data->ID ) );

		$donor_data = get_user_by( 'email', 'enormansell6@youtu.be' );
		$this->assertTrue( empty( $donor_data->ID ) );
	}

	/**
	 * To test to check is donor is created
	 *
	 * @since 2.1
	 */
	public function test_to_check_donor_is_created() {

		parent::tearDown();

		give_import_donation_report_reset();

		$this->import_donation_in_live();

		$donor_data = new Give_Donor( 'lgraalman4@mapquest.com' );

		$this->assertTrue( ! empty( $donor_data->id ) );

		$donor_data = new Give_Donor( 'echicchelli8@thetimes.co.uk' );
		$this->assertTrue( ! empty( $donor_data->id ) );


		$donor_data = new Give_Donor( 'nodonorexists@thetimes.co.uk' );
		$this->assertTrue( empty( $donor_data->id ) );
	}

	/**
	 * To test to check is donation form is created or not
	 *
	 * @since 2.1
	 */
	public function test_to_check_donation_form_is_created() {

		parent::tearDown();

		give_import_donation_report_reset();

		$this->import_donation_in_live();

		$form = get_page_by_title( 'Make a wish Foundation', OBJECT, 'give_forms' );
		$this->assertTrue( ! empty( $form->ID ) );
		$form = new Give_Donate_Form( $form->ID );
		$this->assertTrue( ! empty( $form->get_ID() ) );


		$form = get_page_by_title( 'Save the Trees', OBJECT, 'give_forms' );
		$this->assertTrue( ! empty( $form->ID ) );
		$form = new Give_Donate_Form( $form->ID );
		$this->assertTrue( ! empty( $form->get_ID() ) );

		$form = get_page_by_title( 'Help a Child', OBJECT, 'give_forms' );
		$this->assertTrue( ! empty( $form->ID ) );
		$form = new Give_Donate_Form( $form->ID );
		$this->assertTrue( ! empty( $form->get_ID() ) );

		$form = get_page_by_title( 'No Donation Form', OBJECT, 'give_forms' );
		$this->assertTrue( empty( $form->ID ) );
	}


	/**
	 * Test to check if donation form is created
	 *
	 * @since 2.1
	 */
	public function test_to_check_donation_is_created() {

		parent::tearDown();

		give_import_donation_report_reset();
		$this->import_donation_in_live();

		/* Give get all donation */
		$payments = new Give_Payments_Query( array( 'number' => - 1 ) );
		$payments = $payments->get_payments();
		$this->assertEquals( 10, count( $payments ) );

		$donor_data = new Give_Donor( 'lgodball2@hao123.com' );
		/* Give get all donation */
		$payments = new Give_Payments_Query( array( 'number' => - 1, 'donor' => $donor_data->id ) );
		$payments = $payments->get_payments();
		foreach ( $payments as $payment ) {
			$this->assertEquals( 103, absint( $payment->total ) );
			$this->assertEquals( 'Save the Bees', $payment->form_title );
			$this->assertEquals( 'EUR', $payment->currency );
			$this->assertEquals( 'Lindsay', $payment->first_name );
			$this->assertEquals( 'Godball', $payment->last_name );
			$this->assertEquals( 'BIG BAZAR', $payment->get_meta( '_give_donation_company' ) );
			$this->assertEquals( 'lgodball2@hao123.com', $payment->email );
			$this->assertEquals( 'test', $payment->mode );
			$this->assertEquals( 'refunded', $payment->status );
			$this->assertEquals( 'offline', $payment->gateway );
			//$this->assertEquals( '60030 Evergreen Center', $payment->address );

			var_dump( $payment->address );
		}
	}
}