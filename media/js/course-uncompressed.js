/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

/**
 * Weeklyfrequency custom field validation
 *
 * Inspired by: Joomla validate.js
 *
 * @package		Joomla.Framework
 * @subpackage	Custom field Weeklyfrequency
 */
var Weeklyfrequency = (function($) 
{
	
	var handleResponse = function(state, $el, labelref, message) 
	{
		// Set the element and its label (if exists) invalidtime state
		if (state === false) 
		{
			$el.addClass('invalidtime').attr('aria-invalid', 'true');
			if (labelref) 
			{
				$(labelref).addClass('invalidtime').attr('aria-invalid', 'true');
				if (message)
					$(labelref).attr('data-error-message', message);
			}
		} 
		else 
		{
			$el.removeClass('invalidtime').attr('aria-invalid', 'false');
			if (labelref) 
			{
				$(labelref).removeClass('invalidtime').attr('aria-invalid', 'false').attr('data-error-message', '');
				$(labelref).attr('data-error-message', '');
			}
		}
	};

	var validate = function(el) 
	{		
		var $el = $(el), tr, labelref, starttime = 0, endtime = 0, message = '', isvalidformat = true;
		
		tr = $el.closest('tr');
		labelref = tr.find('.day-label');
		
		tr.find('.validate-time').each(function(index, time)
		{
			var $time = $(time);
			handleResponse(true, $time, labelref, '');
			
			if (!$time.val().test("^[0-9]{2}:[0-9]{2}$")) 
			{
				if ($time.attr('id').test("^start_time_[0-9]{1}$"))
					message = Joomla.JText._('COM_COOLTOURAMAN_START_TIME_HOUR_AND_MINUTES_FORMAT_ERROR');
				else if ($time.attr('id').test("^end_time_[0-9]{1}$"))
					message = Joomla.JText._('COM_COOLTOURAMAN_END_TIME_HOUR_AND_MINUTES_FORMAT_ERROR');
				
				handleResponse(false, $time, labelref, message);
				isvalidformat = false;
				return false;
			}
			
			if ($time.attr('id').test("^start_time_[0-9]{1}$"))
				starttime = new Date('1970-01-01T' + $time.val() + 'Z').getTime() / 1000;
			if ($time.attr('id').test("^end_time_[0-9]{1}$"))
				endtime = new Date('1970-01-01T' + $time.val() + 'Z').getTime() / 1000;
		});
		
		
		if (!isvalidformat)
			return false;
		
		if (starttime > endtime)
		{
			message = Joomla.JText._('COM_COOLTOURAMAN_START_TIME_GT_END_TIME_FORMAT_ERROR');
			handleResponse(false, $el, labelref, message);
			return false;
		}
		
		if ((endtime - starttime) < 3600)
		{
			message = Joomla.JText._('COM_COOLTOURAMAN_LESSON_DURATION_TO_SHORT_ERROR');
			handleResponse(false, $el, labelref, message);
			return false;
		}
		
		return true;
	};
	
	var isValid = function(table) 
	{
		var valid = 0, $table = $(table), i, checked = 0, isValid = true, message, controlgroup, labelref;
		
		message = Joomla.JText._('COM_COOLTOURAMAN_SELECT_WEEKLY_ERROR');
		controlgroup = $table.closest('.control-group');
		
		if (controlgroup !== 'undefined')
			labelref =  controlgroup.find('label');
		
		// Validate form fields
		$table.find('.validate-day').each(function(index, el)
		{
			if (($(el).attr('type') === 'checkbox' && $(el).is(':checked')))
			{
				checked++;
				
				if (validate(el) !== false)
					valid++;
					
			}
		});
			
		handleResponse(true, $table, labelref, '');
		
		if (checked === 0)
		{
			handleResponse(false, $table, labelref, message);
			isValid = false;
		}
		
		if (checked !== valid)
			isValid = false;
		
		
		
		if (!isValid) 
		{
			
			var message, errors, error;
			message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
			
			if (checked > 0)
				errors = $("td.invalidtime");
			else
				errors = $("label.invalidtime");
			error = {};
			error.error = [];
			for ( i = 0; i < errors.length; i++) 
			{
				var label = $(errors[i]).text();
				var dataerrormsg = $(errors[i]).attr('data-error-message');
				if (label !== 'undefined') 
				{
					error.error[i] = message + label.replace("*", "")+"&#160"+dataerrormsg;
				}
			}
			Joomla.renderMessages(error);
		}
		
		return isValid;
 	};
 
    // Public API
    return {
		isValid : isValid
    };
});

