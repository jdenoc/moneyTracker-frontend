/**
 * Created by denis on 8/18/14.
 */

var url='../includes/request_data.php?x=';

var accounts = {
    display: function(){
        $.ajax({
            type: 'POST',
            url: url+nocache(),
            data: { type: 'get_account_data' },
            beforeSend:function(){
                $('#account_settings tr').remove();
            },
            success:function(accountData){
                $('#account_settings').append(accountData);
                $('.account_type').each(function(idx, obj){
                    var typeData = accounts.types.initHandler(obj);
                    typeData.accountID = typeData.accountID.replace('account_setting_', '');
                    typeData.typeID = $(obj).prop('id').replace('type_', '');
                    var selectOptions = '';
                    $(typeOptions).each(function(typeIdx, typeObj){
                        selectOptions += '<option value="'+typeObj+'">'+typeObj.substring(0,1).toUpperCase()+typeObj.substring(1)+'</option>'+"\n";
                    });
                    if(typeof types[typeData.accountID] != 'undefined'){
                        $(obj).children('label').children('select').append(selectOptions).val( types[typeData.accountID][typeData.typeID] );
                    }
                });
                loading.end();
            },
            error:function(){
                // TODO - test
                notice.display('danger', 'Error occurred while trying to display information on accounts.');
                loading.end();
            }
        });
    },
    add: function(){
        // TODO - create a new account
        alert('Account Add doesn\'t work yet.');
        notice.display('warning', 'Feature: "Adding new Account", will be coming soon.')
    },
    disable: function(){
        // TODO - disable account
        notice.display('warning', 'Feature: "Disabling Account", will be coming soon.')
    },
    types: {
        tempData:{},
        newButton: "<li class='account_type add_type btn'>Add Account Type</li>\n",
        display: function(){
            var typeData = accounts.types.initHandler($(this)[0]);
            var element = "#"+typeData.accountID+" ul .account_type";
            if($(element).is(':visible')){
                $(element).slideUp();
                $(element+' label input').prop('readonly', true);
                $(element+' label select').prop('disabled', true);
                $(element+' .save_type').hide();
                $(element+' .cancel_type').hide();
                $(element+' .edit_type').show();
                if(!$(element+':last-child').hasClass('add_type')){
                    $(element+':last-child').remove();
                    $("#"+typeData.accountID+" ul").append(accounts.types.newButton);
                }
            } else {
                $(element).slideDown();
            }
        },
        add: function(){
            var typeData = accounts.types.initHandler($(this)[0]);
            $(this).remove();
            var selectOptions = '';
            $(typeOptions).each(function(typeIdx, typeObj){
                selectOptions += '<option value="'+typeObj+'">'+typeObj.substring(0,1).toUpperCase()+typeObj.substring(1)+'</option>'+"\n";
            });
            var display = '<li class="account_type">';
            display += "\t"+'<label>Name:<input type="text" name="type_name" class="form-control" /></label>';
            display += "\t"+'<label>Last Digits:<input type="text" name="last_digits" class="form-control" maxlength="4" /></label>';
            display += "\t"+'<label>Type: <select name="type" class="form-control" >'+selectOptions+'</select></label>';
            display += "\t"+'<button type="button" class="btn btn-default type_button save_type">Save</button>';
            display += "\t"+'<button type="button" class="btn btn-default type_button cancel_type">Cancel</button>';
            display += "</div></li>"+"\n";
            $('#'+typeData.accountID+' ul').append(display);
            var element = "#"+typeData.accountID+" ul li:last-child";
            $(element).show();
            $(element+' .save_type').show();
            $(element+' .cancel_type').show();

        },
        edit: function(){
            var typeData = accounts.types.initHandler($(this)[0]);
            var element = "#"+typeData.typeID;
            $(element+' label input').prop('readonly', false);
            $(element+' label select').prop('disabled', false);
            $(element+' .edit_type').hide();
            $(element+' .save_type').show();
            $(element+' .cancel_type').show();
        },
        cancel: function(){
            var typeData = accounts.types.initHandler($(this)[0]);
            if(typeData.typeID == ''){
                $(this).parent().remove();
                $('#'+typeData.accountID+' ul').append(accounts.types.newButton);
                $('#'+typeData.accountID+' ul li:last-child()').show();

            } else {
                var element = "#"+typeData.typeID;
                $(element+' label input').prop('readonly', true);
                $(element+' label select').prop('disabled', true);
                $(element+' .save_type').hide();
                $(element+' .cancel_type').hide();
                $(element+' .edit_type').show();
            }
        },
        save: function(){
            var typeData = accounts.types.initHandler($(this)[0]);
            if(typeData.typeID == ''){
                // we're adding a new account type here
                accounts.types.tempData.type_name = $('#'+typeData.accountID+' ul li:last-child() input[name="type_name"]').val();
                accounts.types.tempData.last_digits = $('#'+typeData.accountID+' ul li:last-child() input[name="last_digits"]').val();
                accounts.types.tempData.type = $('#'+typeData.accountID+' ul li:last-child() select').val();
                accounts.types.tempData.accountID = typeData.accountID.replace('account_setting_', '');
            } else {
                // we're going to update an existing account type
                accounts.types.tempData.id = typeData.typeID.replace('type_', '');
                accounts.types.tempData.type_name = $('#'+typeData.typeID+' input[name="type_name"]').val();
                accounts.types.tempData.last_digits = $('#'+typeData.typeID+' input[name="last_digits"]').val();
                accounts.types.tempData.type = $('#'+typeData.typeID+' select').val();
                accounts.types.tempData.accountID = typeData.accountID.replace('account_setting_', '');
            }
            
            if(accounts.types.tempData.type_name=='' || accounts.types.tempData.last_digits=='' || accounts.types.tempData.type==''){
                // TODO - TEST
                notice.display('warning', 'Could not save account type. Missing data');
            } else {
                $.ajax({
                    type: 'POST',
                    url: url+nocache(),
                    data: {
                        type: 'save_account_type',
                        type_data: accounts.types.tempData
                    },
                    beforeSend:function(){
                        loading.start();
                    },
                    success:function(data){
                        accounts.types.tempData = {};
                        // TODO - TEST
                        if(parseInt(data)==0 || isNaN(parseInt(data))){
                            notice.display('warning', 'Could not save account type at this time');
                        } else {
                            accounts.display();
                        }
                    },
                    error:function(){
                        // TODO - TEST
                        notice.display('danger', 'Error occurred while trying to save account type information');
                        accounts.types.tempData = {};
                        loading.end();
                    }
                });
            }
        },
        disable: function(){
            if(confirm("Are you sure you want to disable this account type?")){
                var typeData = accounts.types.initHandler($(this)[0]);
                accounts.types.tempData.accountID = typeData.accountID.replace('account_setting_', '');
                accounts.types.tempData.id = typeData.typeID.replace('type_', '');
                $.ajax({
                    type: 'POST',
                    url: url+nocache(),
                    data: {
                        type: 'disable_account_type',
                        type_data: accounts.types.tempData
                    },
                    beforeSend:function(){
                        loading.start();
                    },
                    success:function(data){
                        accounts.types.tempData = {};
                        if(parseInt(data)==0 || isNaN(parseInt(data))){
                            notice.display('warning', 'Could not disable account type at this time');
                        } else {
                            accounts.display();
                        }
                    },
                    error:function(){
                        notice.display('danger', 'Error occurred while trying to disable account type');
                        accounts.types.tempData = {};
                        loading.end();
                    }
                });
            }
        },
        initHandler: function(element){
            notice.remove();
            var accountTypeData = {};
            accountTypeData.accountID = $(element).parents('tr').prop('id');
            accountTypeData.typeID = $(element).parents('li').prop('id');
            return accountTypeData;
        }
    }
};

