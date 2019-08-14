document.addEventListener("DOMContentLoaded", function (e) {
    if(typeof($$("#tagalys_admin_core_agree_cron_enabled")[0]) != "undefined" && typeof($$("#tagalys_admin_core_agree_start_sync")[0]) != "undefined") {
        document.getElementById('tagalys_admin_core_agree_cron_enabled').onclick = tagalysCheckAndEnableSyncButton;
        document.getElementById('tagalys_admin_core_agree_start_sync').onclick = tagalysCheckAndEnableSyncButton;
        // $$("#tagalys_admin_core_agree_start_sync")[0].on("change", function () {
        //     // alert($$("#tagalys_admin_core_agree_start_sync")[0].value);
        //     document.getElementById('tagalys_admin_core_submit').disabled = !document.getElementById('tagalys_admin_core_submit').disabled;
        // });
    }
});
function tagalysCheckAndEnableSyncButton() {
    document.getElementById('tagalys_admin_core_submit').disabled = !(document.getElementById('tagalys_admin_core_agree_cron_enabled').checked && document.getElementById('tagalys_admin_core_agree_start_sync').checked);
}