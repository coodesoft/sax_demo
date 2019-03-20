<?php
//require_once(ABSPATH . '/wp-content/plugins/woo-variations-table/woo-variations-table.php');
add_shortcode( 'gbs_catalog', 'gbs_catalog');
function gbs_get_categories(){
  $orderby = 'name';
  $order = 'asc';
  $hide_empty = false ;
  $cat_args = array(
      'orderby'    => $orderby,
      'order'      => $order,
      'hide_empty' => $hide_empty,
  );
  $product_categories = get_terms( 'product_cat', $cat_args );
  $categories = [];
  foreach ($product_categories as $key => $category) {
      if(!($category->term_id ==149)){
    $categories[$key]['name'] = $category->name;
    $categories[$key]['id'] = $category->term_id;

      }
  }
  return $categories;
}
add_action( 'wp_ajax_nopriv_gbs_get_products_by_category', 'gbs_get_products_by_category' );
add_action( 'wp_ajax_gbs_get_products_by_category', 'gbs_get_products_by_category' );

function gbs_get_products_by_category(){
  $params = array();
  parse_str($_POST['data'], $params);
  $args = [
    'post_type' => 'product',
    'orderby'   => 'title',
    'status' => 'publish',
    'posts_per_page' => -1,
    'tax_query' => [
        [
          'taxonomy'  => 'product_cat',
    			'field'     => 'id',
    			'terms'     => $params['Category']
        ]
    ]
  ];
  $products = new WP_Query($args);
  $cartItems = gbsBuildProductsCartArray(WC()->cart->get_cart());
  echo gbs_products_list($products, $cartItems);
  wp_die();
}
add_action( 'wp_ajax_nopriv_gbs_load_variations', 'gbs_load_variations' );
add_action( 'wp_ajax_gbs_load_variations', 'gbs_load_variations' );

function gbs_load_variations(){

  $id = $_POST['product_id'];

  $factory = new WC_Product_Factory();
  $product = $factory->get_product($id);

  $cartItems = gbsBuildCartItemsArray(WC()->cart->get_cart());

  //echo json_encode(WC()->cart->get_cart()); wp_die()

  if ( $product->is_type('variable') ){
    $args = array(
    	'post_type'     => 'product_variation',
    	'post_status'   => array( 'publish' ),
    	'numberposts'   => -1,
    	'orderby'       => 'menu_order',
    	'order'         => 'asc',
    	'post_parent'   => $id // get parent post-ID
    );
    $variations = get_posts( $args );

    $varMeta = gbsBuildVariationArray($variations);
    //echo json_encode($varMeta);
    echo gbs_variation_table($varMeta, $id, $product->get_name(), $cartItems);
  } else{
    echo gbs_simple_product_table($product->get_id(), $cartItems);
  }

  wp_die();
}
add_action( 'wp_ajax_nopriv_gbs_add_variations_to_cart', 'gbs_add_variations_to_cart' );
add_action( 'wp_ajax_gbs_add_variations_to_cart', 'gbs_add_variations_to_cart' );

