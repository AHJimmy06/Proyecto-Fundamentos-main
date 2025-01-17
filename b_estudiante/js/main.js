$(document).ready(function(){
	$('.btn-sideBar-SubMenu').on('click', function(){
		var SubMenu=$(this).next('ul');
		var iconBtn=$(this).children('.zmdi-caret-down');
		if(SubMenu.hasClass('show-sideBar-SubMenu')){
			iconBtn.removeClass('zmdi-hc-rotate-180');
			SubMenu.removeClass('show-sideBar-SubMenu');
		}else{
			iconBtn.addClass('zmdi-hc-rotate-180');
			SubMenu.addClass('show-sideBar-SubMenu');
		}
	});
	$('.btn-exit-system').on('click', function(){
		swal({
		  	title: 'Estás seguro??',
		  	text: "La sesion se va a cerrar",
		  	type: 'warning',
		  	showCancelButton: true,
		  	confirmButtonColor: '#03A9F4',
		  	cancelButtonColor: '#F44336',
		  	confirmButtonText: '<i class="zmdi zmdi-run"></i> Si, salir!',
		  	cancelButtonText: '<i class="zmdi zmdi-close-circle"></i> No, Cancelar!'
		}).then(function () {
			window.location.href="../php/csesion.php";
		});
	});
	$('.btn-form').on('click', function(){
		swal({
		  	title: 'Formato de Registro',
			html: `<div class="form-group label-floating"> <label class="control-label"> Nombre Completo </label> <input class="form-control" type="text" id="nombre" name="nombre" required disabled value="Juan Carlos Perez Zuñiga" </div> <div class="form-group label-floating"><label class="control-label">Cédula</label><input class="form-control" type="text" name="cedula" disabled value="1850645657"></div>` ,
		  	type: 'info',
		  	showCancelButton: false,
		  	confirmButtonColor: '#640d14',
		  	confirmButtonText: ' Continuar',
		});
	});
	$('.btn-formpro').on('click', function(){
		swal({
		  	title: 'Formato de Registro',
			html: `<div class="form-group label-floating"> <label class="control-label"> Nombre Completo </label> <input class="form-control" type="text" id="nombre" name="nombre" required disabled value="Juan Carlos Perez Zuñiga" </div> <div class="form-group label-floating"><label class="control-label">Cédula</label><input class="form-control" type="text" name="cedula" disabled value="1850645657" ></div><div class="form-group label-floating"> <label class="control-label"> Especialidad </label> <input class="form-control" type="text" id="nombre" name="nombre" required disabled value="Matemáticas"> </div>` ,
		  	type: 'info',
		  	showCancelButton: false,
		  	confirmButtonColor: '#640d14',
		  	confirmButtonText: ' Continuar',
		});
	});
	$('.btn-menu-dashboard').on('click', function(){
		var body=$('.dashboard-contentPage');
		var sidebar=$('.dashboard-sideBar');
		if(sidebar.css('pointer-events')=='none'){
			body.removeClass('no-paddin-left');
			sidebar.removeClass('hide-sidebar').addClass('show-sidebar');
		}else{
			body.addClass('no-paddin-left');
			sidebar.addClass('hide-sidebar').removeClass('show-sidebar');
		}
	});
	$('.btn-Notifications-area').on('click', function(){
		var NotificationsArea=$('.Notifications-area');
		if(NotificationsArea.css('opacity')=="0"){
			NotificationsArea.addClass('show-Notification-area');
		}else{
			NotificationsArea.removeClass('show-Notification-area');
		}
	});
	$('.btn-search').on('click', function(){
		swal({
		  title: 'What are you looking for?',
		  confirmButtonText: '<i class="zmdi zmdi-search"></i>  Search',
		  confirmButtonColor: '#03A9F4',
		  showCancelButton: true,
		  cancelButtonColor: '#F44336',
		  cancelButtonText: '<i class="zmdi zmdi-close-circle"></i> Cancel',
		  html: '<div class="form-group label-floating">'+
			  		'<label class="control-label" for="InputSearch">write here</label>'+
			  		'<input class="form-control" id="InputSearch" type="text">'+
				'</div>'
		}).then(function () {
		  swal(
		    'You wrote',
		    ''+$('#InputSearch').val()+'',
		    'success'
		  )
		});
	});
	$('.btn-modal-help').on('click', function(){
		$('#Dialog-Help').modal('show');
	});
});
(function($){
    $(window).on("load",function(){
        $(".dashboard-sideBar-ct").mCustomScrollbar({
        	theme:"light-thin",
        	scrollbarPosition: "inside",
        	autoHideScrollbar: true,
        	scrollButtons: {enable: true}
        });
        $(".dashboard-contentPage, .Notifications-body").mCustomScrollbar({
        	theme:"dark-thin",
        	scrollbarPosition: "inside",
        	autoHideScrollbar: true,
        	scrollButtons: {enable: true}
        });
    });
})(jQuery);