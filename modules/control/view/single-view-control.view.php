<?php
/**
 * La vue single d'un contrôle.
 *
 * @package   TheEPI
 * @author    Nicolas Domenech <nicolas@eoxia.com>
 * @copyright 2019 Evarisk
 * @since     0.7.0
 * @version   0.7.0
 */

namespace theepi;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="table-row epi-single-control-row view" data-id="<?php echo esc_attr( $control->data['id'] ); ?>">
	<ul style="display : flex">
		<li>
			<span style="text-align: center;">#<?php echo esc_attr( $control->data['id'] ); ?></span> </br>
		</li>

		<li>
			<span> <?php echo do_shortcode( '[theepi_avatar ids="' . $control->data['author_id'] . '" size="60"]' ); ?> </span>
		</li>

		<li>
			<span style="text-align: center;" > <i class="fas fa-calendar-alt"></i> <?php echo esc_attr( $control->data['control_date']['rendered']['date'] ); ?> </span>
		</li>

		<li>
			<span style="text-align: center;" > <?php echo esc_attr( $control->data['comment'] ); ?> </span>
		</li>

		<li>
			<?php if ( esc_attr( $control->data['url'] ) != "No url" ): ?>
				<?php echo esc_attr( $control->data['url'] ); ?>
				<a class="wpeo-button wpeo-tooltip-event button-grey button-square-50 button-rounded"
					href="<?php echo esc_attr( $control->data['url'] ); ?>"
					target="_blank"
					aria-label="<?php esc_html_e( 'Display Url File', 'theepi' ); ?>">
					<i class="fas fa-copy"></i>
				</a>
			<?php else: ?>
				<?php echo esc_attr( $control->data['url'] ); ?>
			<?php endif; ?>
		</li>

		<li>
			<span> <?php echo Control_Class::g()->get_media( $control->data['id'] ) ?> </span>
		</li>

		<li>
			<?php if ( esc_attr( $control->data['status_control'] ) == 'OK' ):  ?>
				<div class="wpeo-button button-green button-square-50">
					<i class="fas fa-check"></i>
				</div>
			<?php elseif ( esc_attr( $control->data['status_control'] ) == 'KO' ): ?>
				<div class="wpeo-button button-red button-square-50">
					<i class="fas fa-exclamation"></i>
				</div>
			<?php elseif ( esc_attr( $control->data['status_control'] ) == 'repair' ): ?>
				<div class="wpeo-button button-square-50" style="background-color : orange; border-color : orange;">
					<i class="fas fa-tools"></i>
				</div>
			<?php elseif ( esc_attr( $control->data['status_control'] ) == 'trash' ): ?>
				<div class="wpeo-button button-square-50" style="background-color : black; border-color : black;">
					<i class="fas fa-trash-alt"></i>
				</div>
			<?php endif; ?>
		</li>
	</ul>
</div>
