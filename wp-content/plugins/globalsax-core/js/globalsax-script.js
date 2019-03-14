(function($){

  var loadUserContentCallback = function(form, action, target, callback){
    var data = {
      'data': $(form).serialize(),
      'action': action,
    }
    $.post(ajaxurl, data, function(data){
        $(target).html(data);
        $('body').removeClass('gbs-progress');

        if (callback != undefined)
          callback(data);
    });
  }

  var loadVariationTable = function(id, self){
    var data = {
      'product_id': id,
      'action': 'gbs_load_variations',
    }
    $.post(ajaxurl, data, function(data){

        // se limpia el contenido previamente cargado y el estilo.
        $('.product-variations').empty();
        $('.product-description').removeClass('active');
        $('.product-type').css('margin-bottom', "");
        $('#variation-'+id).removeClass('visible');
        // se agrega la variaci贸n actualmente solicitada
        $('#variation-'+id).html(data);
        $('#variation-'+id).addClass('visible');
        $('.gbs_data input').first().focus();

        //se setea el estilo del actual producto y su variaci贸n
        $(self).addClass('active');

        let variationHeight = $('#variation-'+id).height() + 10;
        let productContainerWidht = $('#gbs_productos_list .products').width() - 20;
        let offset = $(self).parent().position().left;


        $(self).parent().css('margin-bottom', variationHeight);
        $('#variation-'+id).css('width', productContainerWidht);
        $('#variation-'+id).css('left', -offset);


        $('body').removeClass('gbs-progress');
    });
  }

  var sendContent = function(form, action, target, callback){
    var data = {
      'data': $(form).serialize(),
      'action': action,
    }
    $.post(ajaxurl, data, function(data){
      data = JSON.parse(data);

      if (target instanceof jQuery)
        target.html(data['variations-added']);
      else
        $(target).html(data['variations-added']);

      $('body').removeClass('gbs-progress');
      if (callback != undefined)
        callback(data);
    });
  }

  var enviarPedido = function(){
    let selectCliente = $('#clientesList select').length;
    let clienteSerialize = $('#clientesList select').serialize();

    if ( !selectCliente || (selectCliente && clienteSerialize.length)){
      $('body').addClass('gbs-progress');

      var data = {
        'action': 'gbs_create_order',
        'data' : $('form.gbs-cart-form').serialize(),
      }
      $.post(ajaxurl, data, function(data){
        $('body').removeClass('gbs-progress');
        data = JSON.parse(data);
        let response = '<p class="cart-response '+data['status']+'">'+data['msg']+'</p>';
        $('#gbsCheckout').html(response);
      });
    } else{
      alert('Debe seleccionar una Razón Social');
    }
  }



  $(document).ready(function(){
    let rootCatalog = '#gbsCatalog';
    let rootCartForm = '.gbs-cart-form';

    $(rootCatalog).off().on('change', '#selectCategoryForm select', function(){
      $('body').addClass('gbs-progress');
      loadUserContentCallback(this, 'gbs_get_products_by_category', '#gbs_productos_list');
    });

    $(rootCatalog).on('click', 'li.product .product-description', function(){
      $('body').addClass('gbs-progress');

      let id = $(this).parent().data('product');
      loadVariationTable(id, this);
    });

    $(rootCatalog).on('click', 'span.gbs-close', function(){
      $(this).closest('.gbs-dialog').removeClass('active');
      $(this).closest('.gbs-dialog').find('.body').empty();
    });

    $(rootCatalog).on('submit', '#gbsAddVariationToCartForm', function(e){
      e.preventDefault();
      e.stopPropagation();
      $('body').addClass('gbs-progress');
      let self = this;
      let target = $(this).closest('.product-type').find('span.qty');
      sendContent(this, 'gbs_add_variations_to_cart', target, function(data){
        let parent = $(self).closest('.product-variations');
        parent.empty();
        parent.removeClass('visible');
        parent.css('width', "");
        parent.css('left', "");

        let producto = parent.closest('.product-type');
        producto.css('margin-bottom', "");

      });
    });

    $(rootCartForm).on('click', '#gbsEnviarPedido', function(){
      enviarPedido();
    })
  });


    $('#clientesList').on('change', '#cliente_id', function(){
		var data = {
	    'user' : this.value,
	    'action' : 'get_do_checkout',

	};

	$.post(ajaxurl, data, function(response){
	    $(".target").html(response);
	});
	});




})(jQuery);
