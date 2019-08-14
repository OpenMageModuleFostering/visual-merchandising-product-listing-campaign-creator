document.addEventListener("DOMContentLoaded", function (e) {
    if(typeof($$("#tagalys_admin_core_agree_start_sync")[0]) != "undefined") {
        $$("#tagalys_admin_core_agree_start_sync")[0].on("change", function () {
            // alert($$("#tagalys_admin_core_agree_start_sync")[0].value);
            document.getElementById('tagalys_admin_core_submit').disabled = !document.getElementById('tagalys_admin_core_submit').disabled;
        });
    }
});