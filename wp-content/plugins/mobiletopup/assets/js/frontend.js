jQuery(document).ready(function ($) {
	$('#mobiletopup_phone').on('propertychange input', function (e) {
		var input = $(this).val();			

		if (input.length === 1) {
			checkRegex(input);
		} else if (input.length > 1) {
			var firstInput = input.charAt(0);
			checkRegex(firstInput);
		}

		var x = $(this).val().replace(/\D/g, '')
			.match(/([689]{0,1})(\d{0,4})(\d{0,4})/);

		$(this).val(!x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : ''));

	});

	function checkRegex(input) {
		var regex = new RegExp("[689]");
		if (!regex.test(input)) {
			$('#mobiletopup_phone').val('');
			return;
		}
	}
});


//^[6].*$
// ^((?!(0))[0-9]{9})$
// document.addEventListener('DOMContentLoaded', function () {
// 	document.getElementById('mobiletopup_phone_regex')
// 	.addEventListener('input', function (e) {
// 		var input = e.target.value;

// 		if (input.length === 1) {
// 			var regex = new RegExp("[689]");
// 			if (!regex.test(input)) {
// 				e.target.value = '';
// 				return;
// 			}
// 		}

// 		var x = e.target.value.replace(/\D/g, '')
// 			.match(/([689]{0,1})(\d{0,4})(\d{0,4})/);

// 		e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '');

// 	});
// });