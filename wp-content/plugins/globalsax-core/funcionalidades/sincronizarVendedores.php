<?php
$cantidadImportar = get_user_meta(1,"importar_sellers",true);
$totalImportar = get_user_meta(1,"total_importar_sellers",true);
//echo 'Cantidad total: '.$cantidadImportar;
/**LLAMADA AJAX**/
add_action('wp_ajax_get_sincronizar_vendedor', 'ajax_get_sincronizar_vendedor');
add_action('wp_ajax_nopriv_get_sincronizar_vendedor', 'ajax_get_sincronizar_vendedor');

/*function console_log( $data ){
  echo '<script>';
  echo 'console.log('. json_encode( $data ) .')';
  echo '</script>';
}*/

function ajax_get_sincronizar_vendedor(){
	update_user_meta(1,'importar_sellers',-99);
	echo get_sincronizar_vendedor();
}


function get_sincronizar_vendedor(){

	$cantidadImportar = get_user_meta(1,"importar_sellers",true);
	if($cantidadImportar == -99 || $cantidadImportar > 0){
		/**Obtener el archivo JSON**/
		global $wpdb;
		$commonurl = get_user_meta(1, 'url', true);
		$url = $commonurl .'/api/Seller';
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_URL,$url);

		$json = curl_exec($ch);

		if(curl_exec($ch) === false)

		{

			echo 'Curl error: ' . curl_error($ch);

		}

		else {

			echo 'Operación completada sin errores';

		}

		curl_close($ch);

		/**Convertir a JSON**/

		$sellers = json_decode($json, true);

		$amount = (int) sizeof($sellers);

        update_user_meta(1,'total_importar_sellers',$amount);

		/**Crear los atributos si no existen**/

		/*create_att($sellers);

		/**Insertar los vendedores**/

		insert_sellers($sellers);

		/**********************************************************************************************************************/

		//exit;

	} else {

		echo 'No se ha invocado ninguna llamada - get_sincronizar_vendedor';

	}

}

function insert_sellers ($sellers)
{
  if (!empty($sellers)) // No point proceeding if there are no sellers
    {
      //array_map('insert_seller', $sellers);
		$comienzoImportado = get_user_meta(1,"importar_sellers",true);
		if($comienzoImportado == -99) $comienzoImportado = 0;
		$cantidadFija = sizeof($sellers);
		$finImportado = $comienzoImportado + $cantidadFija;
		$i = 0;
		foreach ($sellers as $seller_data) // Go through each attribute
		{
			if($i >= $comienzoImportado and $i<= $finImportado){
				insert_seller($seller_data);
			}
			$i++;
			if($i == $finImportado){break;}
		}
		$totalImportar = get_user_meta(1,"total_importar_sellers",true);
		if($finImportado >=  $totalImportar){
			update_user_meta(1,'importar_sellers',0);
			update_user_meta(1,'total_importar_sellers',0);
		}else{
			update_user_meta(1,'importar_sellers',$finImportado);
		}
    }
}

/**/

function insert_seller($seller_data)
{
	$IdWp = get_user_meta(1,'seller_key_'.$seller_data['seller_Id'], true);
	if (empty($IdWp)){
		$values = array(
 			'seller_ID'  => $seller_data['Seller_Id'],
			'Nombre' => $seller_data['Name'],
			'Apellido'   => $seller_data['LastName'],
		);
    $types = array( '%d', '%s', '%s' );
    global $wpdb;
    $gs_sellers_table = $wpdb->prefix . ('gs_sellers');

    if($wpdb->get_var("SHOW TABLES LIKE '$gs_sellers_table'") != $gs_sellers_table) {
       //table not in database. Create new table
       $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $gs_sellers_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
						seller_ID bigint(20) NOT NULL,
            Nombre varchar(120) NOT NULL,
            Apellido varchar(120) NOT NULL,
            PRIMARY KEY  (id)
       ) $charset_collate;";
       require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
       dbDelta( $sql );
    }
    $wpdb->insert($gs_sellers_table, $values, $types);

    update_user_meta(1,'seller_key_'.$seller_data['seller_Id'], $seller_data['seller_Id']);
	} else {
		/*$post = array(
			'ID'  => $IdWp,
			'post_author'  => 1,
			'post_content' => $seller_data['Description'],
			'post_status'  => 'publish',
			'post_title'   => $seller_data['Name'],
			'post_parent'  => '',
			'post_type'    => 'seller'
		);*/
		/*$post_id = wp_update_post($post);
		$seller_category = $seller_data['Category'];
		borrarVariacionesselleros($post_id);*/
		//if (sizeof($seller_data['Sucs']) < 0){
    }
}

?>
