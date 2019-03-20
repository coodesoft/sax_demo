<?php
define('GLOBALSAX_CORE','globalsax-core');

function theme_settings_page()
{
  global $wpdb;
  $error_table = $wpdb->prefix . ('gs_error');
  if($wpdb->get_var("SHOW TABLES LIKE '$error_table'") != $error_table) {
     //table not in database. Create new table
     $charset_collate = $wpdb->get_charset_collate();
      $sql = "CREATE TABLE $error_table (
          id bigint(20) NOT NULL AUTO_INCREMENT,
          cliente_id bigint(20) NOT NULL,
          resultado varchar(120) NOT NULL,
          json varchar(120) NOT NULL,
          tipo varchar(120) NOT NULL,
          PRIMARY KEY  (id)
     ) $charset_collate;";
     require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
     dbDelta( $sql );
  }
  $screen =  get_current_screen();
	$pluginPageUID = $screen->parent_file;
    ?>
    <div class="wrap">
        <h1 class="panel-title">GLOBALSAX - CORE</h1>
          <h2 class="nav-tab-wrapper">
          <a href="<?= admin_url('admin.php?page='.$pluginPageUID.'&tab=sincronizar')?>" class="nav-tab">Sincronizar</a>
          <a href="<?= admin_url('admin.php?page='.$pluginPageUID.'&tab=assignClient')?>" class="nav-tab">Asignar clientes</a>
          <a href="<?= admin_url('admin.php?page='.$pluginPageUID.'&tab=assignSeller')?>" class="nav-tab">Asignar Seller</a>
          <a href="<?= admin_url('admin.php?page='.$pluginPageUID.'&tab=adminUrl')?>" class="nav-tab">Administrar URL</a>
          <a href="<?= admin_url('admin.php?page='.$pluginPageUID.'&tab=errorTab')?>" class="nav-tab">Listado de errores</a>
        </h2>

      <div class="panel-body">
  			<?php $activeTab = $_GET['tab']; ?>

  			<?php if (!isset($activeTab)){ ?>
        	<div id="gs-tab"><?php settings(); ?></div>
  			<?php } ?>

  			<?php if ($activeTab == 'sincronizar'){ ?>
  				<div class="gs-tab" ><?php	settings(); ?></div>
  			<?php } ?>

        <?php if ($activeTab == 'assignClient'){ ?>
  				<div class="gs-tab" id="editClientRel"><?php	assignClient(); ?></div>
  			<?php } ?>

        <?php if ($activeTab == 'assignSeller'){ ?>
  				<div class="gs-tab" id="editSeller"><?php assignSeller(); ?></div>
  			<?php } ?>

        <?php if ($activeTab == 'adminUrl'){ ?>
  				<div class="gs-tab" id="adminUrl"><?php adminUrl(); ?></div>
  			<?php } ?>

        <?php  if ($activeTab == 'errorTab'){ ?>
  				<div class="gs-tab" id="errorTab"><?php errorTab(); ?></div>
  			<?php } ?>

		</div>
	<?php
}
function settings(){
  ?>
  <form method="post" action="options.php">
   <?php
       settings_fields("section");
       do_settings_sections("theme-options");
       submit_button();
   ?>
</form>
<?php
}
function display_opcion_sincronizar_productos()
{

	?>

		<input type="button" name="sincronizar_productos" value="Sincronizar productos" onclick="sincronizarProductos()"/>
    <input type="button" name="delete_product_key" value="Borrar key de productos" onclick="deleteProductKey()"/>

    <script>

      function sincronizarProductos(){
        jQuery.ajax({
          type : "post",
          url : "<?php echo home_url('/wp-admin/admin-ajax.php'); ?>",
          data : 'action=get_sincronizar_producto&security=<?php echo wp_create_nonce('globalsax'); ?>',
          success: function( response ) {
            console.log(response);
            //location.reload();
        },
        error: function() {
          console.log('error');
        }
        });
      }
      function deleteProductKey(){
        jQuery.ajax({
          type : "post",
          url : "<?php echo home_url('/wp-admin/admin-ajax.php'); ?>",
          data : 'action=delete_product_key&security=<?php echo wp_create_nonce('globalsax'); ?>',
          success: function( response ) {
            console.log(response);
            //location.reload();
        },
        error: function() {
          console.log('error');
        }
        });
      }
    </script>


	<?php
}

