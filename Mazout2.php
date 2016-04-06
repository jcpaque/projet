<?php
session_start();
?>
<html>
<head>
<title>
Pr&eacute;visions Mazout
</title>
</head>
<body>

<?php
$tab = file('prixmazout12mars2016.csv');//Lecture du fichier csv
$tab = array_map('trim', $tab);
$tab2 = array();
foreach($tab as $ele){
 $tab2[] = explode(';', $ele);//création d'un tableau multidimensionnel dans lequel on a les prix 
}
//echo '<pre>';
//print_r($tab2);
//echo '</pre>';
//fin tableau multidimensionnel

$reponse=file_get_contents("http://www.informazout.be/fr/prix_mazout"); 
	$pos_first_bracket=strpos($reponse,"€");   // on cherche la 1ere position de "€" dans le texte
	$pos_second_bracket=strpos($reponse,"<",$pos_first_bracket);  // on cherche la 1ere position de "<" après "€" dans le texte
	$prix1=substr($reponse,$pos_first_bracket+3,$pos_second_bracket-$pos_first_bracket-3);  // = le texte entre € <
	$normal=(float)$prix1;
	//echo "Normal < 2000l: $normal <br/>";
	$pos_first_bracket1=strpos($reponse,"€",$pos_second_bracket);
	$pos_second_bracket1=strpos($reponse,"<",$pos_first_bracket1);
	$prix2=substr($reponse,$pos_first_bracket1+3,$pos_second_bracket1-$pos_first_bracket1-3); 
	$extra=(float)$prix2;
	//echo "Extra < 2000l: $extra <br/>";
	$pos_first_bracket2=strpos($reponse,"€",$pos_second_bracket1);
	$pos_second_bracket2=strpos($reponse,"<",$pos_first_bracket2);
	$prix3=substr($reponse,$pos_first_bracket2+3,$pos_second_bracket2-$pos_first_bracket2-3); 
	$normal1=(float)$prix3;
	//echo "Normal > 2000l: $normal1 <br/>";
	$pos_first_bracket3=strpos($reponse,"€",$pos_second_bracket2);
	$pos_second_bracket3=strpos($reponse,"<",$pos_first_bracket3);
	$prix4=substr($reponse,$pos_first_bracket3+3,$pos_second_bracket3-$pos_first_bracket3-3); 
	$extra1=(float)$prix4;
	//echo "Extra > 2000l: $extra1 <br/>";

$max=count($tab2)-1;
$nbreligne=count($tab2)-1;
//echo $nbreligne;
$hier=explode("/",$tab2[$nbreligne][0]); //date d'hier au format numérique pour comparaison
$date_jour = date ('d/m/Y'); //date du jour
$ajd=explode("/",$date_jour); //explode pour mettre au format numérique
if($ajd != $hier) //si la date de la dernière ligne est différente de la date d'ajd, alors on crée une nouvelle ligne pour y stocker les données d'ajd
{
$ligne=$nbreligne;
$tab2[$ligne][0]=$date_jour;
$tab2[$ligne][1]=$normal;
$tab2[$ligne][2]=$normal1;
$tab2[$ligne][3]=$extra;
$tab2[$ligne][4]=$extra1;
$fp=fopen("prixmazout12mars2016.csv","a");
$data=array($date_jour,$normal,$normal1,$extra,$extra1);
fputcsv($fp,$data,";");

fclose($fp);

}
$d=date('d/m/Y');
echo $d;
echo "<table border='0'>";
echo "<tr>";
echo "<td>";
//echo '<pre>';
//print_r($tab2);
//echo '</pre>';	



//CHECK OCCURRENCE PENTE POSITIVE/NEGATIVE/NULLE 7JOURS ET 1JOUR EXTRA2

//CALCUL PREVISIONS
//-------------------------------------------------------------------------------------------------------
//TECHNIQUE 1:
//Lissage exponentiel Winters
//-------------------------------------------------------------------------------------------------------

//1) PREVISIONS MAZOUT NORMAL < 2000L
echo "<br/>";
echo "PREVISIONS LISSAGE EXPONENTIEL WINTERS: <br/> NORMAL < 2000 <br/>";
$alpha=0.999467584894159;//à déterminer avec le solveur d'excel
$gamma4=0.024065644669399;
$beta=0.992417890865483;
$prevision_normal=array();
$prevision_normal[0][0]="date";
$prevision_normal[0][1]="b";
$prevision_normal[0][2]="a";
$prevision_normal[0][3]="s";
$prevision_normal[0][4]="PREV";

