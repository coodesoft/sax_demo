<?php
/**LLAMADA AJAX**/
add_action('wp_ajax_get_sincronizar_precios', 'ajax_get_sincronizar_precios');
add_action('wp_ajax_nopriv_get_sincronizar_precios', 'ajax_get_sincronizar_precios');

function ajax_get_sincronizar_precios(){
	get_sincronizar_precios();
}

function get_sincronizar_precios(){
	$dirJsonPrecio = plugin_dir_path( __DIR__ ).'funcionalidades/listaPrecios.json';
	/**Obtener el archivo JSON**/
	//$url = $GLOBALS['cgv']['endpoint_lista_precios'];
	$commonurl = get_user_meta(1, 'url', true);
	$url = $commonurl.'/api/PriceList';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$url);
	$json = curl_exec($ch);
	if(curl_exec($ch) === false)
	{
		echo 'Curl error: ' . curl_error($ch);
	}
	else
	{
		//echo 'Operación completada sin errores';
	}
	//$listaPrecios = json_decode($json, true);
	curl_close($ch);
	file_put_contents($dirJsonPrecio,$json);
	//actualizar_precios_bd($dirJsonPrecio);
}
/**************************/
function actualizar_precios_bd($dirJsonPrecio) {
	global $wpdb;
	//$dirJsonPrecio = plugin_dir_path( __DIR__ ).'funcionalidades/listaPrecios.json';
	$listasPrecios = file_get_contents($dirJsonPrecio);
	$listasPrecios = json_decode($listasPrecios,true);
	foreach($listasPrecios as $lista)
	{
		foreach($lista["Items"] as $items)
		{
			/**verificamos si existe el dato en la base de datos**/
			$sql = "SELECT 1 from ".$wpdb->prefix."lista_precios_gsax
					where lista = '".strtoupper($lista["Name"])."'
					and product_id = '".strtoupper($items["Product_Id"])."'
					and poductvariation_id = '".strtoupper($items["ProductVariation_Id"])."'
					LIMIT 1";
			$result = $wpdb->get_results($sql);
			if(count($result)){
				/*Si existe, se actualiza*/
				$sql = "UPDATE ".$wpdb->prefix."lista_precios_gsax SET
						price = '".strtoupper($items["Price"])."',
						fechavigencia = '".strtoupper($items["FechaVigencia"])."',
						fechahasta = '".strtoupper($items["FechaHasta"])."'
						WHERE lista = '".strtoupper($lista["Name"])."'
						AND product_id = '".strtoupper($items["Product_Id"])."'
						AND poductvariation_id = '".strtoupper($items["ProductVariation_Id"])."' LIMIT 1";
			}else{
				/*Si no existe, se inserta por primera vez*/
				$sql = "INSERT INTO ".$wpdb->prefix."lista_precios_gsax
						(`id`,`lista`,`product_id`,`poductvariation_id`,`price`,`fechavigencia`,`fechahasta`)
						VALUES (
							NULL,
							'".strtoupper($lista["Name"])."',
							'".strtoupper($items["Product_Id"])."',
							'".strtoupper($items["ProductVariation_Id"])."',
							'".strtoupper($items["Price"])."',
							'".strtoupper($items["FechaVigencia"])."',
							'".strtoupper($items["FechaHasta"])."'
						);";
			}
			$result = $wpdb->get_results($sql);
		}
	}
}
/**************************/

function opal_varient_price( $price, $variation ) {
	$listasUsuario = trim(str_replace(';', ',', get_user_meta(get_current_user_id(),"lista_precios",true)),",");
	$listas = explode(",",$listasUsuario);
	$listasUsuario = '';
	foreach ($listas as $lista) {
		$listasUsuario = $listasUsuario."'".$lista."',";
	}
	$listasUsuario = trim($listasUsuario,",");
	global $wpdb;
	$sql = "SELECT price FROM  ".$wpdb->prefix."lista_precios_gsax where
			`poductvariation_id` LIKE '".$variation->sku."'
			AND price > 0
			AND lista in (".$listasUsuario.")
			order by price asc
			LIMIT 1";
	$result = $wpdb->get_results($sql);
	if(count($result) > 0){
		return $result[0]->price;
	}
	else{
		return 0;
	}
	return 0;
}
//add_filter( 'woocommerce_product_variation_get_regular_price', 'opal_varient_price' , 99, 2 );
//add_filter( 'woocommerce_product_variation_get_sale_price', 'opal_varient_price' , 99, 2 );
add_filter( 'woocommerce_product_variation_get_price', 'opal_varient_price', 99, 2 );
?>
