/**
 * Created by denis on 8/18/14.
 */

var url='../includes/request_data.php?x=';

var accounts = {
    url: {},
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
                $('.edit_type').bind('click', accounts.types.edit);
                $('.disable_type').bind('click', accounts.types.disable);
                $('.account_type').each(function(idx, obj){
                    var typeVal = $(obj).children('input[type="hidden"]').remove();
                    $(obj).children('label').children('select').val(typeVal.val());
                });
                loading.end();
            },
            error:function(){
                // TODO - display error message
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
        display: function(){
            var typeData = accounts.types.initHandler($(this)[0]);
            var element = "#"+typeData.accountID+" ul .account_type";
            if($(element).is(':visible')){
                $(element+' label input').prop('readonly', true);
                $(element+' label select').prop('disabled', true);
                $(element+' .save_type').hide();
                $(element+' .edit_type').show();
                $(element).slideUp();
            } else {
                $(element).slideDown();
            }
        },
        add: function(){
            // TODO - this will open a model
        },
        edit: function(){
            var typeData = accounts.types.initHandler($(this)[0]);
            var element = "#"+typeData.typeID;
            $(element+' label input').prop('readonly', false);
            $(element+' label select').prop('disabled', false);
            $(element+' .edit_type').hide();
            $(element+' .save_type').show();
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