//Show error in the new course wizard page in the joomla style
function showError ()
{
	var message, errors, error;
	message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
	errors = jQuery("label.invalid");
	error = {};
	error.error = [];
	for ( i = 0; i < errors.length; i++) 
	{
		var label = jQuery(errors[i]).text();
		if (label !== 'undefined') {
			error.error[i] = message + label.replace("*", "");
		}
	}
	
	Joomla.renderMessages(error);
}

if (jQuery) 
{
	jQuery(document).ready(function($) 
	{
		/**** START ATTENDANCE REGISTER ***/	
		//Move attendance register page next or previous
		jQuery("a.indicator").click(function()
		{
			var currentIndex = 0;
			var nextIndex = 0;
			var nextPage = null;
			var currentPage = jQuery(".wrapper").find('div[id^="page-"].current');
			var direction = jQuery(this).attr("id");
			
			if (currentPage.length > 0 && jQuery(currentPage).attr("id") != 'undefined')
			{
				currentIndex = parseInt(jQuery(currentPage).attr("id").match(/\d+/g)[0]);
			}
				
			if (currentIndex)
			{
				if (direction === "next")
					nextIndex = currentIndex + 1;
				
				if (direction === "previous")
					nextIndex = currentIndex - 1;
				
				nextPage = jQuery("#page-" + nextIndex);
				if(nextPage.length)
				{
					jQuery(currentPage).removeClass("current");
					jQuery("#page-" + nextIndex).addClass("current");
					
					if (jQuery(nextPage).is(".page:last"))
					{
						jQuery("#next").addClass("disabled");
						jQuery("#previous").removeClass("disabled");
					}
					else if (jQuery(nextPage).is(".page:first"))
					{
						jQuery("#previous").addClass("disabled");
						jQuery("#next").removeClass("disabled");
					}
				}
			}
		});
				
		//Change in attendance register the presence of teacher
		jQuery('select[id^="jformattendanceregisterteacherattendances"]').change(function () 
		{
			var id = jQuery(this).attr("id");
			
			//Get date (dd-mm-yyyy) part from id element
			var re = /(\d{2})-(\d{2})-(\d{4})/;
			var parts = id.match(re);
			//Format date for the teacher row
			var date = parts[1] + "-" + parts[2] + "-" + parts[3];
			//Format date2 for the student row
			var date2 = parts[1] + "_" + parts[2] + "_" + parts[3];
			
			//Get month short name for the calendar lesson
			var objDate = new Date(parts[3] + "/" + parts[2] + "/" + parts[1]);
			var shortMonth = objDate.getShortMonthName().toUpperCase();
			
			id = "jformattendanceregisterteacherattendances"+date;
			var label = id + "-lbl";
			var td = id + "-td";

			if (parseInt(jQuery(this).val()) == 0)//Teacher is absent
			{
				
				jQuery("#"+td).css("background-color", "red");
				jQuery("#"+label).html("N/A");
				jQuery("#"+id+"-substitute").val("N/A");
				//Each student is absent
				jQuery('input:regex(id,jform_attendanceregister__students__[1-9]__attendances__'+date2+')').each(function()
				{
					if(jQuery(this).val() == 1)
					{
						jQuery('label[for="'+jQuery(this).attr("id")+'"]').removeClass("active btn-success");
						jQuery(this).attr('checked', false);
					}	
					else if (jQuery(this).val() == 0)
					{
						jQuery('label[for="'+jQuery(this).attr("id")+'"]').addClass("active btn-danger");
						jQuery(this).attr('checked', true);
					}
				});
				
				//unchecked the day from the calendar lesson and advise that we need to add the new one
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][checked]"]').attr('checked', false);
				//Set this day like a holiday
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][holiday]"]').val(1);
				//Set the holiday title 
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][holidaytitle]"]').val(Joomla.JText._('COM_COOLTOURAMAN_TEACHER_ABSENT'));
				jQuery('#jformcalendaroflessonsspanday'+date).addClass("hasTooltip").css("color", "red").attr("data-original-title", Joomla.JText._('COM_COOLTOURAMAN_TEACHER_ABSENT'));
				jQuery('#jformcalendaroflessonsspanduration'+date).css("color", "red");
				alert(Joomla.JText._('COM_COOLTOURAMAN_TEACHER_ABSENT_WARNING_MSG'));
			}
			else if (parseInt(jQuery(this).val()) == 1)//Teacher is present
			{
				
				jQuery("#"+label).html("N/A");
				jQuery("#"+td).css("background-color", "white");
				jQuery("#"+id+"-substitute").val("N/A");
				//Each student is present
				jQuery('input:regex(id,jform_attendanceregister__students__[1-9]__attendances__'+date2+')').each(function()
				{
					if(jQuery(this).val() == 1)
					{
						jQuery('label[for="'+jQuery(this).attr("id")+'"]').addClass("active btn-success");
						jQuery(this).attr('checked', true);
					}	
					else if (jQuery(this).val() == 0)
					{
						jQuery('label[for="'+jQuery(this).attr("id")+'"]').removeClass("active btn-danger");
						jQuery(this).attr('checked', false);
					}
				});
				//checked the day from the calendar lesson
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][checked]"]').attr('checked', true);
				//Set this day like a unholiday
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][holiday]"]').val(0);
				//Set the holiday title empty
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][holidaytitle]"]').val('');
				jQuery('#jformcalendaroflessonsspanday'+date).css("color", "black");
				jQuery('#jformcalendaroflessonsspanduration'+date).css("color", "black");
			}
			else if (parseInt(jQuery(this).val()) == 2)//Teacher substitute
			{
				jQuery("#"+td).css("background-color", "white");
				//Each student is present
				jQuery('input:regex(id,jform_attendanceregister__students__[1-9]__attendances__'+date2+')').each(function()
				{
					if(jQuery(this).val() == 1)
					{
						jQuery('label[for="'+jQuery(this).attr("id")+'"]').addClass("active btn-success");
						jQuery(this).attr('checked', true);
					}	
					else if (jQuery(this).val() == 0)
					{
						jQuery('label[for="'+jQuery(this).attr("id")+'"]').removeClass("active btn-danger");
						jQuery(this).attr('checked', false);
					}
				});
				//checked the day from the calendar lesson
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][checked]"]').attr('checked', true);
				//Set this day like a unholiday
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][holiday]"]').val(0);
				//Set the holiday title empty
				jQuery('input[name="jform[calendaroflessons]['+shortMonth+'][days]['+date+'][holidaytitle]"]').val('');
				jQuery('#jformcalendaroflessonsspanday'+date).css("color", "black");
				jQuery('#jformcalendaroflessonsspanduration'+date).css("color", "black");
				
				var url = "index.php?option=com_cooltouraman&view=teachers&layout=modal&tmpl=component&field=jform_teacher_name&elemid="+id;
				SqueezeBox.initialize();
				SqueezeBox.open(url, {handler: 'iframe', size: {x: 800, y: 500}});
			}
		});
		/**** END ATTENDANCE REGISTER ***/
		//If the course is publish disabled durantion and start date
		if (courseState)
		{
			jQuery("#jform_duration_in_hours").attr('disabled', true).trigger("liszt:updated");
			jQuery("#jform_start_date").attr('readonly', true);
			jQuery("#jform_start_date_img").attr('disabled', true);
		}
		else
		{
			jQuery("#jform_duration_in_hours").attr('disabled', false).trigger("liszt:updated");
			jQuery("#jform_start_date").attr('readonly', false);
			jQuery("#jform_start_date_img").attr('disabled', false);
		}
		//weeklyfrequency clear each info when uncheck day of week
		$('input[id^="day_of_week_"]').change(function () 
		{
			var $id = $(this).attr("id");
			if(!$(this).is(":checked")) 
			{
				$('input[name="jform[weeklyfrequency]['+$id+'][start_time]"]').val("");
				$('input[name="jform[weeklyfrequency]['+$id+'][end_time]"]').val("");
			}
			else
			{
				$('input[name="jform[weeklyfrequency]['+$id+'][start_time]"]').focus();
			}
		});
		
		//Create a new course whit wizard (com_cooltouraman/views/course/tmpl/new.php)
		if(typeof $.fn.bootstrapWizard !== 'undefined')
		{
			jQuery("#rootwizard").bootstrapWizard({onNext: function(tab, navigation, index) 
			{
				if(index==1) 
				{				
					if(!document.formvalidator.validate(document.getElementById("jform_template_id"))) 
					{
						showError();
						return false;
					}
					
					Joomla.renderMessages('');
				}
				if(index==2) 
				{
					if(!document.formvalidator.validate(document.getElementById("jform_teacher_id"))) 
					{
						showError();
						return false;
					}
					
					Joomla.renderMessages('');
				}
				if(index==3) 
				{
					if(!document.formvalidator.validate(document.getElementById("jform_start_date"))) 
					{
						showError();
						return false;
					}
					
					Joomla.renderMessages('');
				}
				if(index==4) 
				{	
					if (!document.weeklyfrequency.isValid(document.getElementById("weeklyfrequency-table")))
						return false;
					else
						Joomla.submitbutton("course.savenew");
				}

			},onTabClick: function(tab, navigation, index) {
				return false;
			},onTabShow: function(tab, navigation, index) {
				var $total = navigation.find("li").length;
				var $current = index+1;
				if ($current == $total) 
				{
					jQuery(".next").removeClass("disabled");
					jQuery(".next").html('<a href="#">Salva</a>');
				}
				else
				{
					jQuery(".next").html('<a href="#">Next</a>');
				}
				var $percent = ($current/$total) * 100;
				jQuery("#rootwizard .bar").css({width:$percent+"%"});
			}});
				
			window.prettyPrint && prettyPrint();
		}
	});
	
	document.weeklyfrequency = null;
	document.weeklyfrequency = new Weeklyfrequency(jQuery.noConflict());
}
/**** START ATTENDANCE REGISTER ***/
jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ? 
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^s+|s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}

