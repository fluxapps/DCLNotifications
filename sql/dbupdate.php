<#1>
<?php
	require_once('./Services/ActiveRecord/Fields/Converter/class.arBuilder.php');
	require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/DCLNotifications/Config/class.srDCLNotificationsConfig.php');

	srDCLNotificationsConfig::installDB();
	//$arBuilder = new arBuilder(new srDclContentImporterConfig());
    //$arBuilder->generateDBUpdateForInstallation();
?>
<#2>
<?php
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/DCLNotifications/Config/class.srDCLNotificationsConfig.php');

$ilDB->modifyTableColumn(
	srDCLNotificationsConfig::returnDbTableName(),
	'value',
	array(
		"type" => "text",
		"length" => 4000,
		"notnull" => false,
		'fixed' => false
	)
);

?>
<#3>
<?php
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/DCLNotifications/Config/class.srDCLNotificationsConfig.php');

$ilDB->modifyTableColumn(
	srDCLNotificationsConfig::returnDbTableName(),
	'value',
	array(
		"type" => "clob",
	)
);
?>