$(function(){

    refreshWidget("rotate_keys_widget");
    refreshWidget("decode_data_widget", "InitDecodeDataWidget");
    refreshWidget("encode_data_widget", "InitEncodeDataWidget");

    $(document).on('click', '#clear_decode_textarea', function(e) {
        ClearDecodeTextarea(this, e);
    });
    $(document).on('click', '#submit_decode_textarea', function(e) {
        DecodeTextArea(this, e);
    });
    $(document).on('click', '#encode_data_btn', function(e) {
        ShowEncodeWidget(this, e);
    });
    $(document).on('click', '#clear_encode_textarea', function(e) {
        ClearEncodeTextarea(this, e);
    });
    $(document).on('click', '#submit_encode_textarea', function(e) {
        EncodeTextArea(this, e);
    });
    $(document).on('click', '#decode_data_btn', function(e) {
        ShowDecodeWidget(this, e);
    });
    $(document).on('click', '#rotate_refresh_link_btn', function(e) {
        refreshWidget("rotate_keys_widget");
    });
    $(document).on('click', '#rotate_link_btn', function(e) {
        RotateKeys(this,e);
    });

});
function ShowDecodeWidget(click_obj, e)
{
    e.preventDefault();
    $("#decode_data_widget").find(".card-box:first").removeClass("hidden");
    $("#encode_data_widget").find(".card-box:first").addClass("hidden");
}
function ShowEncodeWidget(click_obj, e)
{
    e.preventDefault();
    $("#decode_data_widget").find(".card-box:first").addClass("hidden");
    $("#encode_data_widget").find(".card-box:first").removeClass("hidden");
}
function InitDecodeDataWidget()
{
    $('#encrypted_entity').select2();
}
function InitEncodeDataWidget()
{
    $('#encrypted_entity').select2();
}
function ClearDecodeTextarea(click_obj, e)
{
    e.preventDefault();

    var textarea = $('#decode_textarea');
    $(textarea).val("");
}
function DecodeTextArea(click_obj, e)
{
    e.preventDefault();

    var form = $("#decode_data_form");
    var encrypted_entity = $("#encrypted_entity");
    var textarea = $('#decode_textarea');

    var url = $(form).attr("action");
    var params = {};
    params['url'] = url;
    params['ajax'] = 1;
    params['data'] = $(textarea).val();
    params['encrypted_entity'] = $(encrypted_entity).val();


    $.post( url, securePostVariables(params) ).done(function( responseHTML )
    {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) return;
        var result = JSON.parse(responseHTML);
        var type = result['type'];
        if ( type === 'danger' )
        {
            var message = result['message'];
            $(textarea).val(message);
        }
        if (type === 'success' )
        {
            var message = result['responseText'];
            $(textarea).val(message);
        }
    });

}
function RotateKeys( click_obj, e)
{
    e.preventDefault();

    var url = $("#rotate_link_btn").attr("href");
    var params = {};
    params['url'] = url;
    params['ajax'] = 1;

    if ( getStringValue(url) != "" )
    {
        $.post( url, securePostVariables(params) ).done(function( responseHTML )
        {
            if ( ! ValidateAjaxResponse(responseHTML, url) ) return;
            var result = JSON.parse(responseHTML);
            var type = result['type'];
            refreshWidget("rotate_keys_widget");
        });
    }
}
function ClearEncodeTextarea(click_obj, e)
{
    e.preventDefault();

    var textarea = $('#encode_textarea');
    $(textarea).val("");
}
function EncodeTextArea(click_obj, e)
{
    e.preventDefault();

    var form = $("#encode_data_form");
    var encrypted_entity = $("#encrypted_entity");
    var textarea = $('#encode_textarea');

    var url = $(form).attr("action");
    var params = {};
    params['url'] = url;
    params['ajax'] = 1;
    params['data'] = $(textarea).val();
    params['encrypted_entity'] = $(encrypted_entity).val();


    $.post( url, securePostVariables(params) ).done(function( responseHTML )
    {
        if ( ! ValidateAjaxResponse(responseHTML, url) ) return;
        var result = JSON.parse(responseHTML);
        var type = result['type'];
        if ( type === 'danger' )
        {
            var message = result['message'];
            $(textarea).val(message);
        }
        if (type === 'success' )
        {
            var message = result['responseText'];
            $(textarea).val(message);
        }
    });

}