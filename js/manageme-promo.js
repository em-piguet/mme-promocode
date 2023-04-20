/**
 * Validation du code promo via API manageme
 * Author : Wonderweb
 */

     (function( $ ) {

    "use strict";

    $(document).ready(function() {

		var base_url =  'https://www.manage-me.pro/api/society/';

		$(".mm-promo-form").submit(function(event) {
			var $spinner = $(this).find('.mm-promo-spinner');
			var id = $(this).data('society');
			event.preventDefault();
			event.stopImmediatePropagation();
			var promo = $(this).find('.mm-promo-field').val(); //'P8SN98NJ47';
			if(promo.length !== 0){
				var api_url = base_url + id + '/promocode/' + promo ;
				$spinner.show();
				$.ajax({
					url : api_url,
					type: "GET",
					success : function(){
					},
					complete : function(data,statusText){
						$spinner.hide();
						message(data.responseJSON,statusText);
					}
				});
			}
		});

		function message(result,status){
			if(status === 'error') {
				$(".mm-promo-info").html(result.Message);
			}
			if( result.IsActive === false){
				$(".mm-promo-info").html(result.Exceptions[0]);
			}
			if( result.IsActive === true ){
				$(".mm-promo-info").html( '<a href="https://www.manage-me.pro' + result.Url + '">Aller au panier</a>');
			}
		}
	});

})(jQuery);
