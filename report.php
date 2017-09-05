<form method="get">
<input type="text" name="host" size="40">
<input type="submit" value="Выбрать">
</form>

<?php 
	if(isset($_GET['host']))
	{
		$host = $_GET['host'];
	}
	$db = mysqli_connect("localhost", "eduard", "Oblivion_5", "reporting");
	$host = mysqli_real_escape_string($db,$host);
	echo "Отчет по галереям $host. ";
	echo "<br><br>Общее количество загрузок всех галерей на сайте: ";

	$sql="SELECT sum(uniqueEvents) from report where hostname='$host' and eventAction like '%load%'";
	$query=mysqli_query($db,$sql);

	while ($row = mysqli_fetch_array($query)){
		print($row['sum(uniqueEvents)']);
		$load=$row['sum(uniqueEvents)'];
	}

	echo "<br>Взаимодействие с галереями (клики на картинки): ";

	$sql="SELECT sum(totalEvents) from report where hostname='$host' and eventAction like '%clickPic%'";
	$query=mysqli_query($db,$sql);

	while ($row = mysqli_fetch_array($query)){
		print($row['sum(totalEvents)']);
		$action=$row['sum(totalEvents)'];
	}

    echo "<br>Переходы по ссылкам в публикации: ";

	$sql="SELECT sum(totalEvents) from report where hostname='$host' and eventAction like '%clickLink%'";
	$query=mysqli_query($db,$sql);

	while ($row = mysqli_fetch_array($query)){
		print($row['sum(totalEvents)']);
		$link=$row['sum(totalEvents)'];
	}

	$kof = ($action/$load)*10000;
	$kof = floor($kof);
	$kof = $kof / 100;
 	echo "<br>К-взаимодействия: $kof %";
 	$kof = ($link/$action)*10000;
 	$kof = floor($kof);
	$kof = $kof / 100;
 	echo "<br>К-перехода по ссылкам: $kof %";

 	echo "<br><br>Топ 5 страниц/загрузок галереи<br>";

 	$sql="SELECT pagePath, sum(uniqueEvents) from report where hostname='$host' and eventAction like '%load%' group by 1 order by 2 desc limit 5";
	$query=mysqli_query($db,$sql);
	echo "Страница";
	echo "		Загрука галерей";
	echo "		Взаимодействие с галереями ";
	echo "		Переходы по ссылкам ";
	echo "		К-взаимодействия: ";
	echo "		К-перехода по ссылкам: <br>";
	$row1 = array();
	$row2 = array();
	$row3 = array();
	while ($row = mysqli_fetch_array($query)){
		
		print($row['pagePath']);
		$page = $row['pagePath'];
		echo " | ";
		print($row['sum(uniqueEvents)']);
		array_push($row1,$row['sum(totalEvents)']);
		$sql1="SELECT sum(totalEvents) from report where hostname='$host' and pagePath = '$page' and  eventAction like '%clickPic%'";
		$query1 = mysqli_query($db,$sql1);
		while ($wor = mysqli_fetch_array($query1)){
			echo " | ";
			print($wor['sum(totalEvents)']);
			$pic=$wor['sum(totalEvents)'];
		}
		$sql1="SELECT sum(totalEvents) from report where hostname='$host' and pagePath = '$page' and  eventAction like '%clickLink%'";
		$query1 = mysqli_query($db,$sql1);
		while ($wor = mysqli_fetch_array($query1)){
			echo " | ";
			print($wor['sum(totalEvents)']);
			$link=$wor['sum(totalEvents)'];
			echo " | ";
		}
		$kof = ($pic/$row['sum(uniqueEvents)'])*10000;
		$kof = floor($kof);
		$kof = $kof / 100;
		echo "$kof % | ";

		$kof = ($link/$pic)*10000;
		$kof = floor($kof);
		$kof = $kof / 100;
		echo "$kof %";
		echo "<br>";
	}

	echo "<br><br>Топ 20 картинок по кликам <br>";
	$sql="SELECT sum(totalEvents),substr(eventLabel, instr(eventLabel, '_igCode')+7, 11) from report where hostname='$host' and eventAction like '%clickPic%' group by 2 order by 1 desc limit 20";
	$query=mysqli_query($db,$sql);
	echo "Ссылка на публикацию ";
	echo "Код instagram ";
	echo "Клики <br>";
	while ($row = mysqli_fetch_array($query)){
		echo "https://www.instagram.com/p/".$row[1]." | ";
		print($row[1]);
		echo " | ";
		print($row[0]);
		echo "<br>";
	}

	echo "<br><br>Топ 20 картинок по переходам по ссылкам <br>";
	$sql="SELECT sum(totalEvents),substr(eventLabel, instr(eventLabel, '_igCode')+7, 11) from report where hostname='$host' and eventAction like '%clickLink%' group by 2 order by 1 desc limit 20";
	$query=mysqli_query($db,$sql);
	echo "Ссылка на публикацию ";
	echo "Код instagram ";
	echo "Переходы <br>";
	while ($row = mysqli_fetch_array($query)){
		echo "https://www.instagram.com/p/".$row[1]." | ";
		print($row[1]);
		echo " | ";
		print($row[0]);
		echo "<br>";
	}
?>