//dates
			for($i=1;$i<=$max;$i++)
			{
				$prevision_normal[$i][0]=$tab2[$i][0];
			}

			$prevision_normal[$max+1][0]=date('d/m/Y',strtotime('+1 days'));
			$prevision_normal[$max+2][0]=date('d/m/Y',strtotime('+2 days'));
			$prevision_normal[$max+3][0]=date('d/m/Y',strtotime('+3 days'));
			$prevision_normal[$max+4][0]=date('d/m/Y',strtotime('+4 days'));
			$prevision_normal[$max+5][0]=date('d/m/Y',strtotime('+5 days'));
			$prevision_normal[$max+6][0]=date('d/m/Y',strtotime('+6 days'));
			$prevision_normal[$max+7][0]=date('d/m/Y',strtotime('+7 days'));
			$prevision_normal[$max+8][0]=date('d/m/Y',strtotime('+8 days'));
			$prevision_normal[$max+9][0]=date('d/m/Y',strtotime('+9 days'));
			$prevision_normal[$max+10][0]=date('d/m/Y',strtotime('+10 days'));
			$prevision_normal[$max+11][0]=date('d/m/Y',strtotime('+11 days'));
			$prevision_normal[$max+12][0]=date('d/m/Y',strtotime('+12 days'));
			
//moyenne des prix historiques du premier trimestre
			for($i=1;$i<=91;$i++)
			{
			$somme=$somme+$tab2[$i][1];
			}
$moyenne=$somme/91;
$prevision_normal[92][1]=$moyenne;
//echo "Moyenne:$moyenne";
//coefficient b ligne 92
$prevision_normal[92][2]=0;
//coefficient b
			for($i=93;$i<=$max;$i++)
			{
				$prevision_normal[$i][1]=($alpha*$tab2[$i][1])+(1-$alpha)*($prevision_normal[$i-1][1]+$prevision_normal[$i-1][2]);
			}
//coefficient a
			for($i=93;$i<=$max;$i++)
			{
				$prevision_normal[$i][2]=$gamma4*($prevision_normal[$i][1]-$prevision_normal[$i-1][1])+$prevision_normal[$i-1][2]*(1-$gamma4);
			}
//coefficient s 3 premiers mois
			for($i=1;$i<=91;$i++)
			{
				$prevision_normal[$i][3]=$tab2[$i][1]-$prevision_normal[92][1];
			}
//coefficient s 
			for($i=90;$i<=$max;$i++)
			{
				$prevision_normal[$i][3]=$beta*($tab2[$i][1]-$prevision_normal[$i][1])-$prevision_normal[$i-91][3]*(1-$beta);
			}


//PREV
$prevision_normal[1][4]="/";
			for($i=90;$i<=$max+1;$i++)
			{
				$prevision_normal[$i][4]=$prevision_normal[$i][1]+$prevision_normal[$i][2]+$prevision_normal[$i-91][3];
			}
//PREV J+n			
echo "<table border='1'>";
			for($i=$max+1;$i<=$max+12;$i++)
			{
				echo "<tr>";
				echo "<td>";
				echo $prevision_normal[$i][0];
				echo "</td>";
				echo "<td>";
				$prevision_normal[$i][4]=$prevision_normal[$max][1]+$prevision_normal[$max][2]+$prevision_normal[$i-91][3];
				echo round($prevision_normal[$i][4],4);
				echo "</td>";
				echo "</tr>";
			}
echo "</table>";
			
//echo '<pre>';
//print_r($prevision_normal);
//echo '</pre>';

//2) PREVISIONS MAZOUT NORMAL > 2000L
echo "<br/>";
echo "PREVISIONS LISSAGE EXPONENTIEL WINTERS: <br/> NORMAL > 2000 <br/>";
$alpha3=0.999637125686788;//à déterminer avec le solveur d'excel
$gamma5=0.0236919674194678;
$beta1=0.997686963676466;
$prevision_normal1=array();
$prevision_normal1[0][0]="date";
$prevision_normal1[0][1]="b";
$prevision_normal1[0][2]="a";
$prevision_normal1[0][3]="s";
$prevision_normal1[0][4]="PREV";
$max=count($tab2)-1;

//dates
			for($i=1;$i<=$max;$i++)
			{
				$prevision_normal1[$i][0]=$tab2[$i][0];
			}

			$prevision_normal1[$max+1][0]=date('d/m/Y',strtotime('+1 days'));
			$prevision_normal1[$max+2][0]=date('d/m/Y',strtotime('+2 days'));
			$prevision_normal1[$max+3][0]=date('d/m/Y',strtotime('+3 days'));
			$prevision_normal1[$max+4][0]=date('d/m/Y',strtotime('+4 days'));
			$prevision_normal1[$max+5][0]=date('d/m/Y',strtotime('+5 days'));
			$prevision_normal1[$max+6][0]=date('d/m/Y',strtotime('+6 days'));
			$prevision_normal1[$max+7][0]=date('d/m/Y',strtotime('+7 days'));
			$prevision_normal1[$max+8][0]=date('d/m/Y',strtotime('+8 days'));
			$prevision_normal1[$max+9][0]=date('d/m/Y',strtotime('+9 days'));
			$prevision_normal1[$max+10][0]=date('d/m/Y',strtotime('+10 days'));
			$prevision_normal1[$max+11][0]=date('d/m/Y',strtotime('+11 days'));
			$prevision_normal1[$max+12][0]=date('d/m/Y',strtotime('+12 days'));

