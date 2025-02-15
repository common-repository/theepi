<?php
/**
 * Fichier boot du plugin
 *
 * @package   TheEPI
 * @author    Nicolas Domenech <nicolas@eoxia.com>
 * @copyright 2019 Evarisk
 * @since     0.1.0
 * @version   0.7.0
 */

namespace theepi;

use eoxia\Init_Util;

/**
 * Plugin Name: TheEPI
 * Plugin URI:  http://www.evarisk.com/document-unique-logiciel
 * Description: TheEPI is the open source software for the daily management of your PPE.
 * Ensure the traceability of the PPE, the monitoring of the maintenance operations and the state of the PPE.
 * Version:     0.7.0
 * Author:      Evarisk
 * Author URI:  http://www.evarisk.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /core/assets/languages
 * Text Domain: theepi
 */

DEFINE( 'PLUGIN_THEEPI_PATH', realpath( plugin_dir_path( __FILE__ ) ) . '/' );
DEFINE( 'PLUGIN_THEEPI_URL', plugins_url( basename( __DIR__ ) ) . '/' );
DEFINE( 'PLUGIN_THEEPI_DIR', basename( __DIR__ ) );

require_once 'core/external/eo-framework/eo-framework.php';

Init_Util::g()->exec( PLUGIN_THEEPI_PATH, basename( __FILE__, '.php' ) );
