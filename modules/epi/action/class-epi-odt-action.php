<?php
/**
 * Action qui s'occupe de la Création et la Génération d'un fichier ODT d'un EPI.
 *
 * @author    Jimmy Latour <jimmy@evarisk.com> && Nicolas Domenech <nicolas@eoxia.com>
 * @since     0.5.0
 * @version   0.7.0
 * @copyright 2019 Evarisk
 * @package   TheEPI
 */

namespace theepi;

use eoxia\ODT_Class;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gères toutes les actions des EPI_ODT.
 */
class EPI_ODT_Action {


	/**
	 * Le constructeur.
	 *
	 * @since   0.5.0
	 * @version 0.5.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_export_epi_odt', array( $this, 'callback_export_epi_odt' ) );
	}

	/**
	 * Exporte la fiche de vie d'un EPI en focntion du modèle.
	 *
	 * @since   0.5.0
	 * @version 0.7.0
	 *
	 * @return void
	 */
	public function callback_export_epi_odt() {
		check_ajax_referer( 'export_epi_odt' );

		if ( ! EPI_Class::g()->check_capabilities( 'read_theepi' ) ) {
			wp_send_json_error();
		}

		$upload_dir = wp_upload_dir();

		$id = ! empty( $_POST['id'] ) ? (int) $_POST['id'] : '';

		$epi        = EPI_Class::g()->get( array( 'id' => $id ), true );
		$status_epi = EPI_Class::g()->get_status( $epi );
		$control    = EPI_Class::g()->get_days( $epi );
		$args       = array( 'parent' => $epi );

		// "$audits = \task_manager\Audit_Class::g()->get( array( 'post_parent' => $id ) );"
		$controls = Control_Class::g()->get( array( 'post_parent' => $id ) );

		$picture = array();
		$qrcode  = array();

		$site_id = get_current_blog_id();

		$title  = current_time( 'Ymd' ) . '_';
		$title .= 'EPI_';
		$title .= $site_id . '_';
		$title .= sanitize_title( $epi->data['id'] ) . '_';
		$title .= ODT_Class::g()->get_revision( $epi->data['type'], $epi->data['id'] );
		$title  = str_replace( '-', '_', $title );

		$document_data = array(
			'title'             => $title,
			'guid'              => str_replace( '\\', '/', $upload_dir['baseurl'] ) . '/theepi/' . $epi->data['type'] . '/' . $epi->data['id'] . '/' . sanitize_title( $epi->data['title'] ) . '.odt',
			'path'              => str_replace( '\\', '/', $upload_dir['basedir'] ) . '/theepi/' . $epi->data['type'] . '/' . $epi->data['id'] . '/' . sanitize_title( $epi->data['title'] ) . '.odt',
			'_wp_attached_file' => '/theepi/' . $epi->data['type'] . '/' . $epi->data['id'] . '/' . sanitize_title( $epi->data['title'] ) . '.odt',
		);

		$picture_definition = wp_get_attachment_image_src( $epi->data['thumbnail_id'], 'medium' );
		if ( ! empty( $picture_definition ) ) {
			$picture_final_path = str_replace( '\\', '/', str_replace( site_url( '/', 'http' ), ABSPATH, $picture_definition[0] ) );
			$picture_final_path = str_replace( '\\', '/', str_replace( site_url( '/', 'https' ), ABSPATH, $picture_final_path ) );

			$picture = array(
				'type'   => 'picture',
				'value'  => $picture_final_path,
				'option' => array(
					'size' => 9,
				),
			);
		}

		if ( ! empty( $epi->data['qrcode']['wp_attached_file'] ) ) {
			$qrcode_final_path = $upload_dir['basedir'] . $epi->data['qrcode']['wp_attached_file'];

			$qrcode = array(
				'type'   => 'picture',
				'value'  => $qrcode_final_path,
				'option' => array(
					'size' => 4.5,
				),
			);
		}

		if ( '1970-01-01' === $epi->data['disposal_date']['raw'] ) {
			$disposal = '';
		} else {
			$disposal = $epi->data['disposal_date']['rendered']['date'];
		}

		if ( empty( $epi->data['manager'] ) ) {
			$manager = get_user_by( 'id', $epi->data['author_id'] );
		} else {
			$manager = get_user_by( 'id', $epi->data['manager'] );
		}

		$document_meta = array(
			'photo'         => $picture,
			'reference'     => $epi->data['reference'],
			'status'        => $status_epi,
			'control'       => $control,
			'serial_number' => $epi->data['serial_number'],
			'id'            => $epi->data['unique_identifier'],
			'qrcode'        => $qrcode,
			'url_epi'       => get_option( 'siteurl' ) . '/?p=' . $epi->data['id'],
			'manager'       => $manager->data->display_name,
			'title'         => $epi->data['title'],

			'maker'         => $epi->data['maker'],
			'seller'        => $epi->data['seller'],
			'lifetime'      => $epi->data['lifetime_epi'],
			'manufacture'   => $epi->data['manufacture_date']['rendered']['date'],
			'purchase'      => $epi->data['purchase_date']['rendered']['date'],
			'end_life'      => $epi->data['end_life_date']['rendered']['date'],
			'periodicity'   => $epi->data['periodicity'],
			'commissioning' => $epi->data['commissioning_date']['rendered']['date'],
			'disposal'      => $disposal,

			'url_notice'    => $epi->data['url_notice'],

			// "'audits'        => array( 'type' => 'segment', 'value' => array() ),"
			'controls'      => array(
				'type'  => 'segment',
				'value' => array(),
			),
		);

		if ( empty( $picture ) ) {
			unset( $document_meta['photo'] );
		}

		if ( empty( $qrcode ) ) {
			unset( $document_meta['qrcode'] );
		}


		// foreach ( $audits as $key => $audit ) {
		// 	$user = get_user_by( 'id', $audit->data['author_id'] );
		// 	$tasks  = \task_manager\Task_Class::g()->get( array( 'post_parent' => $audit->data['id'] ) );
		//
		// 	$temp_tasks = array();
		// 	$tasks_points = "";
		// 	foreach ($tasks as $key => $task ) {
		// 		$points = \task_manager\Point_Class::g()->get( array( 'post_id' => $task->data['id'] ) );
		//
		// 		$nbr_point = "(" . count( $points ) . " points définis)";
		// 		$tasks_points .= "\n -> " . $task->data[ 'title' ] . " " . $nbr_point . " <- \n";
		// 		if( empty( $points ) ){
		// 			$tasks_points .= "Aucun point défini \n";
		// 		}else{
		// 			foreach ($points as $key => $point) {
		// 				$tasks_points .= '- ' . $point->data[ 'content' ] . "\n";
		// 			}
		// 		}
		//
		// 	}
		//
		// 	$default_data = array(
		// 	'date_control' => date( 'd/m/Y', strtotime( $audit->data['date']['rendered']['mysql'] ) ),
		// 	'title_audit'  => $audit->data[ 'title' ],
		// 	'tasks_points' => $tasks_points,
		// 	'user'         => $user->data->display_name,
		// 	'status'       => $audit->data['status_audit'],
		// 	);
		//
		// 	$document_meta['audits']['value'][] = $default_data;
		//
		// }

		foreach ( $controls as $key => $control ) {

			$user = get_user_by( 'id', $control->data['author_id'] );

			$document_meta['controls']['value'][] = array(
				'date_control'    => date( 'd/m/Y', strtotime( $control->data['date']['rendered']['mysql'] ) ),
				'control_comment' => $control->data['comment'],
				'user'            => $user->data->display_name,
				'status_control'  => $control->data['status_control'],
			);
		}

		$response = EPI_ODT_Class::g()->save_document_data( $id, $document_meta, $args );

		$response['document']->data['title']             = current_time( 'Ymd' ) . '_';
		$response['document']->data['title']            .= 'EPI_';
		$response['document']->data['title']            .= $site_id . '_';
		$response['document']->data['title']            .= sanitize_title( $epi->data['id'] ) . '_';
		$response['document']->data['title']            .= ODT_Class::g()->get_revision( $epi->data['type'], $epi->data['id'] );
		$response['document']->data['title']             = str_replace( '-', '_', $response['document']->data['title'] );
		$response['document']->data['guid']              = str_replace( '\\', '/', $upload_dir['baseurl'] ) . '/theepi/' . $epi->data['type'] . '/' . $epi->data['id'] . '/' . $response['document']->data['title'] . '.odt';
		$response['document']->data['path']              = str_replace( '\\', '/', $upload_dir['basedir'] ) . '/theepi/' . $epi->data['type'] . '/' . $epi->data['id'] . '/' . $response['document']->data['title'] . '.odt';
		$response['document']->data['_wp_attached_file'] = '/theepi/' . $epi->data['type'] . '/' . $epi->data['id'] . '/' . $response['document']->data['title'] . '.odt';

		$link     = $response['document']->data['guid'];
		$filename = $response['document']->data['title'] . '.odt';

		EPI_ODT_Class::g()->update( $response['document']->data );

		EPI_ODT_Class::g()->create_document( $response['document']->data['id'] );

		wp_send_json_success(
			array(
				'namespace'        => 'theEPI',
				'module'           => 'EPI',
				'callback_success' => 'exportedEPISuccess',
				'filename'         => $filename,
				'link'             => $link,
			)
		);
	}
}

new EPI_ODT_Action();
