@include('common.header_login')

<!--header end here-->
<!--[if lt IE 8]>
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade browser</a> to improve experience.</p>
    <![endif]-->     
    <section class="fxt-template-animation fxt-template-layout4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 col-12 fxt-bg-wrap"><img src="{{ asset('public/uploads/') }}/logo/ezee.jpg" style="width:40%;" alt="Ezeerides"> </div>
				
				<div class="col-md-8 col-12 ">
                    <div class="fxt-content">
                        <div class="fxt-form">
							<h1>{{ $pageheading }}</h1>
							
							<p>{!! $content !!}</p>
							
						</div> 
						                          
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- jquery-->
<script src="{{ asset('public/assets/') }}/js/jquery-3.5.0.min.js"></script>
<!-- Popper js -->
<script src="{{ asset('public/assets/') }}/js/popper.min.js"></script>
<!-- Bootstrap js -->
<script src="{{ asset('public/assets/') }}/js/bootstrap_login.min.js"></script>
<!-- Imagesloaded js -->
<script src="{{ asset('public/assets/') }}/js/imagesloaded.pkgd.min.js"></script>
<!-- Validator js -->
<script src="{{ asset('public/assets/') }}/js/validator.min.js"></script>
<!-- Custom Js -->
<script src="{{ asset('public/assets/') }}/js/main.js"></script>


@include('common.footer_body')
