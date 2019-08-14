document.addEventListener("DOMContentLoaded", function (e) {
  if(typeof($$("#admin_checkbox")[0]) != "undefined") {
      $$("#admin_checkbox")[0].on("change", function() {
    // alert($$("#admin_checkbox")[0].value);
     document.getElementById('admin_submit').disabled = !document.getElementById('admin_submit').disabled
  });
  }

});