function digitsOnly(){
    var last_digits = parseInt($(this).val());
    if(!isNaN(last_digits)){
        $(this).val( last_digits );
    } else {
        $(this).val('');
    }
}

var notice = {
    display: function(alertType, alertText){
        var validAlertTypes = ['info', 'warning', 'success', 'danger'];
        var alertToDisplay = '';
        if($.inArray(alertType, validAlertTypes) > -1){
            alertToDisplay += '<div class="alert alert-'+alertType+' alert-dismissable" role="alert">';
            alertToDisplay += '     <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
            alertToDisplay += '     '+alertText;
            alertToDisplay += '</div>';
        }
        $('.row').before(alertToDisplay);
    },
    remove: function(){
        $('.alert').remove();
    }
};


$(function(){
    loading.img = 'imgs/loader.gif';
    loading.start();
    accounts.display();
    var accountSettings = $('#account_settings');
    accountSettings.delegate('.add_type', 'click', accounts.types.add);
    accountSettings.delegate('.save_type', 'click', accounts.types.save);
    accountSettings.delegate('.cancel_type', 'click', accounts.types.cancel);
    accountSettings.delegate('.edit_type', 'click', accounts.types.edit);
    accountSettings.delegate('.disable_type', 'click', accounts.types.disable);
    accountSettings.delegate('h3', 'click', accounts.types.display);
    accountSettings.delegate('input[name="last_digits"]', 'input', digitsOnly);
});