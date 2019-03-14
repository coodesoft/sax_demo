<?

add_action( 'woocommerce_after_cart_table', 'gbs_cartFunctions' );


function gbs_cartFunctions(){ ?>
  <div class="cart-actions">
    <div class="action-elements">
      <a href= "<?php echo home_url('/catalogo') ?>" class="wpcf7-form-control wpcf7-submit submit_button" style="display: inline-block; padding: 10px 10px !important; width:116px; background-color:#802754!important" >Ver Catalogo</a>
      <a onclick="return VaciarCarrito()" class="del_button"><img src="/demo/img/basura.svg"></a>
      <a id="gbsEnviarPedido" class="wpcf7-form-control wpcf7-submit submit_button" style="height:40px;width:fit-content;" >Realizar Pedido</a>
    </div>
  </div>
    <script>
      function VaciarCarrito(){
         var data = {
           'action' : 'gbs_vaciar_carrito',
         };
         jQuery.post(ajaxurl, data, function(response){
           if (response)
             window.location = '<?php echo esc_url(get_permalink(1171)) ?>';
         });
      }
      function EnviarPedido() {
       	var data = {
       		'action': 'get_enviar_pedido',// We pass php values differently!
       	};
        jQuery.post(ajaxurl, data, function(response){
          if (response)
            alert('El pedido se ha realizado con exito: ' + response);
        });

       	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
    //   	jQuery.post('".admin_url( 'admin-ajax.php' )."', data, function(response) {
       		//
       		//alert('El pedido se ha realizado con exito');
       	//	if(alert('El pedido se ha realizado con exito!')){}
        //       else    window.location = '".esc_url( get_permalink(11963) )."';
    //   	  })
        }
    </script>
  </div>

<?php }




/**LLAMADA AJAX**/
add_action('wp_ajax_get_enviar_pedido', 'ajax_get_enviar_pedido');
add_action('wp_ajax_nopriv_get_enviar_pedido', 'ajax_get_enviar_pedido');

add_action('wp_ajax_gbs_vaciar_carrito', 'ajax_gbs_vaciar_carrito');
add_action('wp_ajax_nopriv_gbs_vaciar_carrito', 'ajax_gbs_vaciar_carrito');


function ajax_get_enviar_pedido(){
	global $woocommerce;
	$woocommerce->cart->empty_cart();
}

function ajax_gbs_vaciar_carrito(){
  global $woocommerce;
  if ( WC()->cart->get_cart_contents_count() != 0 )
	   $woocommerce->cart->empty_cart();

  return true;
}


/**Eliminar boton de finalizar compra***/
remove_action( 'woocommerce_proceed_to_checkout','woocommerce_button_proceed_to_checkout', 20);
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );

?>
