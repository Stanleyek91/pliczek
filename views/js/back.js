/**
*  @author    Prestapro
*  @copyright Prestapro
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*
*/

$(document).ready(function() {
	if ($('input[name=only_show]:checked').val() == '1') {
		$('input[name=enable_links]').prop('disabled', false);
	}

	$('label[for=only_show_on]').click(function() {
		$('input[name=enable_links]').prop('disabled', false);
	});

	$('label[for=only_show_off]').click(function() {
		$('input[name=enable_links]').prop('disabled', true);
	});

	$('ul.nav.nav-pills').prepend('<li class="li-docs"></li>');
	$('#module-documentation').prependTo('.li-docs').removeClass('hidden');
});
