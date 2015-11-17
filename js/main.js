/**
 * Created by denis on 3/3/14.
 */

var validTags=[], url='../includes/request_data.php?x=';

var editDisplay = {
    lock: function(){
        $('#entry_date').prop('disabled', true);
        $('#entry_value').prop('disabled', true);
        $('#entry_account_type').prop('disabled', true);
        $('#entry_memo').prop('disabled', true);
        $("#entry_tags").prop('disabled', true).show().css({width:314, marginBottom: 15});
        $("#entry_tags_tagsinput").hide();
        $('#entry_minus').prop('disabled', true);
        $('#entry_confirm').prop('disabled', true);
        $('#dragandrophandler').hide();
        $('#entry_save').addClass('disabled');
        $('#entry_unlock').show();
        $('#entry_lock').hide();
    },
    unlock: function(){
        $('#entry_date').prop('disabled', false);
        $('#entry_value').prop('disabled', false);
        $('#entry_account_type').prop('disabled', false);
        $('#entry_memo').prop('disabled', false);
        $("#entry_tags").prop('disabled', false).hide();
        $("#entry_tags_tagsinput").show();
        $('#entry_minus').prop('disabled', false);
        $('#entry_confirm').prop('disabled', false);
        $('#dragandrophandler').show();
        $('#entry_save').removeClass('disabled');
        $('#entry_unlock').hide();
        $('#entry_lock').show();
    },
    reset: function(){
        var today = new Date();
        editDisplay.unlock();

        $('#entry-title').html('New Entry');
        $('#entry_id').val(-1);
        $('#entry_date').val(
            today.getFullYear()+'-'
                +(today.getMonth()<10?'0':'')+(today.getMonth()+1)+'-'
                +(today.getDate()<10?'0':'')+today.getDate()
        );
        $('#entry_confirm').prop('checked', false);
        $('#entry_value').val('');
        $('#entry_account_type').val('');
        $('#entry_memo').val('');
        $("#entry_tags").importTags('');
        $('#entry_minus').prop('checked', true);
        $('#entry_attachments').val('[]');
        $('#entry_has_attachment').val(0);
        $('#entry_delete').hide();
        $('#entry_lock').hide();
        $('#entry_unlock').hide();
        attachments.remove();
        $('.statusbar').remove();
    },
    fill: function(entry_id){
        $.ajax({
            type: 'POST',
            url: url+nocache(),
            data: {
                type: 'entry',
                id : entry_id
            },
            beforeSend: function(){
                notice.remove();
                loading.start()
            },
            success:function(data){
                // successful request
                var tags='', editData = JSON.parse(data);
                $(editData['tags']).each(function(index, tagObj){
                    if(tags == '' || typeof tags == 'undefined'){
                        tags = tagObj['tag'];
                    } else {
                        tags += ','+tagObj['tag'];
                    }
                });
                $('#entry-title').html('Entry: '+editData['id']);
                $('#entry_date').val(editData['date']);
                $('#entry_value').val(editData['value']);
                $('#entry_account_type').val(editData['account_type']);
                $('#entry_memo').val(editData['memo']);
                $("#entry_tags").importTags('');
                $('#entry_tags').importTags(tags);
                $('#entry_minus').prop('checked', parseInt(editData['expense']));
                $('#entry_confirm').prop('checked', parseInt(editData['confirm']));
                $('#entry_id').val(editData['id']);

                if(editData['has_attachment']==1){
                    $(editData['attachments']).each(attachments.display);
                    $('#display_attachments').show();  // Display the carousel using data from  editData['attachments'];
                } else {
                    $('#display_attachments').hide();  // hide the carousel. No data available.
                    $('#remove-attachment').hide();
                    $('#entry_attachments').val('[]');
                }
                $('#entry_delete').show();
                $('.statusbar').remove();

                if( $("#entry_confirm").prop('checked') ){
                    editDisplay.lock();
                    $('#entry_unlock').show();
                } else {
                    entry.save();
                }
                loading.end();
            },
            error:function(){
                notice.display('danger', 'Could not retrieve entry data');
                loading.end();
            }
        });
    }
};