//moyenne des prix historiques du premier trimestre
			for($i=1;$i<=91;$i++)
			{
			$somme1=$somme1+$tab2[$i][2];
			}
$moyenne1=$somme1/91;
$prevision_normal1[92][1]=$moyenne1;
//echo "Moyenne:$moyenne";
//coefficient b ligne 92
$prevision_normal1[92][2]=0;
//coefficient b
			for($i=93;$i<=$max;$i++)
			{
				$prevision_normal1[$i][1]=($alpha3*$tab2[$i][2])+(1-$alpha3)*($prevision_normal1[$i-1][1]+$prevision_normal1[$i-1][2]);
			}
//coefficient a
			for($i=93;$i<=$max;$i++)
			{
				$prevision_normal1[$i][2]=$gamma5*($prevision_normal1[$i][1]-$prevision_normal1[$i-1][1])+$prevision_normal1[$i-1][2]*(1-$gamma5);
			}
//coefficient s 3 premiers mois
			for($i=1;$i<=91;$i++)
			{
				$prevision_normal1[$i][3]=$tab2[$i][2]-$prevision_normal1[92][1];
			}
//coefficient s 
			for($i=92;$i<=$max;$i++)
			{
				$prevision_normal1[$i][3]=$beta1*($tab2[$i][2]-$prevision_normal1[$i][1])-$prevision_normal1[$i-91][3]*(1-$beta1);
			}


//PREV
$prevision_normal1[1][4]="/";
			for($i=92;$i<=$max+1;$i++)
			{
				$prevision_normal1[$i][4]=$prevision_normal1[$i][1]+$prevision_normal1[$i][2]+$prevision_normal1[$i-91][3];
			}
//PREV J+n
echo "<table border='1'>";
			for($i=$max+1;$i<=$max+12;$i++)
			{
				echo "<tr>";
				echo "<td>";
				echo $prevision_normal1[$i][0];
				echo "</td>";
				echo "<td>";
				$prevision_normal1[$i][4]=$prevision_normal1[$max][1]+$prevision_normal1[$max][2]+$prevision_normal1[$i-91][3];
				echo round($prevision_normal1[$i][4],4);
				echo "</td>";
				echo "</tr>";
			}
echo "</table>";
			
//echo '<pre>';
//print_r($prevision_normal1);
//echo '</pre>';

//3) PREVISIONS MAZOUT EXTRA < 2000L
echo "<br/>";
echo "PREVISIONS LISSAGE EXPONENTIEL WINTERS: <br/> EXTRA < 2000: <br/>";
$alpha5=1;//à déterminer avec le solveur d'excel
$gamma6=0;
$beta2=1;
$prevision_extra=array();
$prevision_extra[0][0]="date";
$prevision_extra[0][1]="b";
$prevision_extra[0][2]="a";
$prevision_extra[0][3]="s";
$prevision_extra[0][4]="PREV";
$max=count($tab2)-1;

//dates
			for($i=1;$i<=$max;$i++)
			{
				$prevision_extra[$i][0]=$tab2[$i][0];
			}

			$prevision_extra[$max+1][0]=date('d/m/Y',strtotime('+1 days'));
			$prevision_extra[$max+2][0]=date('d/m/Y',strtotime('+2 days'));
			$prevision_extra[$max+3][0]=date('d/m/Y',strtotime('+3 days'));
			$prevision_extra[$max+4][0]=date('d/m/Y',strtotime('+4 days'));
			$prevision_extra[$max+5][0]=date('d/m/Y',strtotime('+5 days'));
			$prevision_extra[$max+6][0]=date('d/m/Y',strtotime('+6 days'));
			$prevision_extra[$max+7][0]=date('d/m/Y',strtotime('+7 days'));
			$prevision_extra[$max+8][0]=date('d/m/Y',strtotime('+8 days'));
			$prevision_extra[$max+9][0]=date('d/m/Y',strtotime('+9 days'));
			$prevision_extra[$max+10][0]=date('d/m/Y',strtotime('+10 days'));
			$prevision_extra[$max+11][0]=date('d/m/Y',strtotime('+11 days'));
			$prevision_extra[$max+12][0]=date('d/m/Y',strtotime('+12 days'));

//moyenne des prix historiques du premier trimestre
			for($i=1;$i<=91;$i++)
			{
			$somme2=$somme2+$tab2[$i][3];
			}
