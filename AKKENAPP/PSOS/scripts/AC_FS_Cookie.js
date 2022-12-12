function AC_FS_Cookie(name, value, expires, path, domain) {
    var samesite = "lax";

    var curCookie = name + "=" + escape(value) +
        ((expires) ? "; expires=" + expires : "; expires=" + 0) +
        ((path) ? "; path=" + escape(path) : "; path=" + escape("/"));
    ((domain) ? "; domain=" + domain : "; domain=" + ac_fs_server_host);

    curCookie += "; secure" + "; SameSite=" + samesite;

    document.cookie = curCookie;
}

if (typeof jQuery == 'undefined') {
    // document.write(unescape('%3Cscript id="vidjs" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"%3E%3C/script%3E'));
    var ijqscript = document.createElement('script');
    ijqscript.setAttribute('id','vidjs');
    ijqscript.setAttribute('src','https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
    
    document.head.appendChild(ijqscript);
}

var exflg = true;
document.addEventListener("DOMContentLoaded", function(event) {
    var url = window.location.pathname;
    var filename = url.substring(url.lastIndexOf('/') + 1);
    var chckName = filename.split(".");
    var elmnt = document.body;
    elmnt.classList.add(chckName[0].toLowerCase());

    var locPath = window.location.pathname.split('/');

    (function($) {

        try {

            /*theme code*/
            try {
                var currentTheme = localStorage.getItem("currentTheme");
                if (currentTheme == "dark-theme") {
                    setCurrntThem(currentTheme);
                } else if (currentTheme == "light-theme") {
                    setCurrntThem(currentTheme);
                } else {
                    currentTheme = "default-theme";
                    setCurrntThem(currentTheme);
                }
            } catch (e) {

            }
            /*theme code*/

            if (locPath[2] != "Analytics" && locPath[3] != "gigboard.php" && locPath[4] != "gigboard.php") {

                var inputfld = $('input').not(':input[type=button],:input[type=submit],:input[type=reset],:input[type=checkbox],:input[type=radio],:input[type=file],:input[name=schavaildate],:input[name=schavailenddate],.revconreg15 :input[name=txtstartdate],.revconreg15 :input[name=txtenddate],.recurrence input,.search_select_assoc input,.createnewgroup input,.saveastime input,.editjoborder input,.addparticipants input,.saveasexpense input,.contact input,.candidate input,.bulk_targetlist input,.todoPopup1 input,.daterangepicker_popup input,.campaign input,.companytaxsetup input,.newearn input,.editearn input,.newcon input,.editcon input,.ercontributionsetup input,.todopopup input,.addopportunity input,.conreg2 input,.revconreg2 input,.newconreg2 input,.editjoborder input[name=posfilled],.regmanage input[name=cust_lastcontacted],.editjoborder #crm-joborder-billinginfoDiv1 input,.opprmanage input,.editcontribution input,.redirectassignment input,.editmanage input,.schassigncalendarview input,.editoroptions input,#rate_avail input,.newgroup input,.editgroup input,.editcontact input,.finddetails_popup input,.taskupdateform input,.emplist input,.suggest input,.ask input,.choosemember input,.new_timesheet input,.empfaxhis input,.multitimesheet input,.doremindercss input,.doreminder input,.akken_ess_select_popup_users input,.histimesheets input,.rejectedtimesheets input,.deletedtimesheets input,.expensereport input,.hisexpense input,.deletedexpenses input,.invoiceall input,.invoicedeliver input,.invoicehis input,.recieve_pay input,.rpayregister input,.managecreatebills input,.rcvbills input,.creategeneralvendorbills input,.billhistory input,.billchistory input,.billconhistory input,.managepaybills input,.payroll input,.showemplpay input,.paystub_history input,.perdiemshiftcalendar input,.newexpense input,.regmanage input[name=newstate],.addexpensetype input,.empexpenses input,.subeditexpensesheet input,.empexpenseshis input,.transferfunds input,.selectemailoptions input,.createinvoicetemplate input,.createinvoicetemplate_combined input,.createinvoicetemplate_consolidated input,.invoice input,.createmanualinvoice input,.invoice_timeexp input,.invoiceopt input,.editinvoice_timeexp input,.emailInvoice input,.editavailcredit input,.creditsregister input,.managebillpayterms input,.itemmanage input,.billpayregister input,.newcat input,.mkdeposit input,.statsetup input,.newcompcode input,.immigrationstatus input,.deductions input,.expense input,.approveex input,.timesheets input,.history input,.paychecks input,.showessgigboardfilters input,.garnishments input,.companycontributions input,.company_info input,.setupjoborderpref input,.schcalendarview input,.extmail input,.resman input,.ansquestion input,.newres input,.commissionadd input,.timeintimeout input,.addformfield input,.dropdown_comp input,.joborder_titles_new input,.joborder_titles_option_setup input,.email_setup input,.ogmail_emp_select_popup input,.aob_notifications input,.placement_notifications input,.cust_email_tmpl input,.emp_email_tmpl input,.timesheet_email_tmpl input,.inv_email_tmpl input,.timesheet_notifications input,.applicantDistribution input,.task_notifications input,.vimanage input,.add_edit_ratetypes input,.managecommissionlevels input,.editcommissionlevels input,.addap input,.cpayroll_setup input,.add_group input,.edit_group_users input,.addeverifyusers input,.editeverifyusers input,.addusers input,.uom_timesheet input,.custom_timesheet input,.editap input,.referral_bonus_settings input,.contactinfo input,.w4 input,.ibreferral_mngmt_home input,.aca_emp_info input,.addeditcrmperdiemshift input,.submit_roles input,.placement input, .adddept input,.editdept input,.companytaxlist input,.placement input,.payschedulesetup input,.addimmig1 input,.newconreg29 input,.approveassignment input,.organizer input,.setuprules input,.addrule input,.editrule input,.cico input,.contimesheets input,.apptimesheets input,.akken_emp_select_popup input,.credential_notifications input,.conreg4 input,.subinfo input,.newconreg24 input,.addacccompany input,.addfolder input,.resconreg2 input,.viewtaxw4 input,.paydata input,.newconreg12 input,.newconreg21 input,.newconreg27 input,.newconreg15 input,.newconreg20 input,.paytaxes input,.custom_invoice input,.email_reminder_tmpl input,.compose input,.conreg14 input,.editinvoice input,.conreg12 input,.revconreg7 input,.revconreg14 input,.revconreg4 input,.revconreg30 input,.empconreg13 input,.addacclocation input,.showgigboardfilters input,.uptnewshiftstoasign input,.addtaxes #effectiveDatesRow input,.conreg27 input,.revconreg27 input,.conreg28 input,.revconreg28 input,.editexpenseheet input,.todopopup1 input,.createvendorbills input,.creditdetails input,.company_info #madisondate,.addcompany #multipleRatesTab input,.conreg30 #comments,.revconreg19 #ptoSectn input,.showpaybills input,.bidashboard input,.ceainit input,.ceaduplicate input,.newmanage input,.exportedtimesheets input,.exportedexpenses input,.editselfservice input,.popupeditappoint input,.newcico input,.cico_edit input,.cico_timesheet_edit input,.ultigigsbroadcast input,.ultigigsperdiemshifts input,.editpref input');

                $(inputfld).each(function() {
                    if ($(this).attr('type') != "hidden" && $(this).attr('type') != "checkbox" && $(this).attr('type') != "radio" && $(this).attr('type') != "button" && $(this).attr('type') != "button" && $(this).attr('type') != "submit" && $(this).attr('name') != "phone_extn") {
                        /* $(this).addClass("form-control d-inline-block"); */
                        if ($(this).parent().hasClass("input-group") || $(this).parent().is('[class*=col]')) {
                            //$(this).filter(":not(#rightpane select, .candidates select)").addClass("form-select d-block w-100");
                            var inpGrp = $(this).parent().hasClass("input-group");
                            if (inpGrp) {
                                if ($(this).parent().parent().hasClass("col"))
                                    $(this).filter(":not(#rightpane input)").addClass("form-control");
                            } else {
                                $(this).filter(":not(#rightpane input,.editdocument input)").addClass("form-control d-block w-100");
                            }

                        } else {
                            $(this).filter(":not(#rightpane input,#scrolldisplay input,.Zebra_DatePicker_Icon_Wrapper input,#crm-joborder-hrprocessDiv input,#crm-joborder-relocationDiv input,#crm-joborder-billinginfoDiv1 input,#overtime_rate_pay_direct input,#db_time_payrate_direct input,.addcompany input[name=prateval])").addClass("form-control d-inline-block");
                            $(this).filter(":not(#rightpane input,#scrolldisplay input,.Zebra_DatePicker_Icon_Wrapper input,#crm-joborder-hrprocessDiv input,#crm-joborder-relocationDiv input,#crm-joborder-billinginfoDiv1 input,#overtime_rate_pay_direct input,#db_time_payrate_direct input,.addcompany input[name=prateval])").css({ "width": "250px" });
                        }


                    } else {
                        if ($(this).attr('type') == "checkbox" || $(this).attr('type') == "radio") {
                            $(this).addClass("form-check-input");
                        } else {
                            if ($(this).attr('type') != "button" && $(this).attr('type') != "submit")
                                $(this).addClass("form-control");
                        }
                    }
                });

                var inpfld = $('.conreg2 input,.revconreg2 input,.revconreg24 input,.newconreg2 input,.editjoborder input,.redirectassignment input,.editmanage input,.editavailcredit input,.deductions input,.garnishments input,.companycontributions input,.company_info input,.addap input,.editap input,.newcompcode input').not(':input[type=button],:input[type=submit],:input[type=reset],:input[type=checkbox],:input[type=radio],:input[type=file],.editjoborder input[name=posfilled],.editmanage #multipleRatesTab input,.editmanage #opprcommissionRows input,.editjoborder #crm-joborder-billinginfoDiv input,.editjoborder #mainSkillTable input,.editjoborder #crm-joborder-hrprocessDiv input,.editjoborder #crm-joborder-relocationDiv input,.editjoborder #crm-joborder-scheduleDiv input,.newcompcode input,.editmanage #multipleRatesShiftsTab input,.addtaxes #effectiveDatesRow input,.createvendorbills input,.company_info #madisondate,.revconreg19 #ptoSectn input,.bidashboard input,.immigrationstatus input,.popupeditappoint input');
                $(inpfld).each(function() {
                    if ($(this).parent().hasClass("input-group"))
                        $(this).addClass('form-control');
                    else
                        $(this).addClass('form-control w-250');

                });

                var colInpfld = $('input').not(':input[type=button],:input[type=submit],:input[type=reset],:input[type=checkbox],:input[type=radio],:input[type=file],.newcat input,.editdocument input,.addfolder input,.contact input,.showgigboardfilters input,.revconreg19 #ptoSectn input,.newmanage input');
                $(colInpfld).each(function() {
                    if ($(this).parent().is('[class*=col]'))
                        $(this).addClass('form-control w-100');
                });

                var colTxtfld = $('textarea').not('.contact textarea');
                $(colTxtfld).each(function() {
                    if ($(this).parent().is('[class*=col]'))
                        $(this).addClass('form-control w-100');
                });

                /* $('.mkdeposit input').not(':input[type=button],:input[type=submit],:input[type=reset],:input[type=checkbox],:input[type=radio],:input[type=file],.input-group input').addClass('form-control w-100');
                $('.mkdeposit select').addClass('form-select w-100'); */


                $('table').each(function() {
                    $(this).filter(":not(#rightpane table,#scrolldisplay table,.calendar table,.managefolder .documentManagerFont table,.editjoborder .accordion-item table,.newassign .accordion-item table,.expensereport table,.showbdata table,.conreg6 .locDtable, #multipleRatesTab table,.newconreg19 table,.paydata table,.revconreg0 .crenTab table,.gigboard .myEditShiftModal table,.opportunitysummary .remvTabCls table,.remvTabCls table,.home table,.skills-table)").addClass("table table-borderless mb-0");
                });

                $(".calendar table,.showbdata table").not('table:eq(0),table:eq(-1)').addClass("table table-bordered mb-0");
                $(".home table").addClass("table mb-0");

                var selectfld = $('select').not('.canSelectCustomWidth select, select[name=showrec],.schcalendarview select,.schassigncalendarview select,.recurrence select,.form-select-w-auto select,.mailschedule select,.createnewgroup select,.saveastime select,.export_crm_searchdata select,.search_select_assoc select,.editjoborder select,.addparticipants select,.maremplist select,.managecat select,.address_book select,.saveasexpense select,.contact select,.candidate select,.adddivisions select,.bulk_targetlist select,.todoPopup1 select,.daterangepicker_popup select,.campaign select,.companytaxsetup select,.newearn select,.editearn select,.newcon select,.editcon select,.ercontributionsetup select,.revconreg24 select,.upload select,.todopopup select,.addopportunity select,.conreg2 select,.revconreg2 select,.revconreg10 select,.revconreg31 select,.revconreg32 select,.conreg24 select,.conreg31 select,.conreg32 select,.newconreg2 select,.newconreg18 select,.newconreg28 select,.newconreg29 select,.newconreg30 select,.addcompany select[name=sstatus],.addcompany select[name=cowner],.addcompany select[name=cshare],.editmanage select[name=sstatus],.editmanage select[name=cowner],.editmanage select[name=cshare],.opprmanage select,.editcontribution select,.redirectassignment select,.editoroptions select,.extmail select,.addfilter select,.contacts select,.newgroup select,.editgroup select,.editcontact select,.finddetails_popup select,.inbox select,.taskupdateform select,.emplist select,.suggest select,.ask select,.choosemember select,.new_timesheet select,.empfaxhis select,.doremindercss select,.doreminder select,.akken_ess_select_popup_users select,.invoiceall select,.invoicedeliver select,.invoicehis select,.recieve_pay select,.rpayregister select,.managecreatebills select,.rcvbills select,.creategeneralvendorbills select,.billhistory select,.billchistory select,.billconhistory select,.managepaybills select,.payroll select,.showemplpay select,.paystub_history select,.perdiemshiftcalendar select,.addacccompany #mntcmnt2 select,.newexpense select,.empexpenses select,.subeditexpensesheet select,.empexpenseshis select,.transferfunds select,.selectemailoptions select,.createinvoicetemplate select,.createinvoicetemplate_combined select,.createinvoicetemplate_consolidated select,.invoice select,.createmanualinvoice select,.invoice_timeexp select,.editinvoice_timeexp select,.emailInvoice select,.editavailcredit select,.creditsregister select,.managebillpayterms select,.itemmanage select,.billpayregister select,.newcat select,.mkdeposit select,.statsetup select,.newcompcode select,.immigrationstatus select,.deductions select,.expense select,.approveex select,.timesheets select,.history select,.paychecks select,.showessgigboardfilters select,.garnishments select,.companycontributions select,.company_info select,.setupjoborderpref select,.schcalendarview select,.extmail select,.resman select,.ansquestion select,.newres select,.commissionadd select,.timeintimeout select,.newassign select[name=shift_type],.editassign select[name=shift_type],.approveassignment select[name=shift_type],.addformfield select,.dropdown_comp select,.joborder_titles_new select,.joborder_titles_option_setup select,.email_setup select,.ogmail_emp_select_popup select,.aob_notifications select,.placement_notifications select,.cust_email_tmpl select,.emp_email_tmpl select,.timesheet_email_tmpl select,.inv_email_tmpl select,.timesheet_notifications select,.applicantDistribution select,.task_notifications select,.invoice_css_user_notification select,.vimanage select,.add_edit_ratetypes select,.managecommissionlevels select,.editcommissionlevels select,.addap select,.cpayroll_setup select,.add_group select,.edit_group_users select,.addeverifyusers select,.editeverifyusers select,.addusers select,.uom_timesheet select,.custom_timesheet select,.editap select,.referral_bonus_settings select,.w4 select,.ibreferral_mngmt_home select,.referral_bonus_settings select,.contactinfo select,.w4 select,.ibreferral_mngmt_home select,.aca_emp_info select,.expensereport select,.addeditcrmperdiemshift select,.submit_roles select,.placement select,.icalendar select,.setuprules select,.addrule select,.editrule select,.cico select,.contimesheets select,.apptimesheets select,.akken_emp_select_popup select,.credential_notifications select,.invoice_notification select,.applicantnotification select,.conreg4 select,.subinfo select,.newconreg27 select,.createjoborder #smdatetable select,.createjoborder #crm-joborder-scheduleDiv select,.organizer select,.newconreg24 select,.revconreg15 select,.addacccompany select,.conreg19 select,.conreg23 select,.newconreg13 select,.newconreg14 select,.conreg15 select,.movedocument select,select[class=multiple-select],.paydata select,.newconreg15 select,.exporthrminfo select,.paytaxes select,.custom_invoice select,.email_reminder_tmpl select,.compose select,.newconreg20 select,.addweb #grid_form select,.revconreg4 select,.newearn select,.gigboard select,.addtaxes #effectiveDatesRow select,.multitimesheet select,.addedithrmperdiemshift select,.ss_empindividualentry select,.individualentry select,.editexpenseheet select,.todopopup1 select,.editweb select,.createvendorbills select,.uptnewshiftstoasign select,.empconreg18 select,.empconreg29 select,.company_info #payrollProsed select,.viewtemplate #templateType,.approveassignment #crm-joborder-billinginfoDiv select,.revconreg19 #ptoSectn select,.showpaybills select,.importfile1 select,.bidashboard select,.newmanage select,.addap select,.popupeditappoint select,.cico_edit select,.cico_timesheet_edit select,.new_pendingtimesheet select,.pendingtimeintimeout select,.pendingtimesheets select,.addaccount select,.editaccount select');

                $(selectfld).each(function() {
                    /* $(this).addClass("form-select d-inline-block"); */
                    if ($(this).parent().hasClass("input-group") || $(this).parent().is('[class*=col]')) {
                        $(this).filter(":not(#leftpane select,#rightpane select,#scrolldisplay select,.candidates select,#crm-joborder-hrprocessDiv select,#crm-joborder-relocationDiv select,#crm-joborder-billinginfoDiv1 select)").addClass("form-select d-block w-100");
                    } else {
                        $(this).filter(":not(#leftpane select,#rightpane select,#scrolldisplay select, .candidates select,#crm-joborder-hrprocessDiv select,#crm-joborder-relocationDiv select)").addClass("form-select d-inline-block");
                        $(this).filter(":not(#leftpane select,#rightpane select,#scrolldisplay select,#crm-joborder-hrprocessDiv select,#crm-joborder-relocationDiv select,#crm-joborder-billinginfoDiv1 select)").css({ "width": "250px" });
                    }
                    /* $(this).css({"width":"250px"}); */

                });


                var selcfld = $('.conreg2 select,.revconreg2 select,.revconreg24 select,.revconreg31 select,.revconreg32 select,.conreg24 select,.conreg31 select,.conreg32 select,.newconreg2 select,.newconreg6 select,.newconreg18 select,.newconreg28 select,.newconreg30 select,.editjoborder select,.editcontribution select,.redirectassignment select,.doremindercss select,.doreminder select,.addacccompany #mntcmnt2 selec,.deductions select,.companycontributions select,.garnishments select,.company_info select,.addap select,.editap select,.newcompcode select').not('.redirectassignment select[name=payfee],.addacccompany #mntcmnt2 select[name=inmethod],.addacccompany #mntcmnt2 select[name=iterms],.editjoborder #sch_calendar select,.editjoborder #overtime_rate_pay select,.editjoborder #overtime_rate_bill select,.editjoborder #db_time_payrate select,.editjoborder #db_time_billrate select,.editjoborder #crm-joborder-billinginfoDiv select,.editjoborder #mainSkillTable select,.editjoborder #crm-joborder-relocationDiv select,.editjoborder #crm-joborder-scheduleDiv select,.addweb #grid_form select,.editweb #grid_form select,.newearn select,.addusers select,.addtaxes #effectiveDatesRow select,.deductions .akk_deductperiods select,.garnishments #akk_deductperiods,.empconreg29 #akk_deductperiods,.ss_empindividualentry select,.companysummary #mntcmnt2 select,.editweb select,.createvendorbills select,.empconreg18 select,.newconreg28 select,.company_info #payrollProsed select,.viewtemplate #templateType,.approveassignment #crm-joborder-billinginfoDiv select,.revconreg19 #ptoSectn select,.newmanage select,.newcompcode #company_code,.immigrationstatus select,.companycontributions #akku_contribperiod,.editap select,.addap select,.addaccount select,.editaccount select');

                $(selcfld).each(function() {
                    if ($(this).parent().is('[class*=col]')) {
                        if ($(this).is('[class*=multiple-select]'))
                            $(this).addClass('d-block w-100');
                        else
                            $(this).addClass('form-select w-100');
                    } else {
                        $(this).addClass('form-select w-250');
                    }
                });

                var colSelcfld = $('select').not('.newcat select,.newwebfolder select,.icalendar select,.addweb #grid_form select,.editweb #grid_form select,.newearn select,.addusers select,.companysummary #mntcmnt2 select,.newconreg28 select,.viewtemplate #templateType,.approveassignment #crm-joborder-billinginfoDiv select,.revconreg19 #ptoSectn select,.addaccount select,.editaccount select');
                $(colSelcfld).each(function() {
                    if ($(this).parent().is('[class*=col]')) {
                        if ($(this).is('[class*=multiple-select]'))
                            $(this).addClass('d-block w-100');
                        else
                            $(this).addClass('form-select w-100');
                    }
                });

                $('.addcompany input[name=prateval]').removeClass('w-250');

                $('input[type=checkbox], input[type=radio]').addClass('form-check-input');

                /*table table-mp-0*/
                $('#header,.innerdivstyle,.ToDoSummaryM,.mailschedule,.regmanage,.header,.maremplist,.showcampaignlist,.editdept,.dept_deactive_win,.extmail,.createinvoicetemplate,.columnord,.customer_message,.createinvoicetemplate_combined,.createinvoicetemplate_consolidated,.creditdetails,.emp_deactive_win,.addeditassignreasoncodes,.reasoncodes,.alljoborders,.viewcandidate,.viewperinfo,.viewgarnishments,.cancelreassignperdiemshifts,.managecreatebills,.managegroups,.invoicedeliver').find("table:eq(0)").addClass('table-mp-0');

                $('.reasoncodes').find("#NewEditSMDiv").find("table:eq(0)").addClass('table-mp-0');

                $('.newmanage,.candidates,.companies,.opportunities,.reqman').find("#searchid").children("table:eq(0)").addClass('table-mp-0');

                $('.addopportunity .form-container,.opprmanage,.emailsetup,.viewshiftmngthis,.aob_emp_select_popup').find("table:eq(0)").addClass('table-mp-0');

                $('.addcrmlocation,.redirectassignment,.newfolder,.editfolder,.movefolder,.regmanage,.editavailcredit,.newcompcode,.jobs1,.eeditcontact,.change_hrmdepartments,.profile,.listcustomfields,.dsmanage,.manage,.manageeverify,.joborder_titles_list,.showexpense').find('table:eq(1)').addClass('table-mp-0');
                $('.index,.multiplerateslist,.campaign,.job_viewact,.newcon,.editcon,.editcon1,.newcustomer_message,.editcustomer_message,.bulkupdtassigninfopage,.hismanage,.talentpool').find('table:eq(0),table:eq(1)').addClass('table-mp-0');
                $('.contact_viewact,.viewact,.conreg1,.editdept,.addacccompany').find('table:eq(1), table:eq(2)').addClass('table-mp-0');
                $('.address_book').find('table:eq(1),table:eq(2),table:eq(3)').addClass('table-mp-0');
                $('.footer').find('table:eq(2),table:eq(3)').addClass('table-mp-0');
                $('.conreg2,.conreg3,.conreg4,.conreg5,.conreg6,.conreg7,.conreg8,.conreg9,.conreg16,.conreg17,.conreg10,.conreg11,.conreg14,.conreg19,.conreg20,.conreg22,.conreg23,.conreg24,.conreg25,.conreg26,.conreg27,.conreg28,.conreg29,.conreg30,.conreg31,.conreg32,.newconreg1,.newconreg2,.newconreg4,.newconreg5,.newconreg6,.newconreg9,.newconreg10,.newconreg11,.newconreg13,.newconreg14,.newconreg15,.newconreg17,.newconreg18,.newconreg19,.newconreg20,.newconreg21,.newconreg22,.newconreg23,.newconreg24,.newconreg25,.newconreg27,.newconreg28,.newconreg29,.newconreg30,.viewhract,.new_timesheet,.compensation,.editchecklist,.mkdeposit,.checksetup,.campaignhis,.empconreg1').find("table:eq(2)").addClass('table-mp-0');

                $('.emailsetup').find('table:eq(3),table:eq(4)').addClass('table-mp-0');
                $('.readmessage').find("table:eq(3),table:eq(4),table:eq(5),table:eq(6)").addClass('table-mp-0');

                $('#mainbody,.index').find("table:eq(3)").addClass('table-mp-0');
                $('.addexpensetype').find("table:eq(5)").addClass('table-mp-0');
                $('[class*=conreg],[class*=cand_viewact],[class*=add_multinotes],[class*=campaigncreate]').find("table:eq(1)").addClass('table-mp-0');
                $('[class*=conreg]:not(.conreg10,.conreg14)').find("#maindiv").children("table:eq(1)").addClass('table-mp-0');
                $('.campaign').find('table:eq(5)').addClass('table-mp-0');

                $('#main').not('.hisreviewmanage #main,.formgroups_formslist #main,.companydiscounts #main,.discountadd #main,.addap #main,.custom_invoice #main,.invoice #main,.custom_editinvoice #main,.manapt #main,.viewpobactivityhistory #main,.empconreg13 #main,.forms_order #main,.formslist #main,.uom_showfxdet #main,.ss_completedpayroll #main,.ss_previewbatch #main,.ss_previewpayroll #main,.createvendorbills #main,.showpaybills #main,.importfile2 #main,.importfile4 #main,.managegroups #main,.viewalltimeframes #main,.paydata #main,.cico_showdetails #main,.cico_showdeletedetails #main,.cico_timesheet_his_details #main,.showreqpagesub1 #main,.viewhistory #main').find("table:eq(0)").addClass("table-mp-0");

                $('.savegridsearch, .custom_grid_columns').find('.akkenNewPopUp').find("table:eq(0)").addClass('table-mp-0');
                $('.skill_selector').find('.akkenNewPopUpTitle').find("table").addClass('table-mp-0');

                $('.maremplist2').find("table:not(table:eq(4),table:eq(5),table:eq(6))").addClass("table-mp-0");
                $('.showemaildet').find("table:not(table:eq(5),table:eq(6),table:eq(7))").addClass("table-mp-0");
                $('.revconreg16').find("table:eq(7),table:eq(8),table:eq(10)").addClass("table-mp-0");
                $('.conreg16').find("table:eq(7),table:eq(8),table:eq(9)").addClass("table-mp-0");
                $('.hirevcon').find("table:not(table:eq(3),table:eq(4),table:eq(5),table:eq(6),table:eq(30),table:eq(31))").addClass("table-mp-0");
                $('.campaign').find("#preview").find("table:eq(0),table:eq(1),table:eq(4)").addClass("table-mp-0");
                $('.managelocations').find("table:eq(6),table:eq(7)").addClass("table-mp-0");
                $('.editvendor').find("table:eq(8),table:eq(9)").addClass("table-mp-0");

                $('.taskupdateform').find("table:eq(3)").addClass("table-mp-0");
                $('.inbox #main:eq(0)').find("table:eq(0)").addClass("w-100");
                $('.inbox #divprocess').find("table").addClass("table-mp-0");
                $('.inbox #EmailPageTab').find("table:not(table:eq(0))").addClass("table-mp-0");
                $('.inbox .PaginationPad').find("tbody").find("tr").addClass("pagination").find("td").addClass("page-item").find("a,span").addClass("page-link");
                /* $('.employees .PaginationPad,.manbatches .PaginationPad,.manpaygroups .PaginationPad,.paystub_history .PaginationPad') */
                $('.PaginationPad').addClass("table-mp-0").find("tbody").find("tr").addClass("pagination").find("td").addClass("page-item").find("a,span").addClass("page-link");


                $('#mainbody').not(".billhistory #mainbody,.recieve_pay #mainbody,.rpayregister #mainbody,.creditsregister #mainbody,.editconbillpaym #mainbody,.payemppstubview #mainbody,.transactiondetails #mainbody,.mkdeposit #mainbody,.newcompcode #mainbody,.compensation #mainbody,.benefits #mainbody,.profile #mainbody,.availabilitystatus #mainbody,.hismanage #mainbody,.companiesarch #mainbody,.companycommissions #mainbody,.skills_list #mainbody,.listcustomfields #mainbody,.manageassign #mainbody,.managelists #mainbody,.joborder_titles_list #mainbody,.email_setup #mainbody,.aca_info #mainbody,.burdentypeslist #mainbody,.managerates #mainbody,.shiftmanagement #mainbody,.managecommissions #mainbody,.manage #mainbody,.manageeverify #mainbody,.dsManage #mainbody,.paycheckdetails #mainbody,.joborder_titles_archivelist #mainbody,.manageservicetype #mainbody,.checksetup #mainbody,.campaignhis #mainbody,.resconreg1 #mainbody,.resconreg2 #mainbody,.resconreg3 #mainbody,.resconreg4 #mainbody,.resconreg5 #mainbody,.resconreg6 #mainbody,.resconreg7 #mainbody,.resconreg8 #mainbody,.resconreg10 #mainbody,.resconreg11 #mainbody,.resconreg12 #mainbody,.resconreg16 #mainbody,.resconreg17 #mainbody,.paytaxes #mainbody,.personalinfo #mainbody,.dependents #mainbody,.status #mainbody,.profile #mainbody,.cpayroll_setup #mainbody,.expensereport #mainbody,.groups #mainbody,.newgroup #mainbody,.editgroup #mainbody,.companycommissions #mainbody,.addtaxes #mainbody,.akkubirole #mainbody,.setuprules #mainbody,.gigboard #mainbody,.managequestions #mainbody,.deductions #mainbody,.contactinfo #mainbody,.showexpense #mainbody,.editexpenseheet #mainbody,.managecreatebills #mainbody,.managepaybills #mainbody,.rcvbills #mainbody,.showdetails #mainbody,.invoicedeliver #mainbody,.icalendar #mainbody,.newcon #mainbody,.managegroups #mainbody,.newmanage #mainbody,.companies #mainbody,.opportunities #mainbody,.candidates #mainbody,.reqman #mainbody,.campaigns #mainbody,.talentpool #mainbody,.manage #mainbody,.jobOpportunities #mainbody,.organizer #mainbody,.newexpense #mainbody,.clockinout_notifications #mainbody,.cico-notifications #mainbody").find("table:eq(0),table:eq(1)").removeClass("table table-borderless mb-0");

                $('#mainbody').not(".billhistory #mainbody,.recieve_pay #mainbody,.rpayregister #mainbody,.creditsregister #mainbody,.editconbillpaym #mainbody,.payemppstubview #mainbody,.transactiondetails #mainbody,.mkdeposit #mainbody,.newcompcode #mainbody,.compensation #mainbody,.benefits #mainbody,.profile #mainbody,.availabilitystatus #mainbody,.hismanage #mainbody,.companiesarch #mainbody,.companycommissions #mainbody,.skills_list #mainbody,.listcustomfields #mainbody,.manageassign #mainbody,.managelists #mainbody,.joborder_titles_list #mainbody,.email_setup #mainbody,.aca_info #mainbody,.burdentypeslist #mainbody,.managerates #mainbody,.shiftmanagement #mainbody,.managecommissions #mainbody,.manage #mainbody,.manageeverify #mainbody,.dsManage #mainbody,.paycheckdetails #mainbody,.joborder_titles_archivelist #mainbody,.manageservicetype #mainbody,.checksetup #mainbody,.campaignhis #mainbody,.resconreg1 #mainbody,.resconreg2 #mainbody,.resconreg3 #mainbody,.resconreg4 #mainbody,.resconreg5 #mainbody,.resconreg6 #mainbody,.resconreg7 #mainbody,.resconreg8 #mainbody,.resconreg10 #mainbody,.resconreg11 #mainbody,.resconreg12 #mainbody,.resconreg16 #mainbody,.resconreg17 #mainbody,.paytaxes #mainbody,.personalinfo #mainbody,.dependents #mainbody,.status #mainbody,.profile #mainbody,.cpayroll_setup #mainbody,.expensereport #mainbody,.groups #mainbody,.newgroup #mainbody,.editgroup #mainbody,.companycommissions #mainbody,.addtaxes #mainbody,.akkubirole #mainbody,.setuprules #mainbody,.gigboard #mainbody,.managequestions #mainbody,.deductions #mainbody,.contactinfo #mainbody,.showexpense #mainbody,.editexpenseheet #mainbody,.managecreatebills #mainbody,.managepaybills #mainbody,.rcvbills #mainbody,.showdetails #mainbody,.invoicedeliver #mainbody,.icalendar #mainbody,.newcon #mainbody,.managegroups #mainbody,.newmanage #mainbody,.companies #mainbody,.opportunities #mainbody,.candidates #mainbody,.reqman #mainbody,.campaigns #mainbody,.talentpool #mainbody,.manage #mainbody,.jobOpportunities #mainbody,.organizer #mainbody,.newexpense #mainbody,.clockinout_notifications #mainbody,.cico-notifications #mainbody").find("table:eq(2)").removeAttr("class");

                $('#mainbody').find(".maingridtophedwidth").children(".modcaption").wrap('<div class="d-flex justify-content-center"></div>');

                $('.NewtopKeySearch').not(".newmanage .NewtopKeySearch,.candidates .NewtopKeySearch,.companies .NewtopKeySearch,.opportunities .NewtopKeySearch,.reqman .NewtopKeySearch").find("table:eq(0), table:eq(1)").addClass('table-mp-0');
                $('.adddept').find("table:eq(0), table:eq(1)").addClass('table-mp-0');

                $('.newmanage .NewtopKeySearch,.candidates .NewtopKeySearch,.companies .NewtopKeySearch,.opportunities .NewtopKeySearch,.reqman .NewtopKeySearch').find("table:eq(0)").addClass('w-auto');

                $('.NewtopKeySearch').not(".hismanage .NewtopKeySearch").find("table:eq(0)").children("tbody").children("tr:eq(1)").attr({ valign: "middle" }).children("td:eq(0)").attr({ align: "center" });

                $('.candidates #ActionMenuFixed,.addcompany #ActionMenuFixed,.regmanage #ActionMenuFixed,.newmanage #ActionMenuFixed').find("table").addClass('table-mp-0');



                /* $('[class*=revconreg] #ActionMenuFixed,.addcompany #ActionMenuFixed,[class*=regmanage] #ActionMenuFixed,[class*=editnotes] #ActionMenuFixed,[class*=editchecklist] #ActionMenuFixed,[class*=matchingjobs] #ActionMenuFixed,[class*=editmanage] #ActionMenuFixed,[class*=conreg] #ActionMenuFixed,[class*=edittask] #ActionMenuFixed,[class*=taskdetails] #ActionMenuFixed,[class*=resup] #ActionMenuFixed,[class*=add_multinotes] #ActionMenuFixed,[class*=mailschedule] #ActionMenuFixed,[class*=import] #ActionMenuFixed,[class*=hirevcon] #ActionMenuFixed,[class*=editjoborder] #ActionMenuFixed,.index #ActionMenuFixed,.candidate #ActionMenuFixed,.editdoc #ActionMenuFixed,.editappoint #ActionMenuFixed').find("table").addClass('table-mp-0').find('.maingridbuttonspad').addClass('d-flex justify-content-end'); */

                $('.editappoint,.popupeditappoint').find("#ActionMenuFixed").children("table:eq(0)").addClass('table-borderless');

                $('[class*=editappoint] #ActionMenuFixed,[class*=index] #ActionMenuFixed').addClass('w-100');

                $('.revconreg0 .crmsummary-navtop, .contactsummary .crmsummary-navtop').addClass('py-2');

                $('.add,.edit,.new,.addtask1,.edittask1,.newtask1').find("table:not(table:eq(5))").addClass('table-mp-0');
                $('.createnewgroup').find("table:not(table:eq(3))").addClass('table-mp-0');
                $('.revconreg0, .revconreg2').find("table:eq(0)").addClass('table-mp-0').find("table:eq(0)").addClass('mb-0');
                $('.revconreg0,.addcompany').find(".tab-page").find("table:eq(0)").addClass('table-mp-0');
                $('[class*=revconreg]').children("form").children("div").children("table").addClass('table-mp-0').children("tbody").children("tr").children("td").children("table").addClass('table-mp-0');
                $('[class*=revconreg1]').children("form").children("div").children("div").children("table").addClass('table-mp-0').children("tbody").children("tr").children("td").children("table").addClass('table-mp-0');
                $('[class*=skill_new],[class*=speciality_list],[class*=speciality_new]').children("form").find("table:not(table:eq(5))").addClass('table-mp-0');
                $('[class*=skill_new],[class*=speciality_list],[class*=speciality_new],.add,.edit,.new,.export_crm_searchdata').children("form").find("fieldset").addClass('m-2');

                $('.viewjobs,.createnewgroup,.creategroup,.postjobboards').find("fieldset").addClass('m-2');

                $('[class*=category_selector],[class*=department_selector],[class*=speciality_selector]').find("table:not(table:eq(4))").addClass('table-mp-0');

                $('[class*=editnotes]').children("form").children("div").find("table:eq(0),table:eq(1)").addClass('table-mp-0');

                /* $('.akkenNewPopUp').not(".custom_grid_columns .akkenNewPopUp").find("table:not(table:eq(1),table:eq(2))").addClass('table-mp-0');
                $('.akkenNewPopUp').not(".custom_grid_columns .akkenNewPopUp").find("table").addClass('align-middle');
                $('.akkenNewPopUp').not(".custom_grid_columns .akkenNewPopUp").find('.hfontstyle').addClass("d-inline-block p-2"); */

                $("#grid_form").not('.compose #grid_form,.regmanage #grid_form,.new_pendingtimesheet #grid_form').children("table").addClass("table-mp-0");

                $(".contactsummary,.editmanage").find("table:eq(1),table:eq(2)").addClass("table-mp-0");
                $(".resup,.createjoborder,.akken_ess_select_popup_users,.paycheckdetails").find("table:eq(0)").addClass("table-mp-0");
                /*less than table*/
                $('[class*=matchingjobs],.emailInvoice,.showpreviewtmpl,.aca_email_tmpl,.cust_email_tmpl,.emp_email_tmpl,.timesheet_email_tmpl,.emp_sms_tmpl,.showpreviewsmstmpl,.expensetype,.showdetails').find("table:lt(2)").addClass('table-mp-0');

                $('.createnewgroup,.movetoinq,.creditsregister,.rpayregister,.conreg15,.movetoreqinq,.movetopostinq,.viewarchivecredentials,.closedassign,.openhisrates,.managecat,.companies,.newmanage,.opportunities,.candidates,.reqman').find("table:lt(3)").addClass('table-mp-0');

                $('[class*=compose],[class*=addparticipants],[class*=adddivisions],[class*=editheader],[class*=newheader],[class*=newassign],[class*=deletelocation],[class*=newfolder1],[class=contacts],[class=emplist],[class=choosemember],[class=invoiceall_manage],[class=invoice_tempselection],[class=tinymce_texteditor],[class=assigninvoice],[class=attachments],.newfooter,.editfooter,.showitem,.itemmanage,.creditcard,.jobtitle_popup,.contactshare,.viewdeductions,.viewcompen,.managebillpayterms').find("table:lt(4)").addClass('table-mp-0');

                $('[class*=filter]').find("table:lt(4)").not('table:eq(2)').addClass('table-mp-0');
                $('.subeditexpensesheet').find(".expense_row td").find('table').addClass('table-mp-0');
                $('.subeditexpensesheet').find(".ExpNotesAdd").addClass('mt-2');

                $('[class*=addgroup],[class*=editgroup1],[class*=newgroup1],[class*=viewdet],[class*=postjobboards],[class*=hybridchannels],[class*=addexpense],[class*=newexpense],.editexpense,[class*=addeditcontributions],[class*=addeditdeductions],[class*=addeditgarnishments],[class*=credentials_type_list],[class*=credentials_type_new],[class*=credentials_name_list],[class*=credentials_name_new],[class*=corp_code],[class*=new_update_corpcode],[class*=managelocations],.addcat,.newcat,.editcat,.editassign,.expensetype,.defaultratesinfo,.editclasses,.invoice_preview_new_combined,.invoice_preview_new_consolidated,.invoice_preview_new,.viewcompen,.manage_assignment_rates,.addstatus,.category_list,.category_new,.joborder_titles_new,.openacahistory,.view_aca_history,.addimmig1,.approveassignment').find("table:lt(5)").addClass('table-mp-0');

                $('[class*=sendhotlist]').find("table:lt(8):not(table:eq(0),table:eq(1),table:eq(4))").addClass('table-mp-0');
                $('[class*=campaignlist]').find("table:lt(12):not(table:eq(0),table:eq(2))").addClass('table-mp-0');

                $('.recurrence').find("table:eq(1)").addClass('align-middle');
                $('.newfolder,.availabilitystatus').find("table:eq(4)").addClass('align-middle');
                $('.addfilter,.editfolder,.taskadd').find("table:eq(5)").addClass('align-middle');

                $('.contact,.campaign').find("table:eq(7),table:eq(8)").addClass("align-middle");
                $('[class*=compose]').find("#grid_form").children("table:eq(0)").addClass('align-middle');
                $('.compose').find('.displayTr:eq(1)').find('table').addClass('align-middle');
                $('.addopportunity #tabs-1-1').find("table:eq(0)").addClass('align-middle');
                $('.addafavorite').find("table:eq(6)").addClass('align-middle');
                $('.revconreg17,.revconreg10,.immigstatus').find("table:eq(7)").addClass('align-middle');
                $('.editmanage #opprcommissionRows').closest("table").addClass('align-middle');
                $(".multitimesheet .searchEmpStycky,.new_pendingtimesheet .dateWeekFixed,.pendingtimeintimeout .dateWeekFixed").find("table:eq(0)").addClass('align-middle');
                $(".addexpensetype .dynamicDiv").find("table:eq(0)").addClass('align-middle');
                $('.invoice_timeexp').find("table:eq(10)").addClass("align-middle");
                $('.invoice').find("table:eq(13)").addClass("align-middle");

                $('.revconreg0,.revconreg1,.revconreg2,.revconreg3,.revconreg4,.revconreg5,.revconreg6,.revconreg7,.revconreg8,.revconreg9,.revconreg10,.revconreg11,.revconreg12,.revconreg16,.revconreg19,.revconreg20,.revconreg21,.revconreg22,.revconreg23,.revconreg24,.revconreg25,.revconreg26,.revconreg27,.revconreg28,.revconreg29,.revconreg30,.revconreg31,.revconreg32,.conreg1,.conreg2,.conreg3,.conreg4,.conreg5,.conreg6,.conreg7,.conreg8,.conreg9,.conreg10,.conreg11,.conreg12,.conreg16,.conreg17,.conreg18,.conreg19,.conreg20,.conreg21,.conreg22,.conreg23,.conreg24,.conreg25,.conreg26,.conreg27,.conreg28,.conreg29,.conreg30,.conreg31,.conreg32,.newconreg1,.newconreg2,.newconreg3,.newconreg4,.newconreg5,.newconreg6,.newconreg7,.newconreg8,.newconreg9,.newconreg10,.newconreg11,.newconreg12,.newconreg13,.newconreg14,.newconreg15,.newconreg17,.newconreg18,.newconreg19,.newconreg20,.newconreg21,.newconreg22,.newconreg23,.newconreg24,.newconreg25,.newconreg26,.newconreg27,.newconreg28,.newconreg29,.newconreg30,.movetoinq,.managecat,.redirectassignment,.newfooter,.managelocations,.editfooter,.newfolder1,.createjoborder,.addcat,.newcat,.editcat,.newgroup1,.editgroup1,.addgroup,.editgroup,.emplist,.doreminder,.doremindercss,.createinvoicetemplate,.newcustomer_message,.editheader,.newheader,.editcustomer_message,.createinvoicetemplate_consolidated,.invoiceopt,.invoice_logs,.editvendor,.immigstatus,.immigrationstatus,.addstatus,.deductions,.companycontributions,.company_info,.editpwd,.ansquestion,.newres,.addsourceaccount,.editsourceaccount,.addformfield,.joborder_titles_option_setup,.addap,.editap,.addcrmlocation,.adddept,.manageaccounts,.editdept,.parent_info,.placement,.email_setup,.aca_email_tmpl,.cust_email_tmpl,.emp_email_tmpl,.timesheet_email_tmpl,.inv_email_tmpl,.movetoreqinq,.movetopostinq,.newfolder,.folderlist,.editfolder,.movefolder,.opprmanage,.paygroup,.empconreg1,.addacccompany,.campaignhis,.invoice,.addopportunity,.editOpportunity,.createmanualinvoice,.personalinfo,.addacclocation,.newexpense,.addeditcustomtype').find("table").addClass('align-middle');

                $('.expensereport').find(".CustomTimesheetInput").find(".expense_row").find("table").addClass('align-middle');

                $('.revconreg19,.newconreg13').find("table:eq(8),table:eq(12)").addClass('table-mp-0');

                $('#newpros').find("afontstyle").addClass("d-inline-block ms-1 me-2");
                $('.maremplist').find(".akkenNewPopUpTitle").addClass("p-0");
                $('.editfooter').find(".titleNewPad").children('font').addClass("d-inline-block m-2");
                $('.newheader,.editheader,.newcustomer_message,.editcustomer_message,.newfooter,.editfooter,.tinymce_texteditor').find("font:eq(0)").addClass("d-inline-block m-2");
                $('.invoiceopt').find("font:eq(3)").addClass("d-inline-block m-2");

                $('.edittask').find(".dynamicMsgPad").addClass('table-mp-0');
                $('#sumNav').addClass('d-block');
                $('.company_info,.parent_info,.bill_contact_info').find(".joborderSerchHed:eq(0)").addClass('modal-header bg-dark p-3');
                $('.importp').find(".tab-page").addClass('p-3');
                $('.compose').find("#ShowPreview").find("table").addClass("table-mp-0");
                $('.showshortlist').find("table:eq(1),table:eq(2),table:eq(5),table:eq(8)").addClass("table-mp-0");
                $('.showshortlist').find("#leftmenu").find("table:eq(0)").addClass("table-mp-0");
                $('.newconreg6').find("table:eq(9)").addClass("table-mp-0");
                $('.showdetv').find("table:eq(6),table:eq(9)").addClass("table-mp-0");
                $('.addcompany').find(".manageSubSticky").addClass('p-2');
                /* $(".newconreg6 input[name=city]").closest("table").addClass("table-mp-0 w-auto"); */
                $(".viewhract .modcaption").closest("table").addClass("table-mp-0");
                $(".newmanage #ActionMenuFixed").closest("td").addClass("p-0");
                $('.newcat1').find("table:eq(0),table:eq(2)").addClass("table-mp-0");
                $('.manageaccounts').find("table:eq(0),table:eq(3)").addClass("table-mp-0");
                $('.manageaccounts').find(".akkenNewPopUpTitle td table").addClass("table-mp-0");
                $('.managefolder').find("#navBody table table").addClass('table-mp-0');
                $('.addmessage').find(".customProfile").addClass('table table-borderless');

                $('.createinvoicetemplate_combined,.createinvoicetemplate_consolidated').find(".fontsDiv").addClass('d-flex align-items-center');
                $('.createinvoicetemplate_combined,.createinvoicetemplate_consolidated').find(".fontsDiv .left_div").addClass('d-flex align-items-center gap-2');


                $('.editpayment').find("table:eq(1)").addClass('table table-borderless mb-0');
                $('.addafavorite,.editpayment').find("table:eq(2)").addClass('table table-borderless');
                $('.checkdocument,.editdocument').find("table:eq(3)").addClass('table table-borderless');

                $(".redirectassignment,.multitimesheet .searchEmpStycky,.uom_timesheet .dateWeekFixed,.custom_timesheet .dateWeekFixed,.new_timesheet .dateWeekFixed").closest("table").addClass("table-mp-0");

                $(".new_timesheet .dateWeekFixed,.timeintimeout .dateWeekFixed,.uom_timesheet .dateWeekFixed,.custom_timesheet .dateWeekFixed").find("table").addClass("table-mp-0 align-middle");
                $('.editpayment').find(".modcaption").addClass("d-inline-block mb-2");
                /* $('.showinvoice').find("#main").addClass("m-2").find(".manageSubSticky").addClass("my-3"); */

                $('.timesheet1').find("div:eq(5)").addClass("m-2");
                $('.burdenitemslist,.commissionadd').find("#ActionMenuFixed:eq(0)").addClass("p-3");
                $('.editdept').find("#ActionMenuFixed:eq(0)").addClass("py-2");
                $('.burdenitemslist').find("#newtabheadingtr #ActionMenuFixed:eq(0)").children("table").children("tbody").addClass("w-100");
                $('.burdenitemslist').find(".createburdennewtop").addClass("table-mp-0");

                $('.newconreg27 input[type=button],.createjoborder input[type=button]').addClass('btn btn-dark');
                $('.newconreg27 .ui-dialog-titlebar .ui-button').addClass('bg-white');
                $('.manageaccounts').find('.NewGridTopBg').children('td').addClass('p-0');
                $('.conreg1').find(".csw-auto").addClass('w-auto');
                $('.jobOpportunities #acjbbtnactiv').addClass('btn btn-success');
                $('.organizer #toggleCursor').addClass('bg-gray-200');


                /* $('.newcon .customlocation').find("table:eq(0)").addClass("table-mp-0"); */

                $('.addusers,.popupeditappoint,.paydata').find("#grid_form").find("table:eq(0)").removeClass('table-mp-0');
                $('.manage_pay_rates,.showdetails,.timesheet1,.invoice,.invoice_timeexp,.editinvoice_timeexp,.addfolder,.movefolder,.contactsummary .contSmryDefRates,.credentials_type_list,.newmanage .NewtopKeySearch,.candidates .NewtopKeySearch,.companies .NewtopKeySearch,.opportunities .NewtopKeySearch,.reqman .NewtopKeySearch,.cico_rejected_showdetails,.cico_timesheet_edit').find("table:eq(0)").removeClass('table-mp-0');
                $('.selectprintoptions,.newconreg19,.home,.addtaxes').find("table:eq(0),table:eq(1)").removeClass('table-mp-0');
                $('.newexpense,.addtaxes,.hismanage').find("table:eq(2)").removeClass('table-mp-0');
                $('.editassign').find("#rightflt").removeAttr('style');
                $('.editinvoice_timeexp').removeAttr('style');
                $('.index .documentManagerPad,.new_timesheet #MainTable,.multitimesheet #MainTable,.uom_timesheet #MainTable,.custom_timesheet #MainTable').removeClass('table-mp-0');
                $('.createnewgroup,.newwebfolder').find("table:eq(3)").removeClass('table-mp-0');
                $('.invoicedeliver,.invoicehis,.w4,.contactinfo,.deductions').find("table:eq(4)").removeClass('table-mp-0');
                $('.mkdeposit,.deductions,.contactinfo').find("table:eq(5)").removeClass('table-mp-0');
                $('.newconreg6').find("table:eq(7)").removeClass("table-mp-0");
                $('.newconreg13').find("#crm-joborder-Tablehours").removeClass("table-mp-0");
                $('.addacclocation').find("#state").removeAttr('style').closest('td').removeAttr('width');
                $(".editexpenseheet").find(".CustomTimesheetInput").removeClass("table-borderless");
                $(".organizer .ssminical").find("table:eq(0)").addClass('table-mp-0').removeClass("table-borderless");



                /* $('.dept_deactive_win,.deletelocation,.editoroptions,.emailsetup,.filter,.campaign,.createjoborder,.movetoinq,.newgroup,.editcontact,.taskupdateform,.inbox,.suggest,.ask,.choosemember,.review,.showdetails,.multitimesheet,.doreminder,.doremindercss,.editassign,.newexpense,.empexpenses,.subeditexpensesheet,.empexpenseshis,.newcat,.header,.customer_message,.invoice_preview_new_combined,.invoice_preview_new_consolidated,.invoice_preview_new,.assigninvoice,.invoice_timeexp,.createmanualinvoice,.invoiceopt,.creditsregister,.addacccompany,.creditdetails,.showdetv,.editconbillpaym,.timesheets,.show,.payemppstubview,.paidliabilities,.showreceivepay,.transactiondetails,.statsetup,.newcompcode,.jobs1,.newprofile,.expensereport,.approveex,.history').find(".multiTimeSticky").removeClass("pt-3"); */

                $('.newcon,.editcon,.newnews,.editnews,.conreg2,.conreg3,.conreg4,.conreg5,.conreg6,.conreg7,.conreg8,.conreg9,.conreg14,.conreg16,.conreg17,.conreg10,.conreg11,.conreg19,.conreg20,.conreg22,.conreg23,.conreg24,.conreg25,.conreg26,.conreg27,.conreg28,.conreg29,.conreg30,.conreg31,.conreg32,.newconreg1,.newconreg2,.newconreg3,.newconreg4,.newconreg5,.newconreg6,.newconreg7,.newconreg8,.newconreg9,.newconreg10,.newconreg11,.newconreg12,.newconreg13,.newconreg14,.newconreg15,.newconreg17,.newconreg18,.newconreg19,.newconreg20,.newconreg21,.newconreg22,.newconreg23,.newconreg24,.newconreg25,.newconreg26,.newconreg27,.newconreg28,.newconreg29,.newconreg30,.extmail,.contact,.eeditcontact,.hisreviewmanage,.regmanage,.addcompany,.newassign,.editchecklist,.hisconreg1,.aca_emp_info,.newwebfolder,.creditcard,.editdept,.addcrmlocation,.suggest,.address,.editcontact,.hisaddress,.groups,.newgroup,.addacccompany,.addfilter,.newfolder,.folderlist,.editfolder,.viewaddrbookcontact,.alljoborders,.showcampaignlist,.resup,.addtaxes,.discountadd,.addform,.editopportunity').find(".multiTimeSticky").addClass("py-2");



                $('.newexpense,.empexpenses,.subeditexpensesheet,.empexpenseshis,.transferfunds').find(".maingridbuttonspad").removeClass('justify-content-end');
                $('.addrule,.editrule,.addaccount,.companysummary #mntcmnt3,.companysummary #mntcmnt4,.companysummary #mntcmnt5,.companysummary #mntcmnt6,.contactsummary #mntcmnt3,.jobsummary #mntcmnt3,.jobsummary #mntcmnt5,.jobsummary #mntcmnt4').find("table:eq(0)").removeClass('table-mp-0');
                $('.addexpensetype,.managebillpayterms,.rpayregister,.showpdata').find("table:eq(1)").removeClass('table-mp-0');
                $('.subeditexpensesheet,.vimanage,.creditsregister').find("table:eq(2)").removeClass('table-mp-0');
                $('.suggest').find("table:eq(3)").removeClass('table-mp-0');
                $('.newcat').find("table:eq(1), table:eq(4)").removeClass('table-mp-0');
                $('.showexpensedet').find("table:eq(3)").removeClass('table table-borderless mb-0');
                $('.rcvbills,.billhistory,.billpayregister,.ibreferral_mngmt_home').find("table:eq(4)").removeClass('table table-borderless mb-0 table-mp-0');
                $('.editdocument,.check_docdet,.docdetails,.checkdocument,.movedocument').find(".defaultTopRange").removeClass("table-mp-0");
                $('.CustomTimesheetTh').removeClass('table-borderless align-middle');
                $('.cs-350').find(".select2-container").css("width", "350px");
                /* $('.cs-150').find(".select2-container").css("width", "150px"); */
                $('.showinvoice .tdborder6').closest("table").removeClass('table-borderless');
                $('.inovicetableborder').closest("table").removeClass('table-borderless');
                $('.showtimeintimeout').find("table:eq(5)").removeClass('table-borderless');
                $('.resumereviewf .custmzScroll').find("table:eq(0)").removeClass('table-mp-0');
                $('.newexpense').find(".CustomTimesheetInput").removeClass('table-mp-0');
                $('.jobOpportunities').find("#essjobgrid").removeClass('table-mp-0');



                /* var selectfld = $('form-select').not('[class*=conreg]'); */
                /* var selectfld = $('select');
                $(selectfld).each(function() {
                	if(!$(this).parent().hasClass("input-group") && !$(this).parent().is('[class*=col]')){
                		$(this).css({"display": "inline-block", "width":"250px"});
                	}
                }); */

                $(window).on("load", function() {
                    try {

                        $('.joborder_titles_new,.paybatch').find(".ms-parent").removeAttr('style');
                        $('.regmanage').find(".multiple-select:eq(1)").removeAttr('style');
                        $('.managelists').find('table:eq(2),table:eq(4)').removeClass('table-mp-0');
                        $('.custom_timesheet').find('.header-fixed').css({ 'top': '144' });
                        $(".listMenuRContent .tbldata").find('table:eq(0)').addClass("table table-borderless");
                        $(".manageservicetype .active-scroll-data .active-column-3 span").removeAttr('style');

                        var tabRowCnt = $('.tab-row').children().length;
                        if (tabRowCnt < 1) {
                            $('.tab-row').hide();
                        }

                        var chkFlg = $(".active-scroll-top").children("div:eq(0)").find(".container-chk").length;
                        if (chkFlg == 0) {
                            //$(".active-scroll-search").children("div:eq(0)").hide();
                        }

                        $(".showexpensedet").html(function(i, html) {
                            return html.replace(/&nbsp;/g, '');
                        });

                        setTimeout(colmnWid, 500);
                    } catch (e) {
                        console.log(e.toString());
                    }
                });
            }
        } catch (e) {
            console.log(e.toString());
        }
    })(jQuery);
});


try {
    if (exflg) {
        document.getElementById("vidjs").remove();
    }
} catch (e) {

}

function getLoadCss() {

    $(".resup").find("#newpros").find("table").addClass('table table-borderless');

    $.each($('input,textarea'), function(k) {

        if ($(this).attr('type') != "hidden" && $(this).attr('type') != "checkbox" && $(this).attr('type') != "radio" && $(this).attr('type') != "button") {
            $(this).addClass("form-control");
        } else {
            if ($(this).attr('type') == "checkbox" || $(this).attr('type') == "radio")
                $(this).addClass("form-check-input");
            else
                $(this).addClass("form-control");
        }
    });
}

function colmnWid() {
    try {
        var colWd = $('.active-templates-header').not(".showreceivepay .active-templates-header,.banking .active-templates-header,.everifyemps .active-templates-header,.reasoncodes .active-templates-header,.displaycustomtypes .active-templates-header,.empfaxhis .active-templates-header,.available_credits_popup .active-templates-header,.candidatesarch .active-templates-header,.companies .active-templates-header,.viewcampaign .active-templates-header").last().attr("id").split(":");
        var headrlen = $('.active-scroll-search .active-column-' + colWd[1] + ':eq(0)').find("a").length;
        if (headrlen > 0) {
            $('.active-scroll-top .active-column-' + colWd[1]).css("width", "120px");
            $('.active-scroll-search .active-column-' + colWd[1]).css("width", "120px");
        }
    } catch (e) {}
}

/*theme code*/
function setCurrntThem(selMode) {

    switch (selMode) {
        case "dark-theme":
            $('#darkModSwtch').attr('checked', true);
            $('#lightModSwtch').attr('checked', false);
            try {
                if ($('body').hasClass('light-theme')) {
                    $('body').removeClass('light-theme');
                } else if ($('body').hasClass('default-theme')) {
                    $('body').removeClass('default-theme');
                }
            } catch (e) {}
            break;
        case "light-theme":
            $('#darkModSwtch').attr('checked', false);
            $('#lightModSwtch').attr('checked', true);
            try {
                if ($('body').hasClass('dark-theme')) {
                    $('body').removeClass('dark-theme');
                } else if ($('body').hasClass('default-theme')) {
                    $('body').removeClass('default-theme');
                }
            } catch (e) {}
            break;
        default:
            $('#darkModSwtch').attr('checked', false);
            $('#lightModSwtch').attr('checked', false);
            try {
                if ($('body').hasClass('dark-theme')) {
                    $('body').removeClass('dark-theme');
                } else if ($('body').hasClass('light-theme')) {
                    $('body').removeClass('light-theme');
                }
            } catch (e) {}

    }

    document.body.classList.add(selMode);
    if (typeof(Storage) !== "undefined") {
        localStorage.setItem("currentTheme", selMode);
    }

}
/*theme code*/