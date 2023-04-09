// assets/chindevs.js
jQuery(document).ready(function($) {
	els = document.getElementsByClassName("form-group");
	var countryEl;
	var stateEl;

	for(let node of els) {
		if (node.childNodes[0].textContent == "Country") {
			countryEl = node.childNodes[4];
			countryEl.id = "country-dropdown";
			console.log("country");
			console.log(countryEl);
		}
		if (node.childNodes[0].textContent == "State") {
			stateEl = node.childNodes[4];
			stateEl.id = "state-dropdown";
			console.log(stateEl);
		}
	}

	$('#country-dropdown').change(function() {
		var country = this.value;
		console.log(country);
		$.ajax({
			type: 'POST',
			url: '/wp-admin/admin-ajax.php',
			data: {
				action: 'get_states',
				country: country
			},
			success: function(response) {
				$('#state-dropdown').html(response);
			}
		});
	});
});