$moyenne2=$somme2/91;
$prevision_extra[92][1]=$moyenne2;
//echo "Moyenne:$moyenne";
//coefficient b ligne 92
$prevision_extra[92][2]=0;
//coefficient b
			for($i=93;$i<=$max;$i++)
			{
				$prevision_extra[$i][1]=($alpha5*$tab2[$i][3])+(1-$alpha5)*($prevision_extra[$i-1][1]+$prevision_extra[$i-1][2]);
			}
//coefficient a
			for($i=93;$i<=$max;$i++)
			{
				$prevision_extra[$i][2]=$gamma6*($prevision_extra[$i][1]-$prevision_extra[$i-1][1])+$prevision_extra[$i-1][2]*(1-$gamma6);
			}
//coefficient s 3 premiers mois
			for($i=1;$i<=91;$i++)
			{
				$prevision_extra[$i][3]=$tab2[$i][3]-$prevision_extra[92][1];
			}
//coefficient s 
			for($i=92;$i<=$max;$i++)
			{
				$prevision_extra[$i][3]=$beta2*($tab2[$i][3]-$prevision_extra[$i][1])-$prevision_extra[$i-91][3]*(1-$beta2);
			}


//PREV
$prevision_extra[1][4]="/";
			for($i=92;$i<=$max+1;$i++)
			{
				$prevision_extra[$i][4]=$prevision_extra[$i][1]+$prevision_extra[$i][2]+$prevision_extra[$i-91][3];
			}
//PREV J+n		
echo "<table border='1'>";	
			for($i=$max+1;$i<=$max+12;$i++)
			{
				echo "<tr>";
				echo "<td>";
				echo $prevision_extra[$i][0];
				echo "</td>";
				echo "<td>";
				$prevision_extra[$i][4]=$prevision_extra[$max][1]+$prevision_extra[$max][2]+$prevision_extra[$i-91][3];
				echo round($prevision_extra[$i][4],4);
				echo "</td>";
				echo "</tr>";
			}
echo "</table>";
			
//echo '<pre>';
//print_r($prevision_extra);
//echo '</pre>';

//4) PREVISIONS MAZOUT EXTRA > 2000L
echo "<br/>";
echo "PREVISIONS LISSAGE EXPONENTIEL WINTERS: <br/> EXTRA > 2000: <br/>";
$alpha6=0.999060928688368;//à déterminer avec le solveur d'excel
$gamma7=0.0190809488591042;
$beta3=0.991301023836727;
$prevision_extra1=array();
$prevision_extra1[0][0]="date";
$prevision_extra1[0][1]="b";
$prevision_extra1[0][2]="a";
$prevision_extra1[0][3]="s";
$prevision_extra1[0][4]="PREV";
$max=count($tab2)-1;

//dates
			for($i=1;$i<=$max;$i++)
			{
				$prevision_extra1[$i][0]=$tab2[$i][0];
			}

			$prevision_extra1[$max+1][0]=date('d/m/Y',strtotime('+1 days'));
			$prevision_extra1[$max+2][0]=date('d/m/Y',strtotime('+2 days'));
			$prevision_extra1[$max+3][0]=date('d/m/Y',strtotime('+3 days'));
			$prevision_extra1[$max+4][0]=date('d/m/Y',strtotime('+4 days'));
			$prevision_extra1[$max+5][0]=date('d/m/Y',strtotime('+5 days'));
			$prevision_extra1[$max+6][0]=date('d/m/Y',strtotime('+6 days'));
			$prevision_extra1[$max+7][0]=date('d/m/Y',strtotime('+7 days'));
			$prevision_extra1[$max+8][0]=date('d/m/Y',strtotime('+8 days'));
			$prevision_extra1[$max+9][0]=date('d/m/Y',strtotime('+9 days'));
			$prevision_extra1[$max+10][0]=date('d/m/Y',strtotime('+10 days'));
			$prevision_extra1[$max+11][0]=date('d/m/Y',strtotime('+11 days'));
			$prevision_extra1[$max+12][0]=date('d/m/Y',strtotime('+12 days'));

//moyenne des prix historiques du premier trimestre
			for($i=1;$i<=91;$i++)
			{
			$somme3=$somme3+$tab2[$i][4];
			}
$moyenne3=$somme3/91;
$prevision_extra1[92][1]=$moyenne3;
//echo "Moyenne:$moyenne";
//coefficient b ligne 92
$prevision_extra1[92][2]=0;
//coefficient b
			for($i=93;$i<=$max;$i++)
			{
				$prevision_extra1[$i][1]=($alpha6*$tab2[$i][4])+(1-$alpha6)*($prevision_extra1[$i-1][1]+$prevision_extra1[$i-1][2]);
			}
//coefficient a
			for($i=93;$i<=$max;$i++)
			{
				$prevision_extra1[$i][2]=$gamma7*($prevision_extra1[$i][1]-$prevision_extra1[$i-1][1])+$prevision_extra1[$i-1][2]*(1-$gamma7);
			}
