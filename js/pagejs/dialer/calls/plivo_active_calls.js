$(document).ready(function() {
    $("table#staffing_attrition_report tr:even").css("background-color", "#F4F4F8");
    $("table#staffing_attrition_report tr:odd").css("background-color", "#FFFFFF");

    $("#qa_item").addClass("active open");
    $("#active_calls").addClass("active");
});