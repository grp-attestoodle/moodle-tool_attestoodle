/**
 * Call server script ajaxsrvmethod and handle
 * the return.
 *
 * @param @e javascript event or null.
 * @param @args array parameters contents launchid, trainingid and categoryid.
 */
function ajax_certif_generate(e, args) {
    var launchid = -1;
    var trainingid = -1;
    var categoryid = -1;
    if (e instanceof Event) {
        e.preventDefault();
    } else {
        launchid = args[0];
        trainingid = args[1];
        categoryid = args[2];
    }

    var ioconfig = {
        method: 'POST',
        data: {'sesskey': M.cfg.sesskey, 'launchid': launchid.toString(),
            'trainingid': trainingid.toString(),
            'categoryid': categoryid.toString()},
        on: {
            success: function(o, response) {
                var data = Y.JSON.parse(response.responseText);
                displayBar(data.nb);
            },
            failure: function(o, response) {
                alert(response.toSource());
            }
        }
    };

    Y.io(M.cfg.wwwroot + '/admin/tool/attestoodle/classes/generated/ajaxsrvmethod.php', ioconfig);
}