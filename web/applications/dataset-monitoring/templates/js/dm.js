var fund_src_map = {
    {% for fund in funds %}
        '{{fund.Abbr}}': '{{fund.ID}}',
    {% endfor %}
};

var rev_fund_src_map = {
    {% for fund in funds %}
        '{{fund.ID}}': '{{fund.Abbr}}',
    {% endfor %}
};

var project_map = {
    {% for project in projects %}
        '{{project.Abbr}}': '{{project.ID}}',
    {% endfor %}
}

var rev_project_map = {
    {% for project in projects %}
        '{{project.ID}}': '{{project.Abbr}}',
    {% endfor %}
}

var $ = jQuery.noConflict();

$(document).ready(function() {
    $('#menu .overview').width($('#menu .viewport').width() - 15);

    $(window).hashchange( function(){
        var m = location.hash.match(/^#([^\/]+)\/?([^\/]+)?/);
        if (m) {
            if (typeof m[1] !== 'undefined') {
                if (typeof m[2] === 'undefined') {
                    $("#tree").jstree("select_node", $('#datasets_projectId_' + m[1]), true);
                    $("#tree").jstree("select_node", $('#tasks_projectId_' + m[1]), true);
                }
                else {
                    $("#tree").jstree("open_node", $('#projects_fundSrc_' + map_fund_src(m[1])));
                    $("#tree").jstree("select_node", $('#datasets_projectId_' + map_project(m[2])), true);
                    $("#tree").jstree("select_node", $('#tasks_projectId_' + map_project(m[2])), true);
                }
            }
        }
    })


});

function showProjects(by,id) {
    $('#content .overview').html('<div class="spinner"><div><img src="{{baseUrl}}/includes/images/spinner.gif"></div></div>');
    $('div.spinner').height($('#content .viewport').height()-12);
    $.ajax({
        "url": "{{baseUrl}}/projects/" + by + "/" + id,
        "success": function(data) {
            $('#content .overview').html(data);
            $('#content .overview td[title]').qtip({
                position: {
                    my: 'right bottom',
                    at: 'center',
                    adjust: {
                        x: -8
                    },
                    viewport: $(window)
                },
                show: {
                    event: "mouseenter focus",
                    solo: true
                },
                hide: {
                    fixed: true,
                    delay: 100
                },
                style: {
                    classes: "qtip-default qtip-tipped"
                }
            });
            $('#content .overview th[title]').qtip({
                position: {
                    my: 'bottom center',
                    at: 'top center',
                    viewport: $(window)
                },
                show: {
                    event: "mouseenter focus",
                    solo: true
                },
                hide: {
                    fixed: true,
                    delay: 100
                },
                style: {
                    classes: "qtip-default qtip-tipped"
                }
            });
            $('#content .overview td.details').each(function() {
                $(this).qtip({
                    content: {
                        text: "loading...",
                        ajax: {
                            url: "{{baseUrl}}/dataset_details/" + escape($(this).parent().attr('udi')),
                            loading: false
                        }
                    },
                    position: {
                        my: 'right bottom',
                        at: 'middle left',
                        viewport: $(window),
                        effect: false,
                        target: $(this).parent()
                    },
                    show: {
                        event: "mouseenter focus",
                        solo: true
                    },
                    hide: {
                        fixed: true,
                        delay: 100
                    },
                    style: {
                        classes: "qtip-default qtip-tipped"
                    }
                });
            });
            $('#content .overview table.tablesorter').tablesorter({
                sortList: [[0,0]],
                sortRestart : true,
                sortInitialOrder: 'asc'
            });
            $(".sbarchart").each(function() {

    var series = [{
        data: [
            [15, 0]
        ],
        label: "Identified"
    },
    {
        data: [
            [10, 0]
        ],
        label: "Registered"
    }, {
        data: [
            [9, 0]
        ],
        label: "Available"
    }];

    var options = {
        xaxis: {
            minTickSize: 1
        },
        yaxis: {
            ticks: false
        },
        series: {
            bars: {
                align: "center",
                show: true,
                barWidth: .9,
                horizontal: true
            },
            stack: true
        }
    };
                $.plot((this), series, options);
            });
        }
    });
}

function updateHash(fund_src,project_id) {
    location.href = '#' + rev_map_fund_src(fund_src) + '/' + rev_map_project(project_id);
}

function map_fund_src(name) {
    if (name in fund_src_map) return fund_src_map[name];
    return name;
}

function rev_map_fund_src(name) {
    if (name in rev_fund_src_map) return rev_fund_src_map[name];
    return name;
}

function map_project(name) {
    if (name in project_map) return project_map[name];
    return name;
}

function rev_map_project(name) {
    if (name in rev_project_map) return rev_project_map[name];
    return name;
}