function display_opcion_sincronizar_clientes() {
  ?>

		<input type="button" name="sincronizar_clientes" value="Sincronizar clientes" onclick="sincronizarClientes()"/>
    <script>

      function sincronizarClientes(){

        jQuery.ajax({
          type : "post",
          url : "<?php echo home_url('/wp-admin/admin-ajax.php'); ?>",
          data : 'action=get_sincronizar_cliente&security=<?php echo wp_create_nonce('globalsax'); ?>',
          success: function( response ) {
            console.log(response);
            //location.reload();
        },
        error: function( data ) {
          console.log(data);
        }
        });
      }
    </script>
	<?php

}
function display_opcion_sincronizar_vendedores() {
  ?>
  <input type="button" name="sincronizar_vendedores" value="Sincronizar vendedores" onclick="sincronizarVendedores()"/>
  <script>

    function sincronizarVendedores(){

      jQuery.ajax({
        type : "post",
        url : "<?php echo home_url('/wp-admin/admin-ajax.php'); ?>",
        data : 'action=get_sincronizar_vendedor&security=<?php echo wp_create_nonce('globalsax'); ?>',
        success: function( response ) {
          console.log(response);
          //location.reload();
      },
      error: function( data ) {
        console.log(data);
      }
      });
    }
  </script>
<?php
}

function display_opcion_administrar_url() {
  $commonurl = get_user_meta(1, "url", true);
  echo "La URL actual es: " . $commonurl;
}
function display_opcion_ver_errores() {
  echo "Abrir la solapa para poder revisar los pedidos y las sincronizaciones erroneas";
  }
function display_theme_panel_fields()
{
	add_settings_section("section", "Configuracion de opciones de sistema", null, "theme-options");
	/**/
  add_settings_field("productos", "Sincronizar lista de productos de todos los productos! - Es un proceso lento y que genera mucho estress a la base de datos, por favor sincronizar cuando este seguro que se debe hacer.", "display_opcion_sincronizar_productos","theme-options", "section");
	register_setting("section", "productos");
	add_settings_field("clientes", "1) Sincronizar lista de clientes", "display_opcion_sincronizar_clientes","theme-options", "section");
	register_setting("section", "clientes");
	add_settings_field("vendedores", "2) Sincronizar lista de vendedores", "display_opcion_sincronizar_vendedores","theme-options", "section");
	register_setting("section", "vendedores");
	add_settings_field("url", "3) Administrar url del WS", "display_opcion_administrar_url","theme-options", "section");
    register_setting("section", "url");
  add_settings_field("error", "4) Ver errores", "display_opcion_ver_errores","theme-options", "section");
    register_setting("section", "error");
	/**/
}

add_action("admin_init", "display_theme_panel_fields");
function get_Sellers(){

    global $wpdb;

    $gs_seller_table = $wpdb->prefix . ('gs_sellers');

    $query = 'SELECT * FROM ' . $gs_seller_table;

    return $wpdb->get_results($query);
}

function assignSeller(){
  $sellers = get_Sellers();
  ?>
  <table>
      <tr>
        <th>Seller ID</th>
        <th>Nombre</th>
        <th>Apellido</th>
      </tr>
      <tr>
        <?php foreach ($sellers as $key => $seller) { ?>
      <td><?php echo $seller->seller_ID; ?></td>
      <td><?php echo $seller->Nombre; ?></td>
      <td><?php echo $seller->Apellido; ?></td>
      </tr>
    <?php } ?>

  </table>
  <?php
}
function get_clients_user_table(){

  global $wpdb;

  $gs_client_table = $wpdb->prefix . ('clients_users_rel');

  $query = 'SELECT * FROM ' . $gs_client_table;

  return $wpdb->get_results($query);

}

function insert_GS_user($GS_client_id, $WP_user_id){

  global $wpdb;

  $gs_client_table = $wpdb->prefix . ('clients_users_rel');

  return $wpdb->insert($gs_client_table, array('user_id' => $WP_user_id, 'Client_ID'=> $GS_client_id), array('%d', '%d'));

}
function get_GS_clients(){

  global $wpdb;

  $gs_client_table = $wpdb->prefix . ('gs_clients');

  $query = "SELECT * FROM ". $gs_client_table;

  return $wpdb->get_results($query);

}