function gbs_add_variations_to_cart(){
  $variations = array();
  parse_str($_POST['data'], $variations);

  $safe_parent = intval($variations['Product']);
  //echo json_encode($variations); throw new \Exception("Error Processing Request", 1);


  if ($variations['productType'] == 'simple'){
    $keys = array_keys($variations['Product']);
    $safe_parent = $keys[0];
  }

  if ($safe_parent){
    if ($variations['productType'] == 'variable'){

      $parent_product = $variations['Product'];
      foreach ($variations['Variation'] as $id => $element) {
        $quantity = $element['qty'];

        if ($element['key']){
          if ($quantity != "" && $quantity>0)
            $result = WC()->cart->set_quantity($element['key'], $quantity);
          else
            $result = WC()->cart->remove_cart_item($element['key']);
        } else{
          if ($quantity != "" && $quantity>0)
            $result = WC()->cart->add_to_cart( $parent_product, $quantity, $id, wc_get_product_variation_attributes( $id ) );
        }
      }
    } else{
      $keys = array_keys($variations['Product']);
      $quantity = $variations['Product'][$keys[0]]['qty'];
      $key = $variations['Product'][$keys[0]]['key'];

      if ($key){
        if ($quantity != "" && $quantity>0)
          $result = WC()->cart->set_quantity($key, $quantity);
        else
          $result = WC()->cart->remove_cart_item($key);
      } else
        $result = WC()->cart->add_to_cart($keys[0], $quantity);
    }

    $counter = 0;
    $cartItems = WC()->cart->get_cart();
    $productObj = get_product($safe_parent);
    foreach ($cartItems as $key => $item) {
      if ($productObj->is_type('variable')){
        $variation_id = $item['variation_id'];
        $product_variation = new WC_Product_Variation($variation_id);
        $parent_sku = $product_variation->get_parent_data()['sku'];
        if ($productObj->get_sku() == $parent_sku){
          $counter = $counter + $item['quantity'];
        }
      } else{
        if ($productObj->get_id() == $item['product_id']){
          $counter = $counter + $item['quantity'];
        }
      }
    }



    if ($counter == 0)
      echo json_encode([
          'msg' => 'Se produjo un error al agregar al carrito',
          'variations-added' => '(Cantidad pedida: '.$counter.")",
        ]);
    else
    echo json_encode([
        'msg' => 'Se agregron las variaciones correctamente al carrito',
        'variations-added' => '(Cantidad pedida: '.$counter.")",
      ]);
  }
  wp_die();
}



/* FUNCIONES PARA REALIZAR UN PEDIDO */
add_action( 'wp_ajax_nopriv_gbs_create_order', 'gbs_create_order' );
add_action( 'wp_ajax_gbs_create_order', 'gbs_create_order' );
function gbs_create_order(){
  $user = wp_get_current_user();
  parse_str($_POST['data'], $values);
  $address = array(
		'first_name' => $user->user_firstname,
		'last_name'  => $user->user_lastname,
		'email'      => $user->user_lastname,
		'country'    => 'ARG'
	 );
  $order = wc_create_order();
  $cartItems = WC()->cart->get_cart();
  foreach ($cartItems as $key => $item) {
    $quantity = $item['quantity'];
    if ($item['variation_id']){
      $variation_id = $item['variation_id'];
      $variation_attrs = wc_get_product_variation_attributes($variation_id);
      $product_variation = new WC_Product_Variation($variation_id);
      $order->add_product( $product_variation, $quantity, $variation_attrs );
    } else{
      $product = wc_get_product( $item['data']->get_id() );
      $order->add_product( $product, $quantity );
    }
  }
	// Set payment gateway
	$payment_gateways = WC()->payment_gateways->payment_gateways();
  $order->set_payment_method( $payment_gateways['cod'] );
	// Calculate totals
	$order->calculate_totals();
	$status = $order->update_status('completed');
  $ws_json = gbs_biuld_ws_object($user->ID, $values, $order);
  $commonurl = get_user_meta(1, "url", true);
  $endpoint = $commonurl . "/api/Order";
  $send_data = array(
    'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
    'body'        => json_encode($ws_json)
    );
  $result = wp_remote_post($endpoint, $send_data);
  $values = array( 'cliente_id' => $user->ID,
                   'resultado' => $result,
                   'json' => $ws_json,
                   'tipo' => 'pedido');
  $types = array( '%d', '%s', '%s', '%s' );
  global $woocommerce;
  $woocommerce->cart->empty_cart();

  if ($status)
    echo json_encode(['status' => 'gbs-success', 'msg' => 'El pedido se ha realizado con éxito', 'WSResult' => $result, 'ws_json' => $ws_json]);
  else
    echo json_encode(['status' => 'gbs-error', 'msg' => 'Se ha producido un error al procesar el pedido', 'WSResult' => $result, 'ws_json' => $ws_json]);
    global $wpdb;
    $error_table = $wpdb->prefix . ('gs_error');
    $wpdb->insert($error_table, $values ,$types);
  wp_die();
}

