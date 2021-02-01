$(function () {

    var token = window.localStorage.getItem('token');

    $(window).on('load', function () {
        if (token != 1) {
            $('#login-form').modal('show');
        } else {
            $('#logout').removeClass('invisible');
            identification();
        }
    });

    $('#logout').on('click', function () {
        window.localStorage.setItem('token', 0);
        location.reload(true);
    });

    $('#login-form').on('click', '#login', function () {
        $.ajax({
            url: "endpoint.php?action=authorize",
            type: "post",
            data: {
                'login': $('#login-form #appliance-user-login').val(),
                'password': $('#login-form #appliance-user-password').val()
            }
        }).done(function (data) {
            let dataObject = JSON.parse(data);
            $('#error-message').html('');

            if (dataObject.type == 'success') {
                window.localStorage.setItem('token', 1);
                location.reload(true);
                $('#logout').removeClass('invisible');
            } else if (dataObject.type == 'error') {
                $('#error-message').html(dataObject.text);
            }
        });
    });

    function identification()
    {
        $.ajax({
            url: 'endpoint.php?action=ident_init',
            type: "post",
            data: {}
        }).done(function (data) {
            let dataObject = JSON.parse(data);

            if (dataObject.type == 'error') {
                if (dataObject.text == 'No TOKEN') {
                    $('#appliance-id-message').val(dataObject.data);
                    console.log(dataObject.url);
                    for (var i = 0; i < dataObject.url.length; i++)
                    {
                        $('#appliance-url-message').append(dataObject.url[i] + '\n');
                    }
                    $('#ident-form').modal('show');
                } else {
                    $('#err-message').html(dataObject.text);
                    $('#error-form').modal('show');
                }

            } else {
                $('#appliance-id').html(dataObject.data);
            }
        });
    }

    $('#error-form').click(function () {
        location.reload(true);
    });

    $('#ident-form').on('click', '#save-ident', function () {
        $.ajax({
            url: "endpoint.php?action=ident_set",
            type: "post",
            data: {
                'token': $('#ident-form #appliance-token-ident').val(),
            }
        }).done(function (data) {
            let dataObject = JSON.parse(data);

            if (dataObject.type == 'error') {
                $('#err-message').html(dataObject.text);
                $('#error-form').modal('show');
            } else {
                location.reload(true);
            }
        });
    });

    function authorize() {

        var token = $('#token').val();

        if (token != 1) {
            return false;
        }

        $.ajax({
            url: 'endpoint.php?action=resource',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Authorization', "OAuth " + token);
                xhr.setRequestHeader('Accept', "application/json");
            },
            success: function (response) {
                console.log(response);
                return true;
            }
        });

        return false;
    }

    $('#auth_method').on('change', function () {
        let auth_method = this.value;
        $('#block-key, #block-password, #block-credentialsGroup').hide();

        if (auth_method === 'group') {
            $('#block-credentialsGroup').bind('beforeShow', function () {
                getCredentialsGroupList();
            }).show();

            $('#block-makeGroup, #block-groupName').hide();
        } else {
            $('#block-makeGroup').show();
        }

        $('#block-' + auth_method).show();
    });

    $('#os').on('change', function () {
        if (this.value == 'windows') {
            $('#auth_method').val('password').trigger('change');
            $('#auth_method').attr('disabled', 'disabled');
            $('#use_sudo').prop('checked', false).trigger('change').attr('disabled', 'disabled');
            $('#use_su').prop('checked', false).trigger('change').attr('disabled', 'disabled');
        } else if (this.value == 'linux') {
            $('#auth_method').removeAttr('disabled');
            $('#use_sudo').removeAttr('disabled');
            $('#use_su').removeAttr('disabled');

        }
    });

    $('#use_su').on('change', function () {
        $('#block-root_password').hide();
        if ($(this).is(':checked')) {
            $('#block-root_password').show();
            $('#use_sudo').prop('checked', false);
        }
    });

    $('#use_sudo').on('change', function () {
        $('#block-root_password').hide();
        if ($(this).is(':checked')) {
            $('#use_su').prop('checked', false);
        }
    });

    $('#serverForm').submit(function (e) {
        let _this = $(this), arr = _this.serializeArray();
        let data = {'serverTicket': {}, 'credentials': {}, 'credentialsGroup': {}};

        for (var i in arr) {
            let fullName = arr[i].name.split('-');
            let tempData = data[fullName[0]];
            tempData[fullName[1]] = arr[i] != undefined ? arr[i].value : '';
            data[fullName[0]] = tempData;
        }

        saveServerData($('#saveServer'), data);
        e.preventDefault();
        return false;
    });

    $('#serverList').on('click', '.delete', function () {
        if (token != 1) {
            return false;
        }

        if (!confirm('Do you want to remove this server?')) {
            return;
        }

        let _this = $(this);
        $.ajax({
            url: "endpoint.php?action=remove",
            type: "post",
            data: {'_id': _this.data('_id')}
        }).done(function (data) {
            let dataObject = JSON.parse(data);

            if (dataObject.type != 'error') {
                _this.closest('tr').remove();
            }

            setResponse(dataObject);
            reloadServerTable();
        });
    });

    $('#serverList').on('click', '.detach-credentials-group', function () {
        let _this = $(this);

        if (token != 1) {
            return false;
        }

        $.ajax({
            url: 'endpoint.php?action=credentialsGroupServerTickets',
            type: "post",
            data: {'_id': _this.data('_id')}
        }).done(function (data) {
            if (!confirm('Do you want to detach credentials from ' + data + ' servers?')) {
                return;
            }
            $.ajax({
                url: "endpoint.php?action=detachCredentialsGroup",
                type: "post",
                data: {'_id': _this.data('_id')}
            })
                    .done(function (data) {
                        let dataObject = JSON.parse(data);

                        if (dataObject.type != 'error') {
                            _this.closest('tr').remove();
                        }

                        setResponse(dataObject);
                        reloadServerTable();
                    });
        });
    });

    $('#makeGroup').on('click', function () {
        $('#block-groupName').toggle(function () {
            $('#groupName').prop('required', true);
        }, function () {
            $('#groupName').prop('required', false);
        });
    });

    $('#scanNetwork').on('click', function () {
        scanNetwork(this);
    });

    $('#scanAllNetwork').on('click', function () {
        scanAllNetwork(this);
    });

    $('#addNetwork').on('click', function () {
        addNetwork(this);
    });

    $('#scannedServers').on('click', '.addScanned', function () {
        $('#ip').focus().val($(this).parent().parent().find('.ip').html());
    });

    $('#tblSubnets').on('click', '.delSubnet', function () {
        $.ajax({
            url: "endpoint.php?action=delNetwork",
            type: 'post',
            data: {
                'subnet': $(this).parent().parent().find('.subnet').html(),
            },            
            beforeSend: function () {
                //$(handler).prop('disabled', true).html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
            },
            complete: function () {
                //$(handler).prop('disabled', false).text('Add server');
            },
            success: function (data) {
                setResponse(JSON.parse(data));
                getSubnets();
            }
        });
    });

    function getCredentialsGroupList() {
        if (token != 1) {
            return false;
        }

        $.ajax({
            url: 'endpoint.php?action=credentialsGroupList',
        }).done(function (data) {
            let credentialsGroup = $('#credentialsGroup');
            let dataJSON = JSON.parse(data);

            if (!dataJSON.length) {
                return false;
            }

            credentialsGroup.html('');
            $.each(dataJSON, function (key, value) {
                $('<option>').attr('value', value._id).html(value.name).appendTo(credentialsGroup);
            });


        });
    }

    function reloadServerTable() {
        let tableBody = $('#serverList tbody');

        if (token != 1) {
            return false;
        }

        $.ajax({
            url: 'endpoint.php?action=list',
            beforeSend: function () {
                tableBody.html('<tr><td colspan="6"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
            },
            success: function (data) {
                tableBody.html('');
                let template = $('#server-row-template').html();
                $.each(JSON.parse(data), function (key, value) {
                    var row = $(template).clone();
                    $(row).find('.ip').text(value.ip);
                    $(row).find('.port').text(value.port);
                    $(row).find('.os').text(value.os);
                    $(row).find('.auth_method').text(value.auth_method);
                    $(row).find('.connection_type').text(value.connection_type);

                    if (value.credentials_group_id) {
                        $(row).find('.credentials_group').text(value.attached_credentials_group_name);
                        $(row).find('.credentials_group').append($('<a>').addClass('detach-credentials-group').attr('data-_id', value.credentials_group_id).html(' (Detach)'));
                    } else {
                        $(row).find('.credentials_group').text(value.credentials_group_name);
                    }
                    $(row).find('.actions').html($('<a>').addClass('delete').attr('data-_id', value._id).html('<img src="img/trash.png" height="18"/>'));
                    row.appendTo(tableBody);
                });
            }
        });
    }

    reloadServerTable();

    function saveServerData(handler, serverData) {

        if (token != 1) {
            return false;
        }

        $.ajax({
            url: "endpoint.php?action=add",
            type: 'post',
            data: serverData,
            beforeSend: function () {
                $(handler).prop('disabled', true).html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
            },
            complete: function () {
                $(handler).prop('disabled', false).text('Add server');
            },
            success: function (data) {
                setResponse(JSON.parse(data));
                reloadServerTable();
            }
        });
    }

    function scanNetwork(target) {

        if (token != 1) {
            return false;
        }

        $.ajax({
            url: "endpoint.php?action=scanNetwork",
            beforeSend: function () {
                $(target).prop('disabled', true).html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
            },
            complete: function () {
                $(target).prop('disabled', false).text('Scan local subnet');
            },
            success: function (data) {
                setResponse(JSON.parse(data));
                getScannedServers();
            }
        });
    }

    function scanAllNetwork(target) {

        if (token != 1) {
            return false;
        }

        $.ajax({
            url: "endpoint.php?action=scanAllNetwork",
            beforeSend: function () {
                $(target).prop('disabled', true).html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
            },
            complete: function () {
                $(target).prop('disabled', false).text('Scan all subnets');
            },
            success: function (data) {
                setResponse(JSON.parse(data));
                getScannedServers();
            }
        });
    }

    function addNetwork(target) {

        if (token != 1) {
            return false;
        }

        $.ajax({
            url: "endpoint.php?action=addNetwork",
            type: 'post',
            data: {
                'subnet': $('#subnet').val(),
            },
            beforeSend: function () {
                $(target).prop('disabled', true).html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
            },
            complete: function () {
                $(target).prop('disabled', false).text('Add subnet');
            },
            success: function (data) {
                setResponse(JSON.parse(data));
                getSubnets();
            }
        });
    }

    function getScannedServers() {
        if (token != 1) {
            return false;
        }

        $("#scannedServers table tbody").html('');
        $.ajax({
            url: "endpoint.php?action=getScannedServers",
            success: function (data) {
                let decodedData = JSON.parse(data);
                $(decodedData.rows).each(function (key, value) {
                    $("#scannedServers table tbody").append($('<tr>').prop('id', key).html('<td class="ip">' + value.doc.ip + '</td><td>' + value.doc.description + '</td><td><a class="addScanned">Add</a></td>'));
                });
            }
        });
    }

    getScannedServers();

    function getSubnets() {
        if (token != 1) {
            return false;
        }

        $("#tblSubnets table tbody").html('');
        $.ajax({
            url: "endpoint.php?action=getSubnets",
            success: function (data) {
                let decodedData = JSON.parse(data);
                $(decodedData.rows).each(function (key, value) {
                    $("#tblSubnets table tbody").append($('<tr>').prop('Subnet', key).html('<td class="subnet">' + value.doc.subnet + '</td><td>' + value.doc.description + '</td><td><a class="delSubnet">Del</a></td>'));
                });
            }
        });
    }

    getSubnets();


    function setResponse(response) {
        $('#alerts .alert-danger, #alerts .alert-success').hide();
        switch (response.type) {
            case 'error':
                $('#alerts .alert-danger').show().text(response.text);
                break;
            case 'message':
                $('#alerts .alert-success').show().text(response.text);
                break;
        }
        $('body, html').animate({
            scrollTop: $('#alerts ').offset().top
        }, 500);
    }

    //add beforeShow afterShow triggers
    jQuery(function ($) {

        var _oldShow = $.fn.show;

        $.fn.show = function (speed, oldCallback) {
            return $(this).each(function () {
                var obj = $(this),
                        newCallback = function () {
                            if ($.isFunction(oldCallback)) {
                                oldCallback.apply(obj);
                            }
                            obj.trigger('afterShow');
                        };

                // you can trigger a before show if you want
                obj.trigger('beforeShow');

                // now use the old function to show the element passing the new callback
                _oldShow.apply(obj, [speed, newCallback]);
            });
        }
    });
});