//coefficient s 3 premiers mois
			for($i=1;$i<=91;$i++)
			{
				$prevision_extra1[$i][3]=$tab2[$i][4]-$prevision_extra1[92][1];
			}
//coefficient s 
			for($i=92;$i<=$max;$i++)
			{
				$prevision_extra1[$i][3]=$beta3*($tab2[$i][4]-$prevision_extra1[$i][1])-$prevision_extra1[$i-91][3]*(1-$beta3);
			}


//PREV
$prevision_extra1[1][4]="/";
			for($i=92;$i<=$max+1;$i++)
			{
				$prevision_extra1[$i][4]=$prevision_extra1[$i][1]+$prevision_extra1[$i][2]+$prevision_extra1[$i-91][3];
			}
//PREV J+n			
echo "<table border='1'>";	
			for($i=$max+1;$i<=$max+12;$i++)
			{
				echo "<tr>";
				echo "<td>";
				echo $prevision_extra1[$i][0];
				echo "</td>";
				echo "<td>";
				$prevision_extra1[$i][4]=$prevision_extra1[$max][1]+$prevision_extra1[$max][2]+$prevision_extra1[$i-91][3];
				echo round($prevision_extra1[$i][4],4);
				echo "</td>";
				echo "</tr>";
			}
echo "</table>";
			
//echo '<pre>';
//print_r($prevision_extra1);
//echo '</pre>';
//------------------------------------------------------------------------------------------------------------
//TECHNIQUE 2:
//Lissage de Holt
//!!!!!!!!!!!!!\\\\\\\\ Prévisions t+1
//Version améliorée du lissage exponentiel double
//------------------------------------------------------------------------------------------------------------

//1) PREVISIONS MAZOUT NORMAL < 2000L

echo "<br/>";
echo "PREVISIONS LISSAGE DE HOLT: <br/> NORMAL < 2000: <br/>";
$alpha2=0.993197461301334;//à déterminer avec le solveur excel
$gamma=0.000596423702212855;//à déterminer avec le solveur excel
$prevision_normal2=array();
$prevision_normal2[0][0]="date";
$prevision_normal2[0][1]="b";
$prevision_normal2[0][2]="a";
$prevision_normal2[0][3]="PREV";

			for($i=1;$i<=$max;$i++)
			{
				$prevision_normal2[$i][0]=$tab2[$i][0];
			}
$prevision_normal2[$max+1][0]=date('d/m/Y',strtotime('+1 days'));
$prevision_normal2[1][1]=$tab2[1][1];
$prevision_normal2[1][2]="/";
//coefficient b
			for($i=2;$i<=$max;$i++)
			{
				$prevision_normal2[$i][1]=($alpha2*$tab2[$i][1])+(1-$alpha2)*($prevision_normal2[$i-1][1]+$prevision_normal2[$i-1][2]);
			}
//coefficient a
			for($i=2;$i<=$max;$i++)
			{
				$prevision_normal2[$i][2]=$gamma*($prevision_normal2[$i][1]-$prevision_normal2[$i-1][1])+$prevision_normal2[$i-1][2]*(1-$gamma);
			}
//PREV
$prevision_normal2[1][3]="/";
			for($i=2;$i<=$max+1;$i++)
			{
				$prevision_normal2[$i][3]=$prevision_normal2[$i-1][1]+$prevision_normal2[$i-1][2];
			}
		echo "<table border='1'>";
		echo "<tr>";
		echo "<td>";
		echo $prevision_normal2[$max+1][0];
		echo "</td>";
		echo "<td>";
		echo round($prevision_normal2[$max+1][3],4);
		echo "</td>";
		echo "</tr>";
		echo "</table>";
			
//echo '<pre>';
//print_r($prevision_normal2);
//echo '</pre>';

//2) PREVISIONS MAZOUT NORMAL > 2000L

echo "<br/>";
echo "PREVISIONS LISSAGE DE HOLT: <br/> NORMAL >2000: <br/>";
$alpha4=0.999110707379315;//à déterminer avec le solveur excel
$gamma1=0.000582183869459574;//à déterminer avec le solveur excel
$prevision_normal4=array();
$prevision_normal4[0][0]="date";
$prevision_normal4[0][1]="b";
$prevision_normal4[0][2]="a";
$prevision_normal4[0][3]="PREV";

			for($i=1;$i<=$max;$i++)
			{
				$prevision_normal4[$i][0]=$tab2[$i][0];
			}
$prevision_normal4[$max+1][0]=date('d/m/Y',strtotime('+1 days'));
$prevision_normal4[1][1]=$tab2[1][2];
$prevision_normal4[1][2]="/";
//coefficient b
			for($i=2;$i<=$max;$i++)
			{
				$prevision_normal4[$i][1]=($alpha4*$tab2[$i][2])+(1-$alpha4)*($prevision_normal4[$i-1][1]+$prevision_normal4[$i-1][2]);
			}