function fillTable(all){
    paging.totalElements = -1;
    $.ajax({
        type: 'POST',
        url: url+nocache(),
        data: {
            type: 'count',
            where: JSON.stringify(entry.where)
        },
        beforeSend:function(){},
        success:function(data){
            paging.totalElements = parseInt(data);
        },
        error:function(){
            // Do nothing. User doesn't need to know about this.
        }
    });

    $.ajax({
        type: 'POST',
        url: url+nocache(),
        data: {
            type: 'list',
            start : paging.current,
            limit : paging.limit,
            where: JSON.stringify(entry.where)
        },
        beforeSend:function(){
            notice.remove();
        },
        success:function(entryData){
            // successful request
            $('table').append(entryData);
            loading.end();
        },
        error:function(){
            notice.display('danger', 'Could not retrieve data');
            loading.end();
        }
    });

    if(all){
        $.ajax({
            type: 'POST',
            url: url+nocache(),
            data: { type: 'list_accounts' },
            beforeSend:function(){},
            success:function(accountData){
                // successful request
                $('#account_display').append(accountData);
                if(typeof entry.where.group != 'undefined'){
                    $('#account_display li:nth-child('+(entry.where.group + 2)+')').addClass('active');
                }
            },
            error:function(){
                notice.display('danger', 'Could not accounts');
            }
        });
    }
}

function refreshTable(all){
    loading.start();
    if(all)     $('#account_display li:nth-child(n+3)').remove();
    $('table tr:nth-child(n+2)').remove();
    fillTable(all);
}

function updateTags(elem, elem_tags){
    if($.inArray(elem_tags, validTags) === -1 && typeof elem_tags !== 'undefined'){
        $(elem).removeTag(elem_tags);
        console.log('removing invalid tag:'+elem_tags);
    }
}

function getValidTags(){
    $.ajax({
        type: 'POST',
        url: url+nocache(),
        data: { type: 'tags' },
        beforeSend:function(){},
        success:function(data){
            var filterTags = '';
            var allTags = JSON.parse(data);
            $(allTags).each(function(idx, obj){
                validTags.push( obj.tag );
                filterTags += '<label class="btn btn-info"><input type="checkbox" value="'+obj.id+'"/>'+obj.tag+'</label>';
            });
            $('#filter_tags').append(filterTags);
            $('#entry_tags_tagsinput')
                .addClass('form-control')
                .attr({
                    'data-toggle':"tooltip",
                    'data-placement':"top",
                    title:validTags.join(', ')
                })
                .tooltip();
        },
        error:function(){
            // Do nothing. User doesn't need to know about this.
        }
    });
}

var entry = {
    where:[],
    save: function(){
        var entryData = {};
        entryData.id = $('#entry_id').val();
        entryData.date = $('#entry_date').val();
        entryData.value = $('#entry_value').val();
        entryData.account_type = $('#entry_account_type').val();
        entryData.memo = $('#entry_memo').val();
        entryData.tags = $('#entry_tags').val().split(',');
        entryData.expense = $('#entry_minus').prop('checked') ? 1 : 0;
        entryData.confirm = $('#entry_confirm').prop('checked') ? 1 : 0;
        entryData.attachments = JSON.parse( $('#entry_attachments').val() );
        entryData.has_attachment = $('#entry_has_attachment').val();

        $('#entry_data').val( JSON.stringify(entryData) );
    },
    submit: function(){
        $.ajax({
            type: 'POST',
            url: url+nocache(),
            data: {
                type: 'save',
                entry_data : $('#entry_data').val()
            },
            beforeSend:function(){
                notice.remove();
                loading.start();
            },
            success:function(data){
                // successful request
                if(parseInt(data) == 1){
                    refreshTable(true);
                    editDisplay.reset();
                }
            },
            error:function(){
                notice.display('danger', 'Could not save entry');
                loading.end();
            }
        });
    },
    del: function(){
        var entryId = $('#entry_id').val();
        if(confirm('Are you sure you want to delete Entry: '+entryId)){
            $.ajax({
                type: 'POST',
                url: url+nocache(),
                data: {
                    type: 'delete_entry',
                    id : entryId
                },
                beforeSend:function(){
                    notice.remove();
                    loading.start();
                },
                success:function(data){
                    if(parseInt(data) == 1){
                        refreshTable(true);
                    }
                },
                error:function(){
                    notice.display('danger', 'Could not delete entry');
                    loading.end();
                }
            });
        }
    }
};

function displayAccount(setWhere, child){
    $('#account_display li').removeClass('active');
    $('#account_display li:nth-child('+child+')').addClass('active');
    paging.current = 0;
    entry.where = setWhere;
    refreshTable(false);
    paging.reset();
}

