<?php
/**
 * Plugin Name
 *
 * @package           ListExams
 * @author            Alcione Ferreira
 * @copyright         2023 AlcioneSytes.net
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       ListExams
 * Plugin URI:        https://github.com/ksombrah/listexams
 * Description:       Listagem de Exames Clínicos
 * Version:           1.0.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Alcione Ferreira
 * Author URI:        https://alcionesytes.net
 * Text Domain:       list-exams
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://github.com/ksombrah/listexams
 */
 
 /*
{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {URI to Plugin License}.
*/
global $listexams_db_version;
$listexams_db_version = '1.0';
global $table_name;
$table_name = $wpdb->prefix.'list_exams';

function listexams_install() 
	{

	global $wpdb;
	global $listexams_db_version;
  	global $table_name;
	
	$charset_collate = $wpdb->get_charset_collate();

  	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    	`id` int(11) NOT NULL,
  	`exame` varchar(255) DEFAULT NULL,
	`prazo` int(11) DEFAULT NULL,
  	`conservante` varchar(255) DEFAULT NULL,
  	`material` varchar(255) DEFAULT NULL,
    	PRIMARY KEY (`id`)
	)$charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'listexams_db_version', $listexams_db_version );
	}
	
function listexams_uninstall()
	{
	}
	
function list_exams_func ()
	{
	global $wpdb;
	global $table_name;
	return $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table_name} ORDER BY exame" ) );
	} 
	
function list_exams_html ()
	{
	global $wpdb;
	global $table_name;
	$dados = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table_name} ORDER BY exame" ) );
	$retorno = "<meta name=\"description\" content=\"Confira nossa lista completa de exames laboratoriais, incluindo hemograma, colesterol, glicemia e mais. Saiba mais no Artemis Laboratório.\">";
	$retorno .= "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC\" crossorigin=\"anonymous\">";
	$retorno .= "<link rel=\"stylesheet\" href=\"".plugins_url('css/listexams.css',__FILE__)."\" >\n";   
	$retorno .= "<div class=\"container-fluid\"><div class=\"row\">";
	$hasPart = array();
	if ($dados)
		{
		foreach ($dados as $dado)
			{
			$hasPart[] = $dado->exame;
			$retorno .= "<div class=\"col-sm-3 col-md-6 col-lg-4 txt-artemis\">";
			$retorno .= '<div class="card shadow p-3 mb-5 bg-white rounded">
  <div class="card-header bg-artemis rounded">
    '.$dado->exame.'
  </div>
  <div class="card-body">
    <h5 class="card-title">Exame: '.$dado->exame.'</h5>
    <p class="card-text">Prazo (dias): '.$dado->prazo.'</p>
  </div>
</div>';
			$retorno .= "</div>";
			}
		}
	$retorno .= "</div></div>";
	$retorno .= '
	<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "MedicalTestPanel",
  "name": "Lista de Exames",
  "subTest": [';
  	for ($h=0; $h < count($hasPart); $h++)
  		{
  		$retorno .= '
    {
      "@type": "MedicalTest",
      "name": "'.str_replace('"','\"',$hasPart[$h]).'",
	   "mainEntityOfPage": {
	    "@type": "WebPage",
	    "@id": "https://artemislaboratorio.com.br/"
	    }
    }';
    	if (($h >= 0)&&($h < (count($hasPart) - 1) ))
    		{
    		$retorno .= ',';
    		}
    	}
  $retorno .= '
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "MedicalOrganization",
  "name": "Artemis Laboratório",
  "url": "https://artemislaboratorio.com.br/",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Rua Hilda Bergo Duarte, 1090 - Vila Planalto",
    "addressLocality": "Dourados",
    "addressRegion": "Mato Grosso do Sul",
    "postalCode": "79826-090",
    "addressCountry": "BR"
  }
}
</script>
	';
	return $retorno;
	} 
	
add_shortcode('list_exams_data','list_exams_html');
add_filter('list_exams_db','list_exams_func');
	
register_activation_hook( __FILE__, 'listexams_install' );

register_deactivation_hook(__FILE__, 'listexams_uninstall');

?>
