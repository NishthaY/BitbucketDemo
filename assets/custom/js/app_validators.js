/*
 * stringsMustMatchValidator
 * This takes in two strings.  They must be the same for
 * this to pass.  The input items can not be empty.
 */
function stringsMustMatchValidator(item1, item2) {

	// The value is the 'confirmation password'.  Bail if we dont' have it.
	if(item1 == null || item1 == "undefined" || item1 == "") return false;


	// Grab the new password and see if it matches the confirmation password.
	if(item2 == null || item2 == "undefined" || item2 == "") return false;

	// If they are not the same, bail.
	if(item1 != item2) return false;

	return true;
}
/*
 * containsLowerCaseLettersValidator
 * This validator will fail if the 'value' passed in does not contain
 * at least one lowercase character.
 */
function containsLowerCaseLettersValidator(value) {

	if(value == null || value == "undefined" || value == "") return false;

	var expression	= new RegExp("[a-z]", "");

	if( expression.test(value) ){
		return true;
	}
	return false;
}
/*
 * containsUperCaseLettersValidator
 * This validator will fail if the 'value' passed in does not contain
 * at least one uppercase character.
 */
function containsUpperCaseLettersValidator(value) {

	if(value == null || value == "undefined" || value == "") return false;

	var expression	= new RegExp("[A-Z]", "");

	if( expression.test(value) ){
		return true;
	}
	return false;
}
/*
 * containsLettersValidator
 * This validator will fail if the input value does not contain
 * a letter at all.
 */
function containsLettersValidator(value) {

	if(value == null || value == "undefined" || value == "") return false;

	var expression	= new RegExp("[A-Za-z]", "");

	if( expression.test(value) ){
		return true;
	}
	return false;
}
/*
 * containsNumbersValidator
 * This validator will fail if 'value' does not contain at least
 * one number.
 */
function containsNumbersValidator(value) {
	if(value == null || value == "undefined" || value == "") return false;
	var expression	= new RegExp("[0-9]", "");
	if( expression.test(value) ){
		return true;
	}
	return false;
}
/*
 * containsSymbolsValidator
 * This validator will fail if one of the following symbols is not found.
 * - | @ # $ % & * / = ? , ; . : _ + ~ ^ = \
 *
 * It should fail of the registered trademark too, but I don't know how to type
 * that character to test it.
 */
function containsSymbolsValidator(value) {
	if(value == null || value == "undefined" || value == "") return false;
	//var symbolTest	= new RegExp("[|!@#$%&*\/=?,;.:\-_+~^¨\\\\]", "");
	var symbolTest	= new RegExp("[-|!@#$%&*\/=?,;.:_+~^¨\\\\]", "");
	if( symbolTest.test(value) ){
		return true;
	}
	return false;
}
function usernameValidator(value) {
    // Empty string should pass. Use the required validator to
    // catch empty if desired.
    if ( getStringValue(value) == "" ) return true;

    var p = {};
    p['ajax'] = 1;
    p['username'] = $.trim(value);

    var url = base_url + "users/validate/username";

    var retval;
    var ajaxOptions = {
        type: 'POST',
        url: url,
        data: securePostVariables(p),
        async : false,
        dataType: "json",
        success: function(data) {
            if (data.validation) {
                //validation passed
                retval = true;
            }
            else {
                //validation failed
                retval = false;
            }
        }
    };
    $.ajax(ajaxOptions);
    return retval;
}
function companynameValidator(value, element) {
    // Empty string should pass. Use the required validator to
    // catch empty if desired.
    if ( getStringValue(value) == "" ) return true;

	// If the input matches the original value, okay.
	var form = $(element).closest("form");
	var form_name = $(form).attr("id");
	var orig = $("#"+form_name+" input[name='company_name_orig']").val();
	if ( value == orig ) return true;

    var p = {};
    p['ajax'] = 1;
    p['company_name'] = $.trim(value);

    var url = base_url + "companies/validate/company";

    var retval;
    var ajaxOptions = {
        type: 'POST',
        url: url,
        data: securePostVariables(p),
        async : false,
        dataType: "json",
        success: function(data) {
            if (data.validation) {
                //validation passed
                retval = true;
            }
            else {
                //validation failed
                retval = false;
            }
        }
    };
    $.ajax(ajaxOptions);
    return retval;
}
function companyparentnameValidator(value, element) {
    // Empty string should pass. Use the required validator to
    // catch empty if desired.
    if ( getStringValue(value) == "" ) return true;

	// If the input matches the original value, okay.
	var form = $(element).closest("form");
	var form_name = $(form).attr("id");
	var orig = $("#"+form_name+" input[name='company_parent_name_orig']").val();
	if ( value == orig ) return true;

    var p = {};
    p['ajax'] = 1;
    p['company_parent_name'] = $.trim(value);

    var url = base_url + "parents/validate/parent";

    var retval;
    var ajaxOptions = {
        type: 'POST',
        url: url,
        data: securePostVariables(p),
        async : false,
        dataType: "json",
        success: function(data) {
            if (data.validation) {
                //validation passed
                retval = true;
            }
            else {
                //validation failed
                retval = false;
            }
        }
    };
    $.ajax(ajaxOptions);
    return retval;
}
function moneyValidator(value, element) {
    //var isValidMoney = /^\d{0,4}(\.\d{0,2})?$/.test(value);
    var isValidMoney = /^[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/.test(value);
    return this.optional(element) || isValidMoney;
}