//coefficient a
			for($i=2;$i<=$max;$i++)
			{
				$prevision_normal4[$i][2]=$gamma1*($prevision_normal4[$i][1]-$prevision_normal4[$i-1][1])+$prevision_normal4[$i-1][2]*(1-$gamma1);
			}
//PREV
$prevision_normal4[1][3]="/";
			for($i=2;$i<=$max+1;$i++)
			{
				$prevision_normal4[$i][3]=$prevision_normal4[$i-1][1]+$prevision_normal4[$i-1][2];
			}
			echo "<table border='1'>";
			echo "<tr>";
			echo "<td>";
			echo $prevision_normal4[$max+1][0];
			echo "</td>";
			echo "<td>";
			echo round($prevision_normal4[$max+1][3],4);
			echo "</td>";
			echo "</tr>";
			echo "</table>";
//echo '<pre>';
//print_r($prevision_normal4);
//echo '</pre>';

//3) PREVISIONS MAZOUT EXTRA < 2000L

echo "<br/>";
echo "PREVISIONS LISSAGE DE HOLT: <br/> EXTRA <2000: <br/>";
$alpha7=1;//à déterminer avec le solveur excel
$gamma2=0;//à déterminer avec le solveur excel
$prevision_extra2=array();
$prevision_extra2[0][0]="date";
$prevision_extra2[0][1]="b";
$prevision_extra2[0][2]="a";
$prevision_extra2[0][3]="PREV";

			for($i=1;$i<=$max;$i++)
			{
				$prevision_extra2[$i][0]=$tab2[$i][0];
			}
$prevision_extra2[$max+1][0]=date('d/m/Y',strtotime('+1 days'));			
$prevision_extra2[1][1]=$tab2[1][3];
$prevision_extra2[1][2]="/";
//coefficient b
			for($i=2;$i<=$max;$i++)
			{
				$prevision_extra2[$i][1]=($alpha7*$tab2[$i][3])+(1-$alpha7)*($prevision_extra2[$i-1][1]+$prevision_extra2[$i-1][2]);
			}
//coefficient a
			for($i=2;$i<=$max;$i++)
			{
				$prevision_extra2[$i][2]=$gamma2*($prevision_extra2[$i][1]-$prevision_extra2[$i-1][1])+$prevision_extra2[$i-1][2]*(1-$gamma2);
			}
//PREV
$prevision_extra2[1][3]="/";
			for($i=2;$i<=$max+1;$i++)
			{
				$prevision_extra2[$i][3]=$prevision_extra2[$i-1][1]+$prevision_extra2[$i-1][2];
			}
			echo "<table border='1'>";
			echo "<tr>";
			echo "<td>";
			echo $prevision_extra2[$max+1][0];
			echo "</td>";
			echo "<td>";
			echo round($prevision_extra2[$max+1][3],4);
			echo "</td>";
			echo "</tr>";
			echo "</table>";
//echo '<pre>';
//print_r($prevision_extra2);
//echo '</pre>';

//4) PREVISIONS MAZOUT EXTRA > 2000L

echo "<br/>";
echo "PREVISIONS LISSAGE DE HOLT: <br/> EXTRA >2000: <br/>";
$alpha8=0.976380180525063;//à déterminer avec le solveur excel
$gamma3=0.000446290023243109;//à déterminer avec le solveur excel
$prevision_extra3=array();
$prevision_extra3[0][0]="date";
$prevision_extra3[0][1]="b";
$prevision_extra3[0][2]="a";
$prevision_extra3[0][3]="PREV";

			for($i=1;$i<=$max;$i++)
			{
				$prevision_extra3[$i][0]=$tab2[$i][0];
			}
$prevision_extra3[$max+1][0]=date('d/m/Y',strtotime('+1 days'));
$prevision_extra3[1][1]=$tab2[1][4];
$prevision_extra3[1][2]="/";
//coefficient b
			for($i=2;$i<=$max;$i++)
			{
				$prevision_extra3[$i][1]=($alpha8*$tab2[$i][4])+(1-$alpha8)*($prevision_extra3[$i-1][1]+$prevision_extra3[$i-1][2]);
			}
//coefficient a
			for($i=2;$i<=$max;$i++)
			{
				$prevision_extra3[$i][2]=$gamma3*($prevision_extra3[$i][1]-$prevision_extra3[$i-1][1])+$prevision_extra3[$i-1][2]*(1-$gamma3);
			}
//PREV
$prevision_extra3[1][3]="/";
			for($i=2;$i<=$max+1;$i++)
			{
				$prevision_extra3[$i][3]=$prevision_extra3[$i-1][1]+$prevision_extra3[$i-1][2];
			}
			echo "<table border='1'>";
			echo "<tr>";
			echo "<td>";
			echo $prevision_extra3[$max+1][0];
			echo "</td>";
			echo "<td>";
			echo round($prevision_extra3[$max+1][3],4);
			echo "</td>";
			echo "</tr>";
			echo "</table>";
