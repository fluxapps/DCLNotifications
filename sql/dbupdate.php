<#1>
<?php
	require_once('./Services/ActiveRecord/Fields/Converter/class.arBuilder.php');
	require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/Config/class.srPHBernDclNotificationsConfig.php');

	srPHBernDclNotificationsConfig::installDB();
	//$arBuilder = new arBuilder(new srDclContentImporterConfig());
    //$arBuilder->generateDBUpdateForInstallation();
?>