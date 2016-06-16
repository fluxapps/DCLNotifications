<#1>
<?php
	require_once('./Services/ActiveRecord/Fields/Converter/class.arBuilder.php');
	require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/Config/class.srPHBernDclNotificationsConfig.php');

	srPHBernDclNotificationsConfig::installDB();
	//$arBuilder = new arBuilder(new srDclContentImporterConfig());
    //$arBuilder->generateDBUpdateForInstallation();
?>
<#2>
<?php
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/Config/class.srPHBernDclNotificationsConfig.php');

$ilDB->modifyTableColumn(
	srPHBernDclNotificationsConfig::returnDbTableName(),
	'value',
	array(
		"type" => "text",
		"length" => 4000,
		"notnull" => false,
		'fixed' => false
	)
);

?>
