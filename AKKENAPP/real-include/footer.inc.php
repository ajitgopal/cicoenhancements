<?php $dateContainer = $dateContainer ? $dateContainer :"body";?>
<!-- Global Footer Start -->
<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>

<script>
(function ($) {
	$(function() {
		
		try{
			var locPgName = window.location.pathname.match(/[^\/]+$/)[0];
			var chckName = locPgName.split(".");
			if(chckName[0] != "schCalendarView" && chckName[0] != "home"){
				if (!$.fn.bootstrapDP && $.fn.datepicker && $.fn.datepicker.noConflict) {
					var datepicker = $.fn.datepicker.noConflict(); 
					$.fn.bootstrapDP = datepicker;
					$('.bsdatepicker').bootstrapDP({
						autoclose: true,
						todayHighlight: true,
					});
					$('.bdatePicker').bootstrapDP({
						autoclose: true,
						container: "<?php echo $dateContainer; ?>",
						format: 'mm/dd/yyyy',
						todayHighlight: true
					});
				}

			}
		}catch(e){
		}		
	});
	})(jQuery);
</script>
<script type="text/javascript" src="/BSOS/scripts/jquery.form.min.js"></script> 
<script src="https://cdn.datatables.net/1.12.0/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.0/js/dataTables.bootstrap5.min.js"></script>
<!-- Tab Scroll -->
<script src="/BSOS/scripts/jquery.scrolling-tabs.min.js"></script>
<script>
(function ($) {
	$(function() {
		$('.nav-tabs').scrollingTabs({
			bootstrapVersion: 4  
		});
	});
})(jQuery);
</script>
<!-- Multiple Select -->
<script src="/BSOS/plugins/multiple-select/multiple-select.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>


	jQuery(document).ready(function($) {
		
					
		$('.custom-select').select2();

		/* $('#gridTable').DataTable(); */
		
		try{
			var wnPgName = window.location.pathname.match(/[^\/]+$/)[0];
			var winName = wnPgName.split(".");
			if(winName[0] != "inbox"){
				$('#gridTable').DataTable();
			}
		}catch(e){
		}

	//	Multiple Select Top Position
		$('.multiple-select').multipleSelect({
			filter: true
		});
		
		$('.multiple-select-top').multipleSelect({
			filter: true,
			position: 'top'
		});
		
		
		try{
			var Pgpth = window.location.pathname.match(/[^\/]+$/)[0];
			var ckPthName = Pgpth.split(".");
			if(ckPthName[0] == "contactSummary" || ckPthName[0] == "companySummary" || ckPthName[0] == "revconreg0" || ckPthName[0] == "jobSummary"){
				
					//HTML5 Local Storage For Accordion
					//accordion state maintaining code
					var lastState = localStorage.getItem('lastState');
					
					if (!lastState) {
						lastState = [];
						localStorage.setItem('lastState', JSON.stringify(lastState));
					} else {
						var lastStateArray = JSON.parse(lastState);
						var arrayLength = lastStateArray.length;
						if(arrayLength > 0){
							var objIndx = lastStateArray.findIndex((obj => obj.module == ckPthName[0]));
							if(objIndx != -1){
								
								var reqObj = lastStateArray[objIndx];
								var secDsData = reqObj.secdata;
								
								for (var i = 0; i < secDsData.length; i++) {
									
									var secRDat = secDsData[i].split("@");
									var panel = '#' + secRDat[0];
									
									if(secRDat[1] == "0"){
										
										$(panel).find('button.accordion-button').addClass('collapsed');
										$(panel).find('button.accordion-button').attr('aria-expanded', 'false');
										$(panel).find('.accordion-collapse').removeClass('show');
										$(panel).removeClass("unsortable").addClass("srtcolumn");
										if(secRDat[0]=="contactsetid")
										$("#contactsetid").find('.accordion-button .form-check-input').prop("checked", false);
									}else{
										$(panel).find('button.accordion-button').removeClass('collapsed');
										$(panel).find('button.accordion-button').attr('aria-expanded', 'true');
										$(panel).find('.accordion-collapse').addClass('show');
										$(panel).removeClass("srtcolumn").addClass("unsortable");
										if(secRDat[0]=="contactsetid")
										$("#contactsetid").find('.accordion-button .form-check-input').prop("checked", true);
									}
								} 
							}
							
						}
						
					}
					
					
					
					$('.accordion').on('show.bs.collapse', '.accordion-collapse', function(e) {
						if(event.target.nodeName == "I"){
							event.stopImmediatePropagation();
							event.stopPropagation();
							return false;
						}
					});	
					
					$('.accordion').on('hide.bs.collapse', '.accordion-collapse', function(e) {
						if(event.target.nodeName == "I"){
							event.stopImmediatePropagation();
							event.stopPropagation();
							return false;
						}
					});	
					
					/* $('.accordion-button .link-primary').on("click",function(event){
						event.stopImmediatePropagation();
						event.stopPropagation();
					}); */

				
					$('.accordion').on('shown.bs.collapse', '.accordion-collapse', function() {
						$("#"+$(this).attr('id')).parent().removeClass("srtcolumn").addClass("unsortable");
						
						updatLastStage(ckPthName[0]);
							
					});

					$('.accordion').on('hidden.bs.collapse', '.accordion-collapse', function() {
						$("#"+$(this).attr('id')).parent().removeClass("unsortable").addClass("srtcolumn");
						
						updatLastStage(ckPthName[0]);
						
					});
					
					
			}
		}catch(e){
			console.log(e.toString());
		}
		
		
		try{
			var locPtName = window.location.pathname.match(/[^\/]+$/)[0];
			var chkPgName = locPtName.split(".");
			if(chkPgName[0] == "contactSummary" || chkPgName[0] == "companySummary" || chkPgName[0] == "revconreg0" || chkPgName[0] == "jobSummary"){
				
				$( "#rightpane .innerdivstyle-drag" ).sortable({
					  helper:'clone',
					  revert: true,
					  axis:'y',
					  cancel: ".unsortable",
					  cursor:'move',
					  update: function() { saveRWorder(chkPgName[0]); }
				  });
				  
				 
				  if (typeof(Storage) !== "undefined") {
					  var wdgtData = localStorage.getItem(chkPgName[0]);
					  var lcData = JSON.parse(wdgtData);
					  if(lcData != null && lcData.hasOwnProperty("module")){
							  var dvData = lcData.wdgtData;
							  for (var i = 0; i < dvData.length; i++)
							  {
																
								$("#rightpane .innerdivstyle-drag").find('#' + dvData[i]).appendTo($("#rightpane .innerdivstyle-drag"));
								
							  }
					  }
				  }
				  
				  
				    <!-- jQuery UI Sortable for Bootstrap Accordion  -->
					$( ".accordionSortable" ).sortable({
						/* connectWith: ".accordionSortable",
						handle: ".accordion-header", */
						helper:'clone',
						axis:'y',
						cancel: ".unsortable",
						cursor:'move',
						update: function(e) { 
						var sectyp = chkPgName[0]+"l";
							saveLftSecorder(sectyp); 							
						}
					});
					
					if (typeof(Storage) !== "undefined") {
					  var secLSdata = chkPgName[0]+"l";
					  var wdgtData = localStorage.getItem(secLSdata);
					  var lcData = JSON.parse(wdgtData);
					  if(lcData != null && lcData.hasOwnProperty("modul")){
							  var dvData = lcData.sectData;
							  for (var i = 0; i < dvData.length; i++)
							  {
															
								$(".accordionSortable").find('#' + dvData[i]).appendTo($(".accordionSortable"));
								
							  }
					  }
					}
			}
						
		}catch(e){
			console.log("2 : "+e.toString());
		}
				
			$(document).on('click', '#main-menu .nav-link', function (e) {
				if(e.ctrlKey && e.type == "click")
				{
					console.log("ctrl + mouse clicked !")
					window.open(this.href);
					e.preventDefault();
				}else{
					window.location = $(this).attr('href');
				}				 
			});
			
			//Candidate screen navigation scroller
			$(".leftArrow").click(function () { 
				var leftPos = $('.innerWrapper').scrollLeft();
				$(".innerWrapper").animate({scrollLeft: leftPos - 200}, 800);
			});
			
			$(".rightArrow").click(function () { 
				var leftPos = $('.innerWrapper').scrollLeft();
				$(".innerWrapper").animate({scrollLeft: leftPos + 200}, 800);
			});
						
	});
	
	function updatLastStage(lstval){
		
		try{ 
			//HTML5 Local Storage For Accordion
			//accordion state maintaining code
			var lastState = localStorage.getItem('lastState'); 
			
			if (!lastState) {
				lastState = [];
				localStorage.setItem('lastState', JSON.stringify(lastState));
			} else {
				lastState = JSON.parse(localStorage.getItem('lastState'));
				var Pagpth = window.location.pathname.match(/[^\/]+$/)[0];
				var ckPathName = Pagpth.split(".");
				var secObj ={};
				secObj["module"] = ckPathName[0];
				secObj["secdata"] = [];
				$(".accordionSortable .accordion-item").each(function() {
					var secval = $(this).attr('id')+"@"+1;
					if($(this).hasClass("srtcolumn")){
						secval = $(this).attr('id')+"@"+0;
					}
					if ($.inArray(secval, secObj["secdata"]) == -1) {
						secObj["secdata"].push(secval);
					}
				});
				
				if(lastState.length > 0){
					var objIndex = lastState.findIndex((obj => obj.module == ckPathName[0]));
					if(objIndex < 0)
						lastState.push(secObj);
					else
						lastState[objIndex]=secObj;
				}else{
					lastState.push(secObj);
				}
				
				localStorage.setItem('lastState', JSON.stringify(lastState));
			}
			
		}catch(e){
			console.log("3 : "+e.toString());
		} 
	}
	
	function pgWidth(val){
		var scrnWdthvl = parseFloat(val/100);
		return window.screen.availWidth * scrnWdthvl;
	}
	function pgHeight(val){
		var scrnHghtvl = parseFloat(val/100);
		window.screen.availHeight * scrnHghtvl;
	}

	function closeIframeModal(){
		$('.iframe-modal', parent.document).parent('html,body').css({
			overflow: 'auto',
			height: 'auto'
		});
		$('.iframe-modal', parent.document).remove();
	}

	$('.iframe-modal', parent.document).parent('html,body').css({
		overflow: 'hidden',
		height: '100%'
	});
	
	function saveRWorder(pval){
		var orderval = Array();
		$("#rightpane .innerdivstyle-drag").each(function(index, value){
			var orderData = $(this).sortable("toArray");
			console.log("orderData : "+orderData);
			var wdgtObj ={};
			wdgtObj['module'] = pval;
			wdgtObj['wdgtData'] = orderData;
			var wdgtsequnc = JSON.stringify(wdgtObj);
			if(typeof(Storage) !== "undefined") {
				localStorage.setItem(pval, wdgtsequnc);
			}	
		});	
	}
	
	function saveLftSecorder(pval){
		var orderval = Array();
		$(".accordionSortable").each(function(index, value){
			var orderData = $(this).sortable("toArray");
			console.log("lftsecorderData : "+orderData);
			var wdgtObj ={};
			wdgtObj['modul'] = pval;
			wdgtObj['sectData'] = orderData;
			var wdgtsequnc = JSON.stringify(wdgtObj);
			if(typeof(Storage) !== "undefined") {
				localStorage.setItem(pval, wdgtsequnc);
			}	
		});	
		updatLastStage(pval);
	}

	
	
</script>

<!-- SmartMenus jQuery plugin 
    <script type="text/javascript" src="https://vadikom.github.io/smartmenus/src/jquery.smartmenus.js"></script>  -->

<script type="text/javascript" src="/BSOS/scripts/jquery.smartmenus.min.js"></script>

<!-- SmartMenus jQuery Bootstrap 4 Addon  -->
<script type="text/javascript" src="/BSOS/scripts/jquery.smartmenus.bootstrap-4.min.js"></script> 

<!-- Metis Menu -->
<script type="text/javascript" src="/BSOS/assets/js/metisMenu.min.js"/></script>
<script>
	$(function () {
		//$("#metismenu").metisMenu();
	});
</script>