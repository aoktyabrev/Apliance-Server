<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome-font-awesome.min.css">
        <link rel="stylesheet" href="css/main.css">

        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
            WebFont.load({
                google: {"families": ["Poppins:300,400,500,600,700", "Roboto:300,400,500,600,700"]},
                active: function () {
                    sessionStorage.fonts = true;
                }
            });
        </script>
    </head>
    <body>
        <div><input type="hidden" id="token" value="0"/></div>
        <div class="container-fluid p-0">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="float-left nav-item pr-5">
                    <img src="img/logo.png" height="55"/>
                </div>
                <div class="float-left nav-item">
                    <span class="navbar-brand mb-0 h1">Appliance Server</span>
                    <span id="appliance-id" class="navbar-brand mb-0 h1"></span>
                </div>
                <ul class="navbar-nav ml-auto">
                    <button type="submit" id="logout" class="nav-item btn btn-success invisible">Logout</button>
                </ul>
            </nav>
            <div class="row mt-4 p-3">
                <div class="col-sm">
                    <div class="left-block">
                        <form id="serverForm" action="" method="post" class="card">
                            <div class="card-body">
                                <h6 class="card-title text-center">Add new server</h6>
                                <div class="card-text">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-8"><input type="text" name="serverTicket-ip" class="form-control" id="ip" placeholder="IP" required></div>
                                            <div class="col-md-4"><input type="text" name="serverTicket-port" class="form-control" id="port" placeholder="Port" required></div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <select name="serverTicket-os" class="form-control" id="os" required>
                                                    <option value="">Operational System</option>
                                                    <option value="linux">Linux</option>
                                                    <option value="windows">Windows</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="serverTicket-connection_type" class="form-control" id="connection_type" required>
                                                    <option value="">Type</option>
                                                    <option value="server" selected>Server</option>
                                                    <option value="agent">Agent</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input name="credentials-username" type="text" class="form-control" id="username" placeholder="Username">
                                    </div>
                                    <div class="form-group">
                                        <select name="serverTicket-auth_method" class="form-control" id="auth_method" required>
                                            <option value="">Authentication method</option>
                                            <option value="password">Password</option>
                                            <option value="key">Key</option>
                                            <option value="group">Credentials Group</option>
                                        </select>
                                    </div>
                                    <div id="block-credentialsGroup" style="display: none;">
                                        <div class="form-group">
                                            <label for="credentialsGroup">Group Credentials</label>
                                            <select name="credentialsGroup-group" class="form-control" id="credentialsGroup">
                                                <option value="" disabled selected>There is no groups yet</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="block-password" style="display: none;">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input name="credentials-password" type="password" class="form-control" id="password">
                                        </div>
                                    </div>
                                    <div id="block-key" style="display: none;">
                                        <div class="form-group">
                                            <label for="public_key">Public Key</label>
                                            <textarea name="credentials-public_key" class="form-control" id="public_key"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="private_key">Private Key</label>
                                            <textarea name="credentials-private_key" class="form-control" id="private_key"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-check form-check-inline">
                                            <input name="credentials-use_sudo" class="form-check-input" type="checkbox" value="1" id="use_sudo">
                                            <label class="form-check-label" for="use_sudo">
                                                Use sudo
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input name="credentials-use_su" class="form-check-input" type="checkbox" value="1" id="use_su">
                                            <label class="form-check-label" for="use_su">
                                                Use su
                                            </label>
                                        </div>
                                    </div>
                                    <div id="block-root_password" style="display: none;">
                                        <div class="form-group">
                                            <label for="root_password">Root Password</label>
                                            <input name="credentials-root_password" type="password" class="form-control" id="root_password">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-7">
                                                <div id="block-makeGroup" class="form-check form-check-inline py-2 pl-1" style="display: none;">
                                                    <input name="credentialsGroup-makeGroup" id="makeGroup" class="form-check-input" type="checkbox" value="1">
                                                    <label class="form-check-label" for="makeGroup">
                                                        <u>Make group credentials</u>
                                                    </label>
                                                </div>
                                                <div id="block-groupName" style="display: none;">
                                                    <div class="form-group">
                                                        <input name="credentialsGroup-groupName" type="text" class="form-control" id="groupName" placeholder="Group Name">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <button type="submit" id="saveServer" class="btn btn-success form-control">Add Server</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="left-block">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="subnet" type="text" class="form-control" id="subnet" placeholder="192.168.0.0/24" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="card-title text-center">
                                        <button type="submit" id="addNetwork" class="btn btn-success form-control">Add subnet</button>
                                    </h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="tblSubnets" class="card-text">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <td>Subnet</td>
                                                    <td>Description</td>
                                                    <td>Action</td>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="left-block">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="card-title text-center">
                                        <button type="submit" id="scanAllNetwork" class="btn btn-success form-control">Scan all subnets</button>
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="card-title text-center">
                                        <button type="submit" id="scanNetwork" class="btn btn-success form-control">Scan local subnet</button>
                                    </h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="scannedServers" class="card-text">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <td>IP</td>
                                                    <td>Description</td>
                                                    <td>Action</td>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 datatable p-3">
                    <div id="alerts" class="nav-item mx-auto">
                        <div class="alert alert-danger mt-3" style="display: none;"></div>
                        <div class="alert alert-success mt-3" style="display: none;"></div>
                    </div>
                    <table id="serverList" class="table table-striped">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Port</th>
                                <th>OS</th>
                                <th>Authentication</th>
                                <th>Connection</th>
                                <th>Group Credentials</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="login-form" class="modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Appliance User Login</h5>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="appliance-user-login" class="col-form-label">Login</label>
                                <input type="text" class="form-control" id="appliance-user-login">
                            </div>
                            <div class="form-group">
                                <label for="appliance-user-password" class="col-form-label">Password</label>
                                <input type="password" class="form-control" id="appliance-user-password">
                            </div>
                        </form>
                        <div id="error-message" class="float-left"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="login" class="btn btn-primary">Login</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="ident-form" class="modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Identification</h5>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="appliance-url-message" class="col-form-label">Appliance URL:</label>
                                <textarea class="form-control" id="appliance-url-message"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="appliance-id-message" class="col-form-label">Appliance ID:</label>
                                <input type="text" class="form-control" id="appliance-id-message">
                            </div>
                            <div class="form-group">
                                <label for="appliance-token-ident" class="col-form-label">Token:</label>
                                <input type="text" class="form-control" id="appliance-token-ident">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="save-ident" class="btn btn-primary" data-dismiss="modal">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="error-form" class="modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Error</h5>
                    </div>
                    <div class="modal-body">
                        <div id="err-message" class="float-left"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/template" id="server-row-template">
            <tr>
            <td class="ip"></td>
            <td class="port"></td>
            <td class="os"></td>
            <td class="auth_method"></td>
            <td class="connection_type"></td>
            <td class="credentials_group"></td>
            <td class="actions"></td>
            </tr>
        </script>

        <script type="application/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        <script type="application/javascript" src="js/main.js"></script>
    </body>
</html>