function gbs_biuld_ws_object($user_id, $adicionales, $order){
  $detalle = [];
  $data = [];
  $tmp = [];
  foreach ($order->get_items() as $item_id => $item) {
    $data['Order_id'] = $order->get_order_number();
    //$tmp[] = [$item->get_product_id(), $item->get_variation_id()];
    //print_r($item);wp_die();
    $product = $item->get_product();
    if ( $item->get_variation_id() ){
      $data['Product_Id'] = $product->get_parent_data()['sku'];
      $data['ProductVariation_Id'] = $product->get_sku();
    } else{
      $data['Product_Id'] = $product->get_sku();
      $data['ProductVariation_Id'] = '';
      $data['ProductVariation_Id'] = $data['Product_Id'];
    }
    $data['Quantity'] = $item['qty'];
    $detalle[] = $data;
  };

  global $wpdb;
  $gs_clients_table = $wpdb->prefix . ('gs_clients');
  $select_seller_id = "SELECT seller_id FROM ". $gs_clients_table . " WHERE Client_ID = " . $adicionales['cliente_id'];
  $seller_id = $wpdb->get_var($select_seller_id);
   $params = [
     'Order_id' => $order->get_order_number(),
     'Client_ID' => $adicionales['cliente_id'],
     'UserID' => $user_id,
     'Fecha_emision' => date('d/m/Y'),
     'Seller_id' => $seller_id,
     'UserAction' => $adicionales['pedido'],
     'Detail' =>  $detalle ,
   ];
  if ($adicionales['sucursal'] !='gbs_noSucursal')
    $params['SucName'] = $adicionales['sucursal'];
  return $params;
}


add_action('wp_ajax_get_do_checkout', 'get_do_checkout');
add_action('wp_ajax_nopriv_get_do_checkout', 'get_do_checkout');
function get_do_checkout(){
     $sucursales = get_user_meta(($_POST['user']),'id_sucursal', false);
				if (sizeof($sucursales)>= 1) { ?>
				<div id="sucursalSelection cuatrocol">
		            <div>Seleccione la sucursal:</div>
		            <div id="sucursalesList">
		              <select name="sucursal" required>
		                  <option value="" disabled selected>Seleccione una sucursal</option>
    								  <?php	foreach ($sucursales as $key => $sucursal) { ?>
											<option value="<?php echo $sucursal?>"><?php echo $sucursal?></option>
										  <?php }?>
		              </select>
		            </div>
		            </div>
				<?php } else{ ?>
					<input type="hidden" name="sucursal" value="gbs_noSucursal">
				<?php }
				wp_die();
}



/* FUNCIONES INTERNAS DE RETORNO */
function gbs_catalog(){
  $categories = gbs_get_categories();
  ?>
  <div id="gbsCatalog">

    <div id="selectCategoryForm">
      <label for="product_cat_selection">Seleccione una categoría</label>
      <select name="Category" id="product_cat_selection">
          <option value="">Categoría</option>
        <?php foreach ($categories as $key => $cat) { ?>
          <option value="<?php echo $cat['id']?>"><?php echo $cat['name']?></option>
        <?php } ?>
      </select>
      <a href= "<?php echo home_url('/carrito') ?>" class="wpcf7-form-control wpcf7-submit submit_button" style="display: inline-block; padding: 10px 10px !important; width:116px" >Revisar pedido</a>
    </div>
    <div id="gbs_productos_list"></div>
  </div>
<?php }

function gbs_products_list($products, $cartItems){ ?>

  <ul class="products products-3">
    <?php while ($products->have_posts()) : ?>
      <?php $products->the_post();
            $product = get_product( $products->post->ID );
      ?>
      <li class="product-<?php echo $product->get_id() ?> product product-type" data-product="<?php echo $product->get_id() ?>">
        <div class="product-description">
          	<div class="product-details-container">
              <h3 class="product-title" data-fontsize="16" data-lineheight="24"><?php echo substr($product->get_name(), 2) ?></h3>
              <?php $p_id = $product->get_id();
              if ( isset($cartItems[$p_id]) && $cartItems[$p_id]>0 ){ ?>
                <span class="qty" data-fontsize="13">(Cantidad pedida: <?php echo $cartItems[$p_id]; ?> ) </span>
              <?php } else { ?>
                <span class="qty" data-fontsize="13"></span>
              <?php } ?>
        	  </div>
        </div>
        <div id="variation-<?php echo $product->get_id() ?>" class="product-variations"> </div>
      </li>
    <?php endwhile;
        wp_reset_query();
    ?>
  </ul>

<?php }

