/**
 * trg_frontend.js
 *
 * @package Total_Recipe_Generator_El
 * @version 3.1.0
 */
jQuery(function ($) {
    'use strict';
    $(window).on('load', function () {
		var share_img = $('img.trg-img').attr('src'),
		    recipe_summary = $('.recipe-summary').text();
		// Modify OG tags dynamically
		if(share_img) {
			$("meta[property='og:image']").attr('content', share_img);
		}
		if(recipe_summary) {
			$("meta[property='og:description']").attr('content', recipe_summary);
		}
        // Print button
		$('#trg-print-btn').on('click', function (e) {
            e.preventDefault();
            var data = $('.trg-recipe').html();
            trg_print_recipe(data);
        });
    });

	function trg_print_recipe(data) {
		var win = window.open('', 'Print', 'height=400,width=600'),
		css_url = trg_localize.plugins_url + '/assets/css/trg_print.css';
		win.document.write('<html><head><title></title>');
		win.document.write('<link rel="stylesheet" href="' + css_url + '" type="text/css" media="all" />');
		win.document.write('</head><body >');
		win.document.write(trg_localize.prnt_header);
		win.document.write(data);
		win.document.write(trg_localize.prnt_footer);
		win.document.write('</body></html>');
		win.document.close();
		win.focus();
		setTimeout(function(){win.print();},1000);
		//win.close();
		return true;
	}

	function trg_el_share_window() {
        $('.trg-sharing li:not(.no-popup,.trg-print) a').on('click', function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            window.open(href, '_blank', 'width=600,height=400,menubar=0,resizable=1,scrollbars=0,status=1', true);
        });
    }
    trg_el_share_window();
});