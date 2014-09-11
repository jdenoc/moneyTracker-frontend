<?php
/**
 * User: denis
 * Date: 2014-02-03
 */
$session_title = include_once(__DIR__ . '/config/config.session.php');
session_name($session_title);
session_start();
if(empty($_SESSION['email'])){
    header('Location: logout.php');
    exit;
}

require_once(__DIR__.'/Lib/php/PDO_Connection.php');
$db = new PDO_Connection('jdenoc_money_tracker', __DIR__.'/config/config.db.php');
$account_types = $db->getAllRows("SELECT id, type_name, last_digits FROM account_types WHERE disabled=0");
$account_type_options = '';
foreach($account_types as $at){
    $account_type_options .= '<option value="'.$at['id'].'">'.$at['type_name'].' ('.$at['last_digits'].')</option>'."\r\n";
}
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <!--  TODO - <link rel="shortcut icon" href="../../assets/ico/favicon.ico">-->
    <title>Money Tracker</title>
    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!-- Custom add-on to Bootstrap - src: https://github.com/marcoceppi/bootstrap-glyphicons -->
    <link href="bootstrap/css/bootstrap.icon-large.min.css" rel="stylesheet" type="text/css"/>
    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet" type="text/css"/>
    <link href="css/custom_bootstrap.css" rel="stylesheet" type="text/css"/>

    <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="Lib/js/loading.js"></script>
    <script type="text/javascript" src="Lib/js/paging.js"></script>
    <script type="text/javascript" src="Lib/js/misc.js"></script>
    <script type="text/javascript" src="js/main.js"></script>

    <script src="jQuery-tags-input/jquery.tagsinput.min.js"></script>
    <link href="jQuery-tags-input/jquery.tagsinput.css" rel="stylesheet" type="text/css" />

    <!-- Drag and Drop code -->
    <script src="js/draganddrop.js" type="text/javascript"></script>
    <link href="css/draganddrop.css" rel="stylesheet" type="text/css"/>
    <!-- END - Drag and Drop code -->

    <link href="css/switch.css" rel="stylesheet" type="text/css" />
    <link href="Lib/css/loading.css" rel="stylesheet" type="text/css" />
</head>
<body>

<!-- Top Nav Bar -->
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <!-- TODO - create logo -->
            <a class="navbar-brand" href="#">Money Tracker</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#" data-toggle="modal" data-target="#entry-modal" id="entry_add">Add Entry</a></li>
                <li><a href="#" data-toggle="modal" data-target="#filter-modal">Filter</a></li>
                <li><a href="#" data-toggle="dropdown" id="user_menu"><img src="<?php echo $_SESSION['pic']; ?>" alt="<?php echo $_SESSION['email']; ?>" /></a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="user_menu">
                        <li role="presentation" class="dropdown-header"><?php echo $_SESSION['name']; ?></li>
                        <li role="presentation" class="divider"></li>
                        <li><a href="settings.php"><span class="glyphicon glyphicon-cog"></span> Settings</a></li>
                        <li role="presentation" class="divider"></li>
                        <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- END - Top Nav Bar -->

<!-- Main body -->
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <ul id="account_display" class="nav nav-sidebar">
                <li><h4>Accounts</h4></li>
                <li class="active" onclick="filter.reset();displayAccount([],2)"><a href="#">Overview <span class="is_filtered">(filtered)</span></a></li>
            </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-condensed">
                    <tr>
                        <th></th>
                        <th>Date</th>
                        <th>Memo</th>
                        <th class="value-col">Value</th>
                        <th class="type-col">Type</th>
                        <th><span class="glyphicon glyphicon-paperclip"></span></th>
                        <th><span class="glyphicon glyphicon-tags"></span></th>
                    </tr>
                </table>
                <button type="button" class="btn btn-default" id="prev"><span class="glyphicon glyphicon-chevron-left"></span></button>
                <button type="button" class="btn btn-default" id="next"><span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>
    </div>
</div>
<!-- END - Main body -->

