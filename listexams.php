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
	
	// Verificar se a tabela está vazia
    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    if ($row_count == 0) 
    	{
		//carregando base
		$sql_file = plugin_dir_path(__FILE__) . 'data/exams.sql';
		$sql = file_get_contents($sql_file);
		$wpdb->query($sql);
		if (!empty($wpdb->last_error)) 
			{
    		error_log('Erro encontrado: ' . $wpdb->last_error);
    		return;
			}
		}


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

// Adiciona o menu ao painel do WordPress
add_action('admin_menu', 'list_exams_add_admin_menu');

function list_exams_add_admin_menu() 
	{
   add_menu_page(
   	'Configuração Listagem de Exames', // Título da página
      'Lista de Exames',              // Nome do menu
      'manage_options',                     // Permissão necessária
      'list_exams_settings',           // Slug do menu
      'list_exams_router',       // Função que exibe a página
      'dashicons-admin-generic',             // Ícone do menu
      25                                     // Posição do menu
    	);
	}
	
function list_exams_settings_page() 
	{
   global $wpdb;
   global $table_name
   
   // Tratamento de exclusão de registros
   if (isset($_GET['delete_id'])) 
   	{
      $id = intval($_GET['delete_id']);
      $wpdb->delete($table_name, ['id' => $id]);
      echo '<div class="updated"><p>Exame excluído com sucesso!</p></div>';
    	}

 	// Busca os exames cadastrados
   $exams = $wpdb->get_results("SELECT * FROM $table_name");

   echo '<div class="wrap">';
   echo '<h1>Lista de Exames</h1>';
   echo '<a href="?page=list_exams_settings&action=add" class="button-primary">Adicionar Novo Exame</a><br><br>';

   if ($exams) 
   	{
      echo '<table class="wp-list-table widefat fixed striped">';
      echo '<thead><tr><th>ID</th><th>Nome do Exame</th><th>Prazo</th><th>Conservante</th><th>Material</th><th>Ações</th></tr></thead>';
      echo '<tbody>';
      foreach ($exams as $exam) 
      	{
         echo '<tr>';
         echo '<td>' . esc_html($exam->id) . '</td>';
         echo '<td>' . esc_html($exam->exame) . '</td>';
         echo '<td>' . esc_html($exam->prazo) . '</td>';
         echo '<td>' . esc_html($exam->conservante) . '</td>';
         echo '<td>' . esc_html($exam->material) . '</td>';
         echo '<td>';
         echo '<a href="?page=list_exams_settings&action=edit&id=' . esc_attr($exam->id) . '" class="button">Editar</a> ';
         echo '<a href="?page=list_exams_settings&delete_id=' . esc_attr($exam->id) . '" class="button delete-exam" onclick="return confirm(\'Tem certeza que deseja excluir?\')">Excluir</a>';
         echo '</td>';
         echo '</tr>';
        	}
      echo '</tbody></table>';
    	} 
  	else 
  		{
      echo '<p>Nenhum exame cadastrado.</p>';
    	}
   echo '</div>';
	}

function list_exams_handle_form() 
	{
   global $wpdb;
   global $table_name;
    
  	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
   $exame = '';
   $prazo = '';
  	$conservante = '';
  	$material = '';

   if ($id) 
   	{
      // Busca os dados para edição
      $exam = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id");
      if ($exam) 
      	{
         $exame = $exam->exame;
         $prazo = $exam->prazo;
  			$conservante = $exam->conservante;
  			$material = $exam->material;
        	}
   	}

    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    	{
    	$exame = sanitize_text_field($_POST['exame']);
      $prazo = sanitize_text_field($_POST['prazo']);
  		$conservante = sanitize_text_field($_POST['conservante']);
  		$material = sanitize_text_field($_POST['material']);
  		$dados = array ('exame' => $exame,'prazo' => $prazo, 'conservante' => $conservante, 'material' =>$material);
  		$formatos = array ('%s','%d', '%s', '%s');
        
     	if ($id) 
     		{
         // Atualiza exame existente
         $wpdb->update($table_name, $dados, ['id' => $id], $formatos);
         echo '<div class="updated"><p>Exame atualizado com sucesso!</p></div>';
        	} 
     	else 
     		{
         // Insere novo exame
         $wpdb->insert($table_name, $dados);
         if (!empty($wpdb->last_error)) 
         	{
    			error_log('Erro: ' . $wpdb->last_error);
    			return;
				}
         echo '<div class="updated"><p>Exame adicionado com sucesso!</p></div>';
        	}
    	echo '<a href="?page=list_exams_settings" class="button">Voltar à lista</a>';
      return;
    	}

   echo '<h1>' . ($id ? 'Editar' : 'Adicionar') . ' Exame</h1>';
   echo '<form method="post">';
   echo '<table class="form-table">';
   echo '<tr>';
   echo '<th><label for="name">Nome do Exame</label></th>';
   echo '<td><input type="text" id="exame" name="exame" value="' . esc_attr($exame) . '" required class="regular-text"></td>';
   echo '</tr>';
   echo '<tr>';
   echo '<th><label for="name">Prazo</label></th>';
   echo '<td><input type="text" id="prazo" name="prazo" value="' . esc_attr($prazo) . '" required class="regular-text"></td>';
   echo '</tr>';   
   echo '<tr>';
   echo '<th><label for="name">Conservante</label></th>';
   echo '<td><input type="text" id="conservante" name="conservante" value="' . esc_attr($conservante) . '" required class="regular-text"></td>';
   echo '</tr>';
   echo '<tr>';
   echo '<th><label for="name">Material</label></th>';
   echo '<td><input type="text" id="material" name="material" value="' . esc_attr($material) . '" required class="regular-text"></td>';
   echo '</tr>';
   echo '</table>';
   echo '<p><input type="submit" class="button-primary" value="Salvar"></p>';
   echo '</form>';
	}

function list_exams_router() 
	{
   if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')) 
   	{
      list_exams_handle_form();
    	} 
   else 
   	{
      list_exams_settings_page();
    	}
	}

	
register_activation_hook( __FILE__, 'listexams_install' );

register_deactivation_hook(__FILE__, 'listexams_uninstall');

?>