var filter = {
    set: function(){
        var filter = {};
        var minRange = $('#filter_min_range');
        var maxRange = $('#filter_max_range');
        minRange.val( minRange.val().replace(/[^0-9.]/g, '') );
        maxRange.val( maxRange.val().replace(/[^0-9.]/g, '') );
        if($("#filter_start").val() != ''){
            filter['start_date'] = $('#filter_start').val();
        }
        if($("#filter_end").val() != ''){
            filter['end_date'] = $('#filter_end').val();
        }
        if($("#filter_account_type").val() != ''){
            filter['account_type'] = $('#filter_account_type').val();
        }
        var filterTags = [];
        $('#filter_tags label.active input').each(function(idx, obj){
            filterTags.push( $(obj).val() );
        });
        filter['tags'] = filterTags;
        if($('.filter_expense:checked').val() != ''){
            filter['expense'] = $(".filter_expense:checked").val();
        }
        if($('#filter_attachments').prop('checked')){
            filter['attachments'] = 1;
        }
        if($('#filter_no_attachments').prop('checked')){
            filter['attachments'] = 0;
        }
        if($('#filter_unconfirmed').prop('checked')){
            filter['confirm'] = 0;
        }
        if($('#filter_confirmed').prop('checked')){
            filter['confirm'] = 1;
        }
        if(minRange.val() != '' && minRange.val() > 0){
            filter['min_value'] = minRange.val();
        }
        if(maxRange.val() != '' && maxRange.val() > 0){
            filter['max_value'] = maxRange.val();
        }
        $('.is_filtered').show();
        displayAccount(filter, 2);
    },
    reset: function(){
        $('#filter_start').val('');
        $('#filter_end').val('');
        $('#filter_account_type').val('');
        $('#filter_tags label').removeClass('active');
        $('.expense_radio .income_expense').prop('checked', true);
        $('#filter_attachments').prop('checked', false);
        $('#filter_no_attachments').prop('checked', false);
        $('#filter_unconfirmed').prop('checked', false);
        $('.is_filtered').hide();
    }
};

var attachments = {
    open: function(attachment_id){
        var url = 'display.php?id='+attachment_id;
        var win=window.open(url, '_blank');
        win.focus();
    },
    del: function(attachment_id){
        var entryId = $('#entry_id').val();
        if(confirm('Are you sure you want to delete attchment: '+attachment_id)){
            $.ajax({
                type: 'POST',
                url: url+nocache(),
                data: {
                    type: 'delete_attachment',
                    entry_id : entryId,
                    id: attachment_id
                },
                beforeSend:function(){
                    notice.remove();
                },
                success:function(data){
                    $('#attachment_'+attachment_id).remove();
                    $('#entry_has_attachment').val( parseInt(data) );
                },
                error:function(){
                    notice.display('danger', 'Could not delete attachment');
                }
            });
        }
    },
    display: function(idx, att){
        $('#entry_has_attachment').val(1);
        $('#display_attachments').append(
            ' <li class="list-group-item" id="attachment_'+att.id+'">'+att.filename+
                '<button type="button" class="btn btn-danger glyphicon glyphicon-trash" onclick="attachments.del('+att.id+');"></button>'+
                '<button type="button" class="btn btn-default glyphicon glyphicon-search" onclick="attachments.open('+att.id+');"></button>'+
            '</li>'
        );
    },
    remove: function(){
        $('#display_attachments li').remove();
    }
};

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
    paging.prevObj = $('#prev');
    paging.nextObj = $('#next');

    getValidTags();
    fillTable(true);
    editDisplay.reset();
    filter.reset();
    paging.reset();

    paging.nextObj.val(paging.current+1).click(function(){
        paging.next();
        refreshTable(false);
    });
    paging.prevObj.hide().click(function(){
        paging.prev();
        refreshTable(false);
    });
    $('#entry_value').change(function(){
        var value = $(this).val().replace(/[^0-9.]/g, '');
        $(this).val( parseFloat(value).toFixed(2) );
    });
    $('#entry_tags').tagsInput({
        width: 314,
        height: 130,
        defaultText:'Add a tag...',
        onChange: updateTags
    });
    $('#entry_save').click(function(){
        entry.save();
        entry.submit();
    });
    $('#entry_delete').click(entry.del);
    $('#entry_lock').click(editDisplay.lock);
    $('#entry_unlock').click(editDisplay.unlock);
    $('#entry-modal').on('hidden.bs.modal', function (e) {
        editDisplay.reset();
    });

    $('#filter_attachments').click(function(){
        var noAttachment = $('#filter_no_attachments');
        if( noAttachment.prop('checked')){
            noAttachment.prop('checked', false);
        }
    });
    $('#filter_no_attachments').click(function(){
        var yesAttachment = $('#filter_attachments');
        if( yesAttachment.prop('checked')){
            yesAttachment.prop('checked', false);
        }
    });
    $('#filter_end').change(function(){
        var startDate = new Date( $('#filter_start').val() );
        var endDate = new Date( $('#filter_end').val() );
        if(endDate < startDate){
            $("#filter_end").val('');
            alert("You can't set an end date to be before the start date.\nThis service doesn't suppose time travel.");
        }
    });
    $('#filter_reset').click(function(){
        filter.reset()();
        entry.where = [];
    });
    $('#filter_set').click(filter.set);
});