//echo '<pre>';
//print_r($prevision_extra3);
//echo '</pre>';
echo "</td>";
echo "<td width='400'>";
echo "</td>";
echo "<td valign='top'>";

//---------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------
//--------------  ---------  --------------  -----------  -----------  -          ----------
//--         ---  --               -         -         -  --       --  -          --
//--         ---  --               -         -         -  --       --  -          --
//--         ---  --               -         - ---------  --       --  -          --
//--------------  --------         -         - - -        --       --  -          ---------
//--              --               -         -    -       --       --  -          --
//--              --               -         -      -     --       --  -          --
//--              ---------        -         -        -   -----------  ---------- ----------
//-------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------
//Tendance prix pétrole

$petrole=file_get_contents("http://prixdubaril.com/"); 
	if((strstr($petrole,'<strong>Haussière</strong>')))
	{
		echo '<span style="font-size: 30pt;">Tendance prix du baril: <br/> <strong><span style="color: #008000;"> <span style="color: red;">Haussi&egrave;re</strong></span></span></span>';
		echo '<br/><span style="font-size: 15pt;"> Augmentation du prix du p&eacute;trole attendue </span>';
	}
	elseif((strstr($petrole,'<strong>Baissière</strong>')))
	{
		echo '<span style="font-size: 30pt;">Tendance prix du baril: <br/><span style="color: #008000;"> <span style="color: green;"><strong>Baissi&egrave;re</strong></span></span></span>';
		echo '<br/><span style="font-size: 15pt;"> Diminution du prix du p$eacute;trole attendue </span>';
	}


$tab1 = file('prixpetrole.csv');//Lecture du fichier csv
$tab1 = array_map('trim', $tab1);
$tabpet = array();
foreach($tab1 as $ele){
 $tabpet[] = explode(';', $ele);//création d'un tableau multidimensionnel dans lequel on a les prix 
}

$get=file_get_contents("http://www.finances.net/matieres_premieres/temps_reel/prix_petrole?type=WTI"); 
	$first_bracket=strpos($get,'"">');  
	$second_bracket=strpos($get,'</div>',$first_bracket); 
	$pet=substr($get,$first_bracket+3,$second_bracket-$first_bracket-3);  
	$petrole = floatval(str_replace(',', '.', $pet));
	$nbreligne3=count($tabpet)-1;
//echo $nbreligne;

// $hier1=explode("/",$tabpet[$nbreligne3][0]); //date d'hier au format numérique pour comparaison
// $date_jour1 = date ('d/m/Y'); //date du jour
// $ajd1=explode("/",$date_jour1); //explode pour mettre au format numérique
// if($ajd1 != $hier1) //si la date de la dernière ligne est différente de la date d'ajd, alors on crée une nouvelle ligne pour y stocker les données d'ajd
// {
// $ligne3=$nbreligne3;
// $tabpet[$ligne3][0]=$date_jour1;
// $tabpet[$ligne3][1]=$petrole;
// $petrole=$petrole*1,13801;
// $fp1=fopen("prixpetrole.csv","a");
// $data1=array($date_jour1,$petrole);
// fputcsv($fp1,$data1,";");

// fclose($fp1);
// }

//1) PREVISIONS PETROLE
echo "</br>";
echo "</br>";
echo "PREVISIONS LISSAGE EXPONENTIEL WINTERS: <br/> PETROLE <br/>";
$alpha9=0.980788498251842;
$gamma9=0.0302723612328884;
$beta9=0.992564626761187;
$petroleprev=array();
$max=count($tabpet)-1;
//dates
			for($i=0;$i<=$max;$i++)
			{
				$petroleprev[$i][0]=$tabpet[$i][0];
			}

			$petroleprev[$max+1][0]=date('d/m/Y',strtotime('+1 days'));
			$petroleprev[$max+2][0]=date('d/m/Y',strtotime('+2 days'));
			$petroleprev[$max+3][0]=date('d/m/Y',strtotime('+3 days'));
			$petroleprev[$max+4][0]=date('d/m/Y',strtotime('+4 days'));
			$petroleprev[$max+5][0]=date('d/m/Y',strtotime('+5 days'));
			$petroleprev[$max+6][0]=date('d/m/Y',strtotime('+6 days'));
			$petroleprev[$max+7][0]=date('d/m/Y',strtotime('+7 days'));
			$petroleprev[$max+8][0]=date('d/m/Y',strtotime('+8 days'));
			$petroleprev[$max+9][0]=date('d/m/Y',strtotime('+9 days'));
			$petroleprev[$max+10][0]=date('d/m/Y',strtotime('+10 days'));
			$petroleprev[$max+11][0]=date('d/m/Y',strtotime('+11 days'));
			$petroleprev[$max+12][0]=date('d/m/Y',strtotime('+12 days'));
			
