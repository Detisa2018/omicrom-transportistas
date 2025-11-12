<?php

include_once ('data/mysqlUtils.php');
require_once ("softcoatl/SoftcoatlHTTP.php");

$request = com\softcoatl\utils\Request::instance();

$conn = getConnection();

if ($request->has("sTable") && $request->has("query") && $request->has("sSearch")) {

    $jsonResult = array();

    $sTable = $request->get("sTable");
    $sText = $request->get("query");
    $sSearch = $request->get("sSearch");
    $sCondition = $request->get("sCondition");

    $query = "SELECT DISTINCT " . $sSearch . " data, " . $sSearch . " value FROM " . $sTable . " WHERE " . $sSearch . " REGEXP ? " . (empty($sCondition) ? "" : " AND " . $sCondition) . " ORDER BY " . $sSearch;

    if (($ps = $conn->prepare($query))) {
        $ps->bind_param("s", str_replace(' ', '.*', $sText)
        );
        error_log(print_r($ps, true) . "==========================");
        $ps->execute();
    }

    $result = $ps->get_result();

    // Itera sobre el array resultado
    while ($row = $result->fetch_assoc()) {
        $jsonResult[] = $row;
    }
    $conn->close();
    $jsonString = json_encode(array('suggestions' => $jsonResult));
    if ($jsonString == null) {
        error_log(json_last_error());
    }

    echo $jsonString;
}// if valid parameters
?>
