<?php
$cantidadImportar = get_user_meta(1,"importar_clients",true);
$totalImportar = get_user_meta(1,"total_importar_clients",true);
//echo 'Cantidad total: '.$cantidadImportar;
/**LLAMADA AJAX**/
add_action('wp_ajax_get_sincronizar_cliente', 'ajax_get_sincronizar_cliente');
add_action('wp_ajax_nopriv_get_sincronizar_cliente', 'ajax_get_sincronizar_cliente');

/*function console_log( $data ){
  echo '<script>';
  echo 'console.log('. json_encode( $data ) .')';
  echo '</script>';
}*/

function ajax_get_sincronizar_cliente(){
	update_user_meta(1,'importar_clients',-99);
	echo get_sincronizar_cliente();
}


function get_sincronizar_cliente(){

	$cantidadImportar = get_user_meta(1,"importar_clients",true);
	if($cantidadImportar == -99 || $cantidadImportar > 0){
		/**Obtener el archivo JSON**/
		$commonurl = get_user_meta(1, 'url', true);
		$url = $commonurl . '/api/Client';

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

		$clients = json_decode($json, true);

		$amount = (int) sizeof($clients);

        update_user_meta(1,'total_importar_clients',$amount);

		/**Crear los atributos si no existen**/

		/*create_att($clients);

		/**Insertar los productos**/

		insert_clients($clients);

		/**********************************************************************************************************************/

		//exit;

	} else {

		echo 'No se ha invocado ninguna llamada - get_sincronizar_producto';

	}

}

function insert_clients ($clients)
{
  if (!empty($clients)) // No point proceeding if there are no products
    {
      //array_map('insert_product', $products);
		$comienzoImportado = get_user_meta(1,"importar_clients",true);
		if($comienzoImportado == -99) $comienzoImportado = 0;
		$cantidadFija = sizeof($clients);
		$finImportado = $comienzoImportado + $cantidadFija;
		$i = 0;
		foreach ($clients as $client_data) // Go through each attribute
		{
			if($i >= $comienzoImportado and $i<= $finImportado){
				insert_client($client_data);
			}
			$i++;
			if($i == $finImportado){break;}
		}
		$totalImportar = get_user_meta(1,"total_importar_clients",true);
		if($finImportado >=  $totalImportar){
			update_user_meta(1,'importar_clients',0);
			update_user_meta(1,'total_importar_clients',0);
		}else{
			update_user_meta(1,'importar_clients',$finImportado);
		}
    }
}

/**/

function insert_client($client_data)
{
	$IdWp = get_user_meta(1,'client_key_'.$client_data['Client_ID'], true);
	if (empty($IdWp)){
		$values = array(
 			'Client_ID'  => $client_data['Client_ID'],
			'Name' => $client_data['Name'],
			'PriceList'   => implode(",",$client_data['PriceList']),
			'Seller_id'  => $client_data['Seller_id'],
			'EnterpriseGroup' => $client_data['EnterpriseGroup']
		);
    $types = array( '%d', '%s', '%s', '%d', '%s' );
    global $wpdb;
    $gs_clients_table = $wpdb->prefix . ('gs_clients');

    if($wpdb->get_var("SHOW TABLES LIKE '$gs_clients_table'") != $gs_clients_table) {
       //table not in database. Create new table
       $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $gs_clients_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            Client_ID bigint(20) NOT NULL,
            Name varchar(120) NOT NULL,
            PriceList varchar(120) NOT NULL,
            Seller_id	bigint(20) NOT NULL,
            EnterpriseGroup varchar(20),
            user_id bigint(20),
            PRIMARY KEY  (id)
       ) $charset_collate;";
       require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
       dbDelta( $sql );
    }
    $wpdb->insert($gs_clients_table, $values, $types);

    if (sizeof($client_data['Sucs']) > 0) {
		insert_client_suc($client_data['Client_ID'], $client_data['Sucs']);
    }
		update_user_meta(1,'client_key_'.$client_data['Client_ID'], $client_data['Client_ID']);
	} else {
		/*$post = array(
			'ID'  => $IdWp,
			'post_author'  => 1,
			'post_content' => $product_data['Description'],
			'post_status'  => 'publish',
			'post_title'   => $product_data['Name'],
			'post_parent'  => '',
			'post_type'    => 'product'
		);*/
		/*$post_id = wp_update_post($post);
		$product_category = $product_data['Category'];
		borrarVariacionesProductos($post_id);*/
		//if (sizeof($client_data['Sucs']) < 0){
    }
}

function insert_client_suc($client_id, $sucursales){
  foreach ($sucursales as $key => $suc) {
    add_user_meta($client_id, 'id_sucursal', $suc['SucName']);
  }
}
?>