//moyenne des prix historiques du premier trimestre

			for($i=0;$i<=90;$i++)
			{
			$somme1=$somme1+$tabpet[$i][1];
			}
$moyenne1=$somme1/91;
$petroleprev[90][1]=$moyenne1;
//echo "Moyenne:$moyenne";
//coefficient b ligne 92
$petroleprev[90][2]=0;
//coefficient b
			for($i=91;$i<=$max;$i++)
			{
				$petroleprev[$i][1]=($alpha9*$tabpet[$i][1])+(1-$alpha9)*($petroleprev[$i-1][1]+$petroleprev[$i-1][2]);
			}
//coefficient a
			for($i=91;$i<=$max;$i++)
			{
				$petroleprev[$i][2]=$gamma9*($petroleprev[$i][1]-$petroleprev[$i-1][1])+$petroleprev[$i-1][2]*(1-$gamma9);
			}
//coefficient s 3 premiers mois
			for($i=0;$i<=90;$i++)
			{
				$petroleprev[$i][3]=$tabpet[$i][1]-$petroleprev[90][1];
			}
//coefficient s 
			for($i=91;$i<=$max;$i++)
			{
				$petroleprev[$i][3]=$beta9*($tabpet[$i][1]-$petroleprev[$i][1])-$petroleprev[$i-91][3]*(1-$beta9);
			}


//PREV
$petroleprev[1][4]="/";
			for($i=91;$i<=$max;$i++)
			{
				$petroleprev[$i][4]=$petroleprev[$i][1]+$petroleprev[$i][2]+$petroleprev[$i-91][3];
			}
//PREV J+n			
echo "<table border='1'>";
			for($i=$max;$i<=$max+12;$i++)
			{
				echo "<tr>";
				echo "<td>";
				echo $petroleprev[$i][0];
				echo "</td>";
				echo "<td>";
				$petroleprev[$i][4]=$petroleprev[$max][1]+$petroleprev[$max][2]+$petroleprev[$i-91][3];
				echo round($petroleprev[$i][4],4);
				echo "</td>";
				echo "</tr>";
			}
echo "</table>";

//--------------------------------------DONNEES STATS SUR LES PENTES----------------------------------
//-------------------------------------------------------------------
//--------------------------------------------------------------------------- (voir les 4 fichiers php à part)
//--------------------------------------METTRE A JOUR SI NECESSAIRE-------------------------------------------
$nbrejoursconsmaxpente0NORMAL1=43;
$nbrejoursconsmoypente0NORMAL1=9.1437908496732;
$nbrejoursconsmaxpente0NORMAL2=43;
$nbrejoursconsmoypente0NORMAL2=9.2171052631579;
$nbrejoursconsmaxpente0EXTRA1=47;
$nbrejoursconsmoypente0EXTRA1=9.1437908496732;
$nbrejoursconsmaxpente0EXTRA2=47;
$nbrejoursconsmoypente0EXTRA2=9.2171052631579;
$nbrejoursconsmaxpente0PETROLE=10;
$nbrejoursconsmoypente0PETROLE=2.1652173913043;
$nbrejoursconsmaxpentePOSITIVE_NORMAL1=2;
$nbrejoursconsmoypentePOSITIVE_NORMAL1=1.0289855072464;
$nbrejoursconsmaxpentePOSITIVE_NORMAL2=2;
$nbrejoursconsmoypentePOSITIVE_NORMAL2=1.0294117647059;
$nbrejoursconsmaxpentePOSITIVE_EXTRA1=2;
$nbrejoursconsmoypentePOSITIVE_EXTRA1=1.0298507462687;
$nbrejoursconsmaxpentePOSITIVE_EXTRA2=2;
$nbrejoursconsmoypentePOSITIVE_EXTRA2=1.0294117647059;
$nbrejoursconsmaxpentePOSITIVE_PETROLE=5;
$nbrejoursconsmoypentePOSITIVE_PETROLE=1.6180124223602;
$nbrejoursconsmaxpenteNEGATIVE_NORMAL1=2;
$nbrejoursconsmoypenteNEGATIVE_NORMAL1=1.0117647058824;
$nbrejoursconsmaxpenteNEGATIVE_NORMAL2=2;
$nbrejoursconsmoypenteNEGATIVE_NORMAL2=1.0119047619048;
$nbrejoursconsmaxpenteNEGATIVE_EXTRA1=2;
$nbrejoursconsmoypenteNEGATIVE_EXTRA1=1.0232558139535;
$nbrejoursconsmaxpenteNEGATIVE_EXTRA2=2;
$nbrejoursconsmoypenteNEGATIVE_EXTRA2=1.0119047619048;
$nbrejoursconsmaxpenteNEGATIVE_PETROLE=5;
$nbrejoursconsmoypenteNEGATIVE_PETROLE=1.5446685878963;
//------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------

?>
</body>
</html>