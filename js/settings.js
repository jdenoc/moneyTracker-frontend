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
            beforeSend:function(){},
            success:function(accountData){
                $('#account_settings').append(accountData);
                $('.account_setting h3').bind('click', accounts.types.display);
                $('.add_type').bind('click', accounts.types.save);
                $('.save_type').bind('click', accounts.types.save);
                $('.cancel_type').bind('click', accounts.types.cancel);
                $('.edit_type').bind('click', accounts.types.edit);
                $('.disable_type').bind('click', accounts.types.disable);
                $('.account_type').each(function(idx, obj){
                    var typeData = accounts.types.initHandler(obj);
                    typeData.accountID = typeData.accountID.replace('account_setting_', '');
                    typeData.typeID = $(obj).prop('id').replace('type_', '');
                    var selectOptions = '';
                    $(typeOptions).each(function(typeIdx, typeObj){
                        selectOptions += '<option value="'+typeObj+'">'+typeObj.substring(0,1).toUpperCase()+typeObj.substring(1)+'</option>'+"\n";
                    });
                    $(obj).children('label').children('select').append(selectOptions).val( types[typeData.accountID][typeData.typeID] );
                });
                loading.end();
            },
            error:function(){
                // TODO - display error message
                loading.end();
            }
        });
    },
    add: function(){
        // TODO - create a new account
        alert('this doesn\'t work yet.');
    },
    disable: function(){
        // TODO - disable account
        alert('this doesn\'t work yet.');
    },
    types: {
        tempData:{},
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
            } else {
                $(element).slideDown();
            }
        },
        add: function(){
            var typeData = accounts.types.initHandler($(this)[0]);
            $(this).remove();       // TODO - test to see if this works
            var display = '<li class="account_type">';
            display += '    <label>Name:<input type="text" name="type_name" class="form-control" /></label>';
            display += '    <label>Last Digits:<input type="text" name="last_digits" class="form-control" /></label>';
            display += '    <label>Type: <select name="type" class="form-control" >'+$type_options+'</select></label>';  // TODO - figure out how to display select options
            display += '    <button type="button" class="btn btn-default type_button save_type">Save</button>';
            display += '    <button type="button" class="btn btn-default type_button cancel_type">Cancel</button>';
            display += "</div></li>"+"\n";
            display += "<li class='account_type add_type btn'>Add Account Type</li>"+"\n";
            $('#'+typeData.accountID+' ul').append(display);

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
            if(typeof typeData.typeID == 'undefined'){
                // TODO - remove section. This is a "potential" added type.
                $(this).remove();   // TODO - Let's see if this actually works.
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
            // TODO - add/update account type
            alert('this doesn\'t work yet.');
            var typeData = accounts.types.initHandler($(this)[0]);
            if(typeof typeData.typeID == 'undefined'){
                // TODO - we're adding a new account type here
                // TODO - cause modal to appear
            } else {
                // TODO - we're going to update an existing account type
                // TODO - cause modal to appear
            }
        },
        disable: function(){
            // TODO - disable account type
            var typeData = accounts.types.initHandler($(this)[0]);
            alert('this doesn\'t work yet.');
        },
        initHandler: function(element){
            var accountTypeData = {};
            accountTypeData.accountID = $(element).parents('tr').prop('id');
            accountTypeData.typeID = $(element).parents('li').prop('id');
            return accountTypeData;
        }
    }
};


$(function(){
    loading.img = 'imgs/loader.gif';
    loading.start();
    accounts.display();
});