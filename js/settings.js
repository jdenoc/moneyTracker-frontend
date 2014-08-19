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
                $('#accounts').append(accountData);
                loading.end();
            },
            error:function(){
                // TODO - display error message
            }
        });
    },
    add: function(){
        // TODO - create a new account
    },
    disable: function(){
        // TODO - disable account
    },
    types: {
        display: function(){
            // TODO - this will just be a show/hide toggle
        },
        save: function(){
            // TODO - add/update account type
        },
        disable: function(){
            // TODO - disable account type
        }
    }
};


$(function(){
    loading.img = 'imgs/loader.gif';
    loading.start();
    accounts.display();
});