<!-- Entry Modal -->
<div class="modal fade" id="entry-modal" tabindex="-1" role="dialog" aria-labelledby="entry-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="entry-title">Entry</h4>
                <label class="small">Confirmed: <input type="checkbox" id="entry_confirm" name="entry_confirm" class="form-control" /></label>
            </div>
            <div class="modal-body">
                <label><span>Date:</span><input type="date" name="entry_date" id="entry_date" class="form-control"/></label>
                <label><span>Value:</span><span>$</span><input type="text" name="entry_value" id="entry_value" class="form-control" placeholder="9.99" /></label>
                <label><span>Account Type:</span>
                    <select name="entry_account_type" id="entry_account_type" class="form-control">
                        <option></option>
                        <?php echo $account_type_options; ?>
                    </select>
                </label>
                <label><span>Memo:</span><textarea name="entry_memo" id="entry_memo" class="form-control"></textarea></label>
                <label for="entry_tags"><span>Tags:</span></label>
                <input type="text" name="entry_tags" id="entry_tags" class="form-control" placeholder="Add tags..." />

                <div class="onoffswitch">
                    <input type="checkbox" name="entry_minus" class="onoffswitch-checkbox" id="entry_minus" />
                    <label class="onoffswitch-label" for="entry_minus">
                        <div class="onoffswitch-inner"></div>
                        <div class="onoffswitch-switch"></div>
                    </label>
                </div>

                <div id="dragandrophandler">Drag & Drop Files Here</div>
                <input type="hidden" name="entry_attachments" id="entry_attachments" />
                <input type="hidden" name="entry_has_attachment" id="entry_has_attachment" />

                <ul id="display_attachments" class="list-group"></ul>
            </div>

            <div class="modal-footer">
                <input type="hidden" name="entry_id" id="entry_id" />
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="entry_delete"><span class="glyphicon glyphicon-trash"></span>Delete</button>
                <button type="button" class="btn btn-default" id="entry_lock"><span class="icon-large icon-lock"></span></button>
                <button type="button" class="btn btn-default" id="entry_unlock"><span class="icon-large icon-unlock"></span></button>
                <button type="button" class="btn btn-default" data-dismiss="modal" id="entry_close">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="entry_save"><span class="glyphicon glyphicon-ok"></span>Save changes</button>
                <input type="hidden" name="entry_data" id="entry_data"/>
            </div>
        </div>
    </div>
</div>
<!-- END - Entry Modal -->


<!-- Filter Modal -->
<div class="modal fade" id="filter-modal" tabindex="-1" role="dialog" aria-labelledby="filter-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="filter-title">Search Filter</h4>
            </div>
            <div class="modal-body">
                <label><span>Start Date:</span><input type="date" name="filter_start" id="filter_start" class="form-control"/></label>
                <label><span>End Date:</span><input type="date" name="filter_end" id="filter_end" class="form-control"/></label>
                <label><span>Account Type:</span>
                    <select name="filter_account_type" id="filter_account_type" class="form-control">
                        <option></option>
                        <?php echo $account_type_options; ?>
                    </select>
                </label>
                <label>Tags:</label>
                <div class="btn-group" data-toggle="buttons" id="filter_tags"></div>
                <div class="expense_radio">
                    <label>Income:<input type="radio" name="filter_expense" class="filter_expense" value="0"/></label>
                    <label>Expense:<input type="radio" name="filter_expense" class="filter_expense" value="1"/></label>
                    <label>Both:<input type="radio" name="filter_expense" class="filter_expense income_expense" value=""/></label>
                </div>
                <label><span>Has Attachment:</span><input type="checkbox" name="filter_attachments" id="filter_attachments"/></label>
                <label><span>No Attachment:</span><input type="checkbox" name="filter_no_attachments" id="filter_no_attachments"/></label>
                <label><span>Not Confirmed:</span><input type="checkbox" name="filter_unconfirmed" id="filter_unconfirmed"/></label>
                <label><span>Min Range:</span><span>$</span><input type="text" name="filter_min_range" id="filter_min_range" class="form-control" placeholder="0.00"/></label>
                <label><span>Max Range:</span><span>$</span><input type="text" name="filter_max_range" id="filter_max_range" class="form-control" placeholder="100.00"/></label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="filter_close">Close</button>
                <button type="button" class="btn btn-warning" data-dismiss="modal" id="filter_reset"><span class="glyphicon glyphicon-repeat"></span>Reset</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="filter_set"><span class="glyphicon glyphicon-search"></span>Set Filter</button>
                <input type="hidden" name="filter_data" id="filter_data"/>
            </div>
        </div>
    </div>
</div>
<!-- END - Filter Modal -->

</body>
</html>