function gbs_variation_table($varMeta, $parentId, $parentName, $inverseCartItems){
  $talles = $varMeta['talles'];
  $variations = $varMeta['variations'];
  ?>
  <form id="gbsAddVariationToCartForm">
    <input type="hidden" name="Product" value="<?php echo $parentId ?>">
    <table>
      <tr>
        <th></th>
        <?php foreach ($talles as $index => $element): ?>
        <th data-order="<?php echo $element['order']?>"><?php echo $element['talle'] ?></th>
        <?php endforeach; ?>
      </tr>

      <?php foreach ($variations as $color => $meta): ?>
      <tr>
        <td class="gbs_tag"><?php echo strtoupper($color)?></td>
        <?php foreach ($meta as $id => $Variation): ?>

        <?php
          $key = isset($inverseCartItems[$Variation['id']]) ? $inverseCartItems[$Variation['id']] : 0;
          $value = $key ? WC()->cart->get_cart_item($key)['quantity'] : 0;
        ?>
        <td class="gbs_data" data-order="<?php echo $Variation['order'] ?>" data-color="<?php echo $color ?>" data-talle="<?php echo $Variation['talle'] ?>">
          <input step="2" min="0" type="number" name="Variation[<?php echo $Variation['id'] ?>][qty]" data-variation="<?php echo $Variation['id'] ?>" value="<?php echo $value ?>">
          <input type="hidden" name="Variation[<?php echo $Variation['id'] ?>][key]" value="<?php echo $key?>">
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </table>
    <input type="hidden" name="productType" value="variable">
    <button id="gbsAddVariationToCartButton" class="wpcf7-form-control wpcf7-submit submit_button" style="padding: 10px 10px !important">Agregar al carrito</button>
  </form>
<?php }

function gbs_simple_product_table($idProduct, $inverseCartItems){ ?>
  <?php
    $key = isset($inverseCartItems[$idProduct]) ? $inverseCartItems[$idProduct] : 0;
    $value = $key ? WC()->cart->get_cart_item($key)['quantity'] : 0;
  ?>
  <form id="gbsAddVariationToCartForm">
    <table class="product-simple-table">
      <tr>
        <td class="gbs_data"><input  type="number" step="2" name="Product[<?php echo $idProduct?>][qty]" value="<?php echo $value ?>"></td>
        <input type="hidden" name="Product[<?php echo $idProduct ?>][key]" value="<?php echo $key?>">

      </tr>
    </table>
    <input type="hidden" name="productType" value="simple">
    <button id="gbsAddVariationToCartButton" class="wpcf7-form-control wpcf7-submit submit_button" style="padding: 10px 10px !important">Agregar al carrito</button>
  </form>

<?php }

/* FUNCIONES ESTRUCTURALES */
function gbsBuildVariationArray($variations){
  $arrVariations = [];
  $orders = [];
  foreach ($variations as $key => $variation) {
    $metadata = get_post_meta($variation->ID);
    $talle = $metadata['attribute_pa_talle'][0];
    $color = $metadata['attribute_pa_color'][0];
    $order = $metadata['attribute_pa_order'][0];
    $talles[$talle] = ['talle' => $talle, 'order' => $order];
    $arrVariations[$color][] = ['talle' => $talle, 'id' => $variation->ID, 'order' => $order];
  }

  foreach ($arrVariations as $key => $value) {
    $arrVariations[$key] = sortByOrderAttr($value);
  }

  $varMeta['talles'] = sortByOrderAttr($talles);
  $varMeta['variations'] = $arrVariations;
  return $varMeta;
}

function sortByOrderAttr($collection){
  usort($collection, function($a, $b){
      if ($a['order'] == $b['order'])
        return 0;

      return ($a['order'] < $b['order']) ? -1 : 1;
  });
  return $collection;
}

function gbsBuildCartItemsArray($cartItems){
  $array = [];
  foreach ($cartItems as $key => $value) {
    $id = $value['variation_id'] ? $value['variation_id'] : $value['product_id'];
    $array[$id] = $key;
  }

  return $array;
}

function gbsBuildProductsCartArray($cartItems){
  $array = [];

  foreach ($cartItems as $key => $value) {
    $id = $value['product_id'];

    if ( !isset($array[$id]) )
      $array[$id] = 0;

    $array[$id] = $array[$id] + $value['quantity'];
  }
  return $array;
}
