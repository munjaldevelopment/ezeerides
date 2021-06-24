$(document).ready(function() {
	var base_url = $('base').attr('href');
	
	var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
	
	$('.home-class').bind('click', function() {
		$('.esskay-home li a').removeClass('active');
		$('.home-class').addClass('active');
		
		$.ajax({
			url: base_url+'homepage',
			type: 'post',
			data: {_token: CSRF_TOKEN},
			beforeSend: function() {
				$('.preloader').show();
			},
			success: function(output) {
				$('.preloader').hide();
				$('.home-content').html(output);
			}
		});
	});
		
	$('.doc-class').bind('click', function() {
		$('.esskay-home li a').removeClass('active');
		$('.doc-class').addClass('active');
		
		$.ajax({
			url: base_url+'document',
			type: 'post',
			data: {_token: CSRF_TOKEN},
			beforeSend: function() {
				$('.preloader').show();
			},
			success: function(output) {
				$('.preloader').hide();
				$('.home-content').html(output);
			}
		});
	});

	$('.news-class').bind('click', function() {
		$('.esskay-home li a').removeClass('active');
		$('.news-class').addClass('active');
		
		$.ajax({
			url: base_url+'news',
			type: 'post',
			data: {_token: CSRF_TOKEN},
			beforeSend: function() {
				$('.preloader').show();
			},
			success: function(output) {
				$('.preloader').hide();
				$('.home-content').html(output);
			}
		});
	});

	$('.contact-class').bind('click', function() {
		$('.esskay-home li a').removeClass('active');
		$('.contact-class').addClass('active');
		
		$.ajax({
			url: base_url+'contact_us',
			type: 'post',
			data: {_token: CSRF_TOKEN},
			beforeSend: function() {
				$('.preloader').show();
			},
			success: function(output) {
				$('.preloader').hide();
				$('.home-content').html(output);
			}
		});
	});
	
	$('.graph-class').bind('click', function() {
		$('.esskay-home li a').removeClass('active');
		$('.graph-class').addClass('active');
		
		$.ajax({
			url: base_url+'company_graph',
			type: 'post',
			data: {_token: CSRF_TOKEN},
			beforeSend: function() {
				$('.preloader').show();
			},
			success: function(output) {
				$('.preloader').hide();
				$('.home-content').html(output);
			}
		});
	});
	
	$('.home-class').trigger('click');
});