<?
$moduleId = "awz.utm";

$connection = \Bitrix\Main\Application::getConnection();
$checkColumn = false;
$checkTable = false;
$recordsRes = $connection->query("select * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='b_awz_utm'");
while($dt = $recordsRes->fetch()){
	$checkTable = true;
	if($dt['COLUMN_NAME'] == 'PAGE'){
		$checkColumn = true;
		break;
	}

}
if($checkTable && !$checkColumn){
	$sql = 'ALTER TABLE `b_awz_utm` ADD `PAGE` varchar(255) DEFAULT NULL';
	$connection->queryExecute($sql);
}