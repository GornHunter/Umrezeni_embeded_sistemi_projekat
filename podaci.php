<?php
	$conn = new mysqli("localhost", "karadzic.e195", "L2@zskEx", "db_karadzic_e195");
	if ($conn->connect_error)
		die("Connection failed: " . $conn->connect_error);
	
	$result = $conn->query("SELECT temperatura, vlaznost, pritisak, osvetljenje, vreme FROM podaci");
	
	$vreme = $temperatura = $vlaznost = $pritisak = $osvetljenje = "";
	while($row = $result->fetch_assoc())
	{
		if ($vreme != "")
		{
			$vreme .= ", ";
			$temperatura .= ", ";
			$vlaznost .= ", ";
			$pritisak .= ", ";
			$osvetljenje .= ", ";
		}
		$vreme .= "'" . $row["vreme"] . "'";
		$temperatura .= $row["temperatura"];
		$vlaznost .= $row["vlaznost"];
		$pritisak .= $row["pritisak"];
		$osvetljenje .= $row["osvetljenje"];
	}
	
	$conn->close();
?>

<html>
	<head>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script>
	</head>
	<body style="background-color:#cfcfcf;">
		<h1 style="text-align:center;font-size:50px;">Podaci</h1>
		
		<canvas style="position:absolute;left:300px;top:150px;" id="temperaturaChart" width = "500" height = "300"></canvas>
		<script>
			const dataTemp = [<?php echo $temperatura; ?>];
			const backgroundColorTemp = [];
			
			for(i = 0; i < dataTemp.length; i++){
				if(dataTemp[i] < 15)
					backgroundColorTemp.push('rgb(0, 63, 252)')
				if(dataTemp[i] >= 15 && dataTemp[i] < 20)
					backgroundColorTemp.push('rgb(0, 176, 252)')
				if(dataTemp[i] >= 20 && dataTemp[i] < 25)
					backgroundColorTemp.push('rgb(252, 172, 0)')
				if(dataTemp[i] >= 25)
					backgroundColorTemp.push('rgb(252, 109, 0)')
			}
			
			var ctx = document.getElementById("temperaturaChart").getContext("2d");
			var myChart = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: [<?php echo $vreme; ?>],
					datasets: [{
						label: 'Temperatura',
						data: dataTemp,
						fill: true,
						borderColor: 'black',
						borderWidth: 0.5,
						backgroundColor: backgroundColorTemp
					}]
				},
				options: {
					responsive: false,
					maintainAspectRatio: false
				}
			});
		</script>
		
		<canvas style="position:absolute;left:900px;top:150px;" id="vlaznostChart" width = "500" height = "300"></canvas>
		<script>
			const dataVlaznost = [<?php echo $vlaznost; ?>];
			const backgroundColorVlaznost = [];
			
			for(i = 0; i < dataVlaznost.length; i++){
				if(dataVlaznost[i] < 25 || dataVlaznost[i] >= 70)
					backgroundColorVlaznost.push('rgb(255, 21, 0)')
				if((dataVlaznost[i] >= 25 && dataVlaznost[i] < 30) || (dataVlaznost[i] >= 60 && dataVlaznost[i] < 70))
					backgroundColorVlaznost.push('rgb(255, 196, 0)')
				if(dataVlaznost[i] >= 30 && dataVlaznost[i] < 60)
					backgroundColorVlaznost.push('rgb(36, 201, 47)')
			}
			
			var ctx = document.getElementById("vlaznostChart").getContext("2d");
			var myChart = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: [<?php echo $vreme; ?>],
					datasets: [{
						label: 'Vlaznost',
						data: dataVlaznost,
						fill: true,
						borderColor: 'black',
						borderWidth: 0.5,
						backgroundColor: backgroundColorVlaznost
					}]
				},
				options: {
					responsive: false,
					maintainAspectRatio: false
				}
			});
		</script>
		
		<canvas style="position:absolute;left:300px;top:550px;" id="pritisakChart" width = "500" height = "300"></canvas>
		<script>
			const dataPritisak = [<?php echo $pritisak; ?>];
			const backgroundColorPritisak = [];
			
			for(i = 0; i < dataPritisak.length; i++){
				if(dataPritisak[i] >= 1022.689)
					backgroundColorPritisak.push('rgb(0, 8, 255)')
				if(dataPritisak[i] > 988.8255 && dataPritisak[i] < 1022.689)
					backgroundColorPritisak.push('rgb(0, 255, 8)')
				if(dataPritisak[i] <= 988.8255)
					backgroundColorPritisak.push('rgb(255, 38, 0)')
			}
			
			var ctx = document.getElementById("pritisakChart").getContext("2d");
			var myChart = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: [<?php echo $vreme; ?>],
					datasets: [{
						label: 'Pritisak',
						data: dataPritisak,
						fill: true,
						borderColor: 'black',
						borderWidth: 0.5,
						backgroundColor: backgroundColorPritisak
					}]
				},
				options: {
					responsive: false,
					maintainAspectRatio: false
				}
			});
		</script>
		
		<canvas style="position:absolute;left:900px;top:550px;" id="osvetljenjeChart" width = "500" height = "300"></canvas>
		<script>
			const dataOsvetljenje = [<?php echo $osvetljenje; ?>];
			const backgroundColorOsvetljenje = [];
			
			for(i = 0; i < dataOsvetljenje.length; i++){
				backgroundColorOsvetljenje.push('rgb(255, 234, 0)')
			}
			
			var ctx = document.getElementById("osvetljenjeChart").getContext("2d");
			var myChart = new Chart(ctx, {
				
				type: 'bar',
				data: {
					labels: [<?php echo $vreme; ?>],
					datasets: [{
						label: 'Osvetljenje',
						data: dataOsvetljenje,
						fill: true,
						borderColor: 'black',
						borderWidth: 0.5,
						backgroundColor: backgroundColorOsvetljenje
					}]
				},
				options: {
					responsive: false,
					maintainAspectRatio: false
				}
			});
		</script>
	</body>
</html>