function get_GS_client($Client_ID){
  global $wpdb;

  $gs_client_table = $wpdb->prefix . ('gs_clients');

  $query = "SELECT * FROM " . $gs_client_table . " WHERE Client_ID = ".$Client_ID;

  return $wpdb->get_results($query, ARRAY_A);

}

function delete_GS_rel($rel_ID){
  global $wpdb;

  $gs_client_table = $wpdb->prefix . ('clients_users_rel');

  return $wpdb->delete($gs_client_table, array('rel_id' => $rel_ID ));
}

function assignClient(){
  if (!empty($_POST['client'])){
    if (!empty($_POST['user'])) {
      insert_GS_user($_POST['client'], $_POST['user']);
    }}
    if (!empty($_POST['borrar'])){
      delete_GS_rel($_POST['borrar']);
  }

?>

  <table>
      <tr>
        <th>Cliente</th>
        <th>Usuario Wordpress</th>
        <th>Borrar</th>
      </tr>
      <tr>
        <?php
        $users_assigned = array();
        $rel_clientes_usuarios = get_clients_user_table();
        foreach ($rel_clientes_usuarios as $key => $cliente_usuario) {
          $userByID = get_user_by('ID', $cliente_usuario->user_id);
          $gs_client = get_GS_client($cliente_usuario->client_id);
          ?>
          <form class="" method="post" >
            <td><?php echo $gs_client[0]["Name"]; ?></td>
            <td><?php echo $userByID->display_name; ?></td>
            <td><button type="submit" name="borrar" value="<?php echo $cliente_usuario->rel_id?>">Borrar</button></td>
        </form>
      </tr>
    <?php } ?>
  </table>
  <form class="" method="post">
    <div id="clientsList">
    <select name="client" id="client" required>
      <option value="" disabled selected>Seleccione un cliente</option>
      <?php
        $gs_clientes = get_GS_clients();

      foreach ($gs_clientes as $key => $cliente) {
        ?>
                <option value="<?php echo $cliente->Client_ID ?> "><?php echo $cliente->Name ?></option>
      <?php } ?>
  </select>
  <select name="user" id="user" required>
    <option value="" disabled selected>Seleccione un usuario de Wordpress</option>
    <?php
    $wp_users = get_users();
    foreach ($wp_users as $key => $wp_user) {
      ?>
              <option value="<?php echo $wp_user->ID?>"><?php echo $wp_user->display_name ?></option>
    <?php } ?>
  </select>
        <div class="gs-submit-button gs-text-center">
          <button type="submit" name="Sincronizar" value="Sincronizar">Sincronizar</button>
        </div>
    </div>
  </form>

<?php
}

function adminUrl(){
  if (!empty($_POST['url'])){
    $commonurl = $_POST['url'];
    update_user_meta(1, 'url', $commonurl);
  }
  $commonurl = get_user_meta(1,'url', true);
  ?>
  <form class="" method="post">
    <input type="Text" name="url" value="<?php echo $commonurl; ?>">
    <div class="gs-submit-button">
      <button type="submit" name="AceptarUrl" value="AceptarUrl">Aceptar</button>
    </div>
  </form>
  <?php
}

function errorTab(){
  global $wpdb;
  $error_table = $wpdb->prefix . ('gs_error');
  ?>
  <table>
      <tr>
        <th>Cliente</th>
        <th>Resultado</th>
        <th>Tipo de Petición</th>
        <th>Reenviar pedido (En desarrollo)</th>
      </tr>
      <tr>
        <?php
        $query = 'SELECT * FROM ' . $error_table;
        $errores = $wpdb->get_results($query);
        foreach ($errores as $key => $error) {
           $id_cliente = $error->cliente_id;
           $result = $error->resultado;
           $type = $error->tipo;
          ?>
            <td><?php echo $id_cliente; ?></td>
            <td><?php echo $result; ?></td>
            <td><?php echo $type; ?></td>
            <td> aca va el voton de reenviar </td>
      </tr>
    <?php } ?>
  </table>
  <?php
}
?>