Date.prototype.monthNames = [
    "January", "February", "March",
    "April", "May", "June",
    "July", "August", "September",
    "October", "November", "December"
];

Date.prototype.getMonthName = function() {
    return this.monthNames[this.getMonth()];
};
Date.prototype.getShortMonthName = function () {
    return this.getMonthName().substr(0, 3);
};

function jSelectTeacher_jform_teacher_name(id, teacher, elemid)
{
	jQuery("#"+elemid+"-lbl").html(teacher);
	jQuery("#"+elemid+"-substitute").val(teacher);
	
	return jModalClose();
}
function jSelectStudent_jform_student_name(id, student)
{
	if(!document.getElementById("studentId_"+id))
	{
		var pages = jQuery('div[id^="page-"]');
	
		jQuery('div[id^="page-"]').each(function()
		{
			var oldRowData = jQuery(this).find("tr.row0");
			var theTable = jQuery(this).find(".table");
			var rowCount = parseInt(jQuery(this).find(".table >tbody >tr").length);
			var rowData = oldRowData.clone();
			rowData.removeClass("row0").addClass("row" + rowCount);
			rowData.attr("id", "studentId_"+id);
			
			rowData.find("input").each(function()
			{
				var name = jQuery(this).attr("name").replace(/0/, rowCount);
				jQuery(this).attr("name", name);
				
				if(name.match(/name\b/))
					jQuery(this).val(student);
				
				if(name.match(/id\b/))
					jQuery(this).val(id);
			});
			
			var firstTdHtml = rowData.find("td:first").html();
			firstTdHtml = firstTdHtml.replace(/id=##/, 'id='+id);
			firstTdHtml = firstTdHtml.replace(/##/, student);
			rowData.find("td:first").html(firstTdHtml);
			
			rowData.find("td#trashBnt").each(function()
			{
				firstTdHtml = jQuery(this).html();
				firstTdHtml = firstTdHtml.replace(/##/g, id);
				jQuery(this).html(firstTdHtml);
			});
			
			rowData.removeAttr("style");
			theTable.append(rowData);
			
		});
		
		//Add entry in accounting TAB
		addAccountingRow(id, student);
	}
	
	return jModalClose();
}

function deleteRow(el, id)
{
	var row = jQuery(el).closest("tr");
	var classToHide = row.attr("class").match(/^row([0-9])$\b/);
	if (classToHide)
	{
		jQuery("."+classToHide[0]).remove();
		jQuery('#accounting_studentId_'+id).remove();
	}
}
/**** END ATTENDANCE REGISTER ***/

/**** START ACCOUNTING REGISTER ***/
function addAccountingRow(id, student)
{
	var theTable = jQuery('#accounting_table');
	if(theTable.length > 0)
	{
		var priceBase = jQuery('#jform_prices_price_base').val();
		var rowCount = parseInt(theTable.find(">tbody >tr").length);
		var oldRowData = jQuery('#accounting_studentId_29061793');
		var rowData = oldRowData.clone();
		var rowHtml = rowData.html();
		rowHtml = rowHtml.replace(/29061793/g, id);
		rowHtml = rowHtml.replace(/%name%/g, student.trim());
		rowHtml = rowHtml.replace(/%price%/g, priceBase);
		rowHtml = rowHtml.replace(/%balance%/g, priceBase);
		rowData.html(rowHtml);
		rowData.find('.student_balance'+id).css('color', 'red');
		rowData.attr("id", "accounting_studentId_"+id);
		
		rowData.removeAttr("style");
		theTable.append(rowData);
		
		var calendarSetup = 'jQuery(document).ready(function($){'
							 +'Calendar.setup({inputField:"jform_accounting_students_student'+id+'_registration",ifFormat: "%Y-%m-%d",button:"jform_accounting_students_student'+id+'_registration_img",align: "Tl",singleClick: true,firstDay: 1});'
							 +'Calendar.setup({inputField:"jform_accounting_students_student'+id+'_earlyend",ifFormat: "%Y-%m-%d",button:"jform_accounting_students_student'+id+'_earlyend_img",align: "Tl",singleClick: true,firstDay: 1});'
							 +'});';
		jQuery('<script>').attr('type', 'text/javascript').text(calendarSetup).appendTo('head');
		
	}
}

function calcBalanceValue(id)
{
	var price = parseInt(jQuery('#jform_accounting_students_student'+id+'_price').val());
	var discount = parseInt(jQuery('#jform_accounting_students_student'+id+'_discount').val());
	var paymentAmount = 0; 
	jQuery('.payment_amount'+id).each(function () {paymentAmount += parseInt(jQuery(this).val());});
	
	if (discount > 0)
		price = discount;
	
	price -= paymentAmount;
	
	jQuery('.student_balance'+id).html(price);
	jQuery('input[name="jform[accounting][students][student'+id+'][balance]"]').val(price);
	
	if (price === 0)
		jQuery('.student_balance'+id).css('color', 'green');
	
}

function addPaymentRow(studentId)
{
	var paymentTable = jQuery('#accounting_payment_table_student_id_'+studentId);
	var lastRow = paymentTable.find("tr").last();
	var rowClass = 'row0';
	var rowId = 'payment_'+studentId+'_0';
	var paymentId = 0;
	var dateId = 'jform_accounting_students_student'+studentId+'_payments_payment0_date';
	var amountId = 'jform_accounting_students_student'+studentId+'_payments_payment0_amount';
	var html = '';
	if (lastRow.length > 0)
	{
		var re = new RegExp('payment_'+studentId+'_'+"(\\d{1})"); 
		
		rowClass = lastRow.attr("class").match(/^row(\d{1})$\b/);
		if (rowClass.length > 0)
		{
			rowClass = parseInt(rowClass[1]);
			
			if(rowClass === 1)
				rowClass = 'row0';
			else
				rowClass = 'row1';
		}
		
		rowId = lastRow.attr("id").match(re);
		if (rowId.length > 0)
		{
			paymentId = parseInt(rowId[1]) + 1;
			rowId = 'payment_'+studentId+'_'+ paymentId;
			dateId = 'jform_accounting_students_student'+studentId+'_payments_payment'+ paymentId+'_date';
			amountId = 'jform_accounting_students_student'+studentId+'_payments_payment'+ paymentId+'_amount';
		}
	}
	
	html += '<tr class="'+rowClass+'" id="'+rowId+'">'+"\n\r";
	html += '<td>'+"\n\r";
	html += '<div class="input-append">'+"\n\r";
	html += '<input type="text" name="jform[accounting][students][student'+studentId+'][payments][payment'+paymentId+'][date]" id="'+dateId+'" class="input-small" data-original-title>'+"\n\r";
	html += '<button type="button" class="btn" id="'+dateId+'_img"><span class="icon-calendar"></span></button>'+"\n\r";
	html += '</div>'+"\n\r";
	html += '</td>'+"\n\r";
	html += '<td>'+"\n\r";
	html += '<input type="text" name="jform[accounting][students][student'+studentId+'][payments][payment'+paymentId+'][amount]" id="'+amountId+'" onchange="calcBalanceValue('+studentId+')" class="input-micro payment_amount'+studentId+'">'+"\n\r";
	html += '</td>'+"\n\r";
	html += '<td width="1%" align="center">'+"\n\r";
	html += '<a class="btn btn-micro" href="javascript:void(0);" onclick="return deletePaymentRow(this, '+studentId+');"><span class="icon-trash"></span></a>'+"\n\r";
	html += '</td>'+"\n\r";
	html += '</tr>'+"\n\r";
	
	paymentTable.append(html);
	
	var calendarSetup = 'jQuery(document).ready(function($){Calendar.setup({inputField:"'+dateId+'",ifFormat: "%Y-%m-%d",button:"'+dateId+'_img",align: "Tl",singleClick: true,firstDay: 1});});';
	jQuery('<script>').attr('type', 'text/javascript').text(calendarSetup).appendTo('head');
}

function deletePaymentRow(el, id)
{
	var row = jQuery(el).closest("tr");
	row.remove();
	
	calcBalanceValue(id);
}

/**** END ACCOUNTING REGISTER ***/