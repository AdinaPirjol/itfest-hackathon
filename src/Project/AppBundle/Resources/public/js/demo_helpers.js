

//----------------------------------------------
//
// Before you dive into these functions, remember that we've included them in the bundle just for demo purposes
//
//----------------------------------------------

function escapeHtml(unsafe) {
    return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
};

function cutWhiteSpace() {
    var offset = escapeHtml(example).match(/^\s+/)[0].length;
    code = escapeHtml(example).split('\n').map(function (line) {
        return line.slice(offset - 1);
    });
}

function showcasing() {
    $('.show-panel-example').each(function () {
        var example = $(this).find('[data-showcase="example"]').html();        
        if(example){
            var offset = escapeHtml(example).match(/^\s+/)[0].length;
            code = escapeHtml(example).split('\n').map(function (line) {
                return line.slice(offset - 1);
            });
            $(this).find('[data-showcase="code"]').append(code.join('\n'));
        }
    });
};

function updateSideBarCode() {
    var fixedStatus = $('input[name="sidebar_fixed_status"]:checked').val();
    var expandedStatus = $('input[name="sidebar_expanded_status"]:checked').val();
    var sidebarClasses = 'sidebar';
    var controlButtonIconClasses = 'menu-icon fa';

    if (fixedStatus == 'fixed') {
        sidebarClasses += ' sidebar-fixed';
    }

    switch (expandedStatus) {
        case 'expanded' :
            controlButtonIconClasses += ' fa-arrow-left';
            break;
        case 'collapsed' :
            sidebarClasses += ' sidebar-min';
            controlButtonIconClasses += ' fa-arrow-right';
            break;
    }

    $('#sidebar_classes').html(sidebarClasses);
    $('#sidebar_control_button_icon_classes').html(controlButtonIconClasses);
}

function updateFooterBarCode() {
    var fixedStatus = $('input[name="footer_fixed_status"]:checked').val();
    switch (fixedStatus) {
        case 'fixed' :
            $('#footer_fixed_code').removeClass('hide');
            $('#footer_unfixed_code').addClass('hide');
            break;
        case 'unfixed' :
            $('#footer_fixed_code').addClass('hide');
            $('#footer_unfixed_code').removeClass('hide');
            break;
    }
}

// This function is just for demo purposes. It controls the way the expanding sidebar menu is displayed.
function staticNavigation() {
    var path = window.location.pathname + window.location.hash;

    $("#sidebar .menu-item").removeClass('active');

    $("#sidebar .menu-item > a").each(function () {
        var href = $(this).attr('href');
        if (href == '#') {
            href = $(this).attr('data-href');
        }
        if (path == href) {
            $(this).parents('.menu-item-has-children').addClass('active');
            $(this).closest('.menu-item').addClass('active');
        }
    });

    $(window).resize();
}

function demoHelpers() {
    staticNavigation();
    $(window).on('hashchange', function() {
        staticNavigation();
    });
}