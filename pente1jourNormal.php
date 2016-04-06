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

//PENTE 1JOUR PRIX NORMAL
$normal1jour=array();
for($i=0;$i<=$max;$i++)
{
	$normal1jour[$i][0]=$i;
}
for($i=0;$i<=1;$i++)
{
	$normal1jour[$i][1]=0;
}
for($i=2;$i<=$max;$i++)
{
	$normal1jour[$i][1]=($tab2[$i][1]-$tab2[$i-1][1])/($normal1jour[$i][0]-$normal1jour[$i-1][0]);
}
// echo '<pre>';
// print_r($normal1jour);
// echo '</pre>';

//PENTE 7JOURS PRIX NORMAL
$pentenormal1=array();

for($i=0;$i<=$max;$i++)
{
	$pentenormal1[$i][0]=$i;
}
for($i=0;$i<=6;$i++)
{
	$pentenormal1[$i][1]=0;
}
for($i=7;$i<=$max;$i++)
{
	$pentenormal1[$i][1]=($tab2[$i][1]-$tab2[$i-6][1])/($pentenormal1[$i][0]-$pentenormal1[$i-6][0]);
}
// echo '<pre>';
// print_r($pentenormal1);
// echo '</pre>';

//CHECK OCCURRENCE PENTE POSITIVE/NEGATIVE/NULLE 1JOUR 

//1JOUR----------------------------------------------------------------------------------------------
$maxlignes=count($normal1jour);
$normaljours11=array();
$normaljours12=array();
$normaljours13=array();
$nbr0normaljours1=array();
$resultnormaljours1=array();
$resultnormaljours12=array();
$resultnormaljours13=array();
$nbrposnormaljours1=array();
$nbrnegnormaljours1=array();


for($i=0;$i<=$maxlignes-1;$i++)
{
	
	switch($normal1jour[$i][1])
	{
		case "0":
		$nbrzero[$i]=count($normal1jour[$i][1]);
		$zero=count($normal1jour[$i][1]);
		$normaljours11[$i]=$nbrzero;
		break;
		
		case ($normal1jour[$i][1]<0):
		$nbrnega[$i]=count($normal1jour[$i][1]);
		$normaljours12[$i]=$nbrnega;
		break;
		
		case ($normal1jour[$i][1]>0):
		$nbrpositif[$i]=count($normal1jour[$i][1]);
		$normaljours13[$i]=$nbrpositif;
		break;
		
		
		
	}


}
for($i=0;$i<=$maxlignes-1;$i++)
{
	$resultnormaljours1[$i]=$normaljours11[count($resultnormaljours1)][$i];
	$resultnormaljours12[$i]=$normaljours12[count($resultnormaljours12)][$i];
	$resultnormaljours13[$i]=$normaljours13[count($resultnormaljours13)][$i];
	
}
// echo '<pre>';
// print_r($resultnormaljours1);
// echo '</pre>';

// echo '<pre>';
// print_r($resultnormaljours12);
// echo '</pre>';

// echo '<pre>';
// print_r($resultnormaljours13);
// echo '</pre>';

//pente nulle
$indiceTabnormaljours1 = 0;
 
for ($x = 0 ; $x < count($resultnormaljours1) ; $x ++)
{
    if ($resultnormaljours1[$x] == 1)
	{
        $y = $x;
		
        while($resultnormaljours1[$y] != 0)
		{
            $nbr0normaljours1[$indiceTabnormaljours1]++;
            $y++;

		}
        $indiceTabnormaljours1 ++;
		
        $x=$y;
		
	}

}

//pente négative
$indiceTabnormaljours12 = 0;
 
for ($x = 0 ; $x < count($resultnormaljours12) ; $x ++)
{
    if ($resultnormaljours12[$x] == 1)
	{
        $y = $x;
		
        while($resultnormaljours12[$y] != 0)
		{
            $nbrnegnormaljours1[$indiceTabnormaljours12]++;
            $y++;

		}
        $indiceTabnormaljours12 ++;
		
        $x=$y;
		
	}

}

//pente positive
$indiceTabnormaljours13 = 0;
 
for ($x = 0 ; $x < count($resultnormaljours13) ; $x ++)
{
    if ($resultnormaljours13[$x] == 1)
	{
        $y = $x;
		
        while($resultnormaljours13[$y] != 0)
		{
            $nbrposnormaljours1[$indiceTabnormaljours13]++;
            $y++;

		}
        $indiceTabnormaljours13 ++;
		
        $x=$y;
		
	}

}

// echo '<pre>';
// print_r($nbr0normaljours1);
// echo '</pre>';
$max0=max($nbr0normaljours1);
echo "Max jours d'affil&eacute;s avec pente 0 :";
echo "</br>";
echo $max0;
echo "</br>";
$moyenne0=array_sum($nbr0normaljours1)/count($nbr0normaljours1);
echo "Moyenne jours d'affil&eacute;s avec pente 0:";
echo "</br>";
echo $moyenne0;
echo "</br>";

// echo '<pre>';
// print_r($nbrnegnormaljours1);
// echo '</pre>';
$maxneg=max($nbrnegnormaljours1);
echo "Max jours d'affil&eacute;s avec pente < 0 :";
echo "</br>";
echo $maxneg;
echo "</br>";
$moyenneneg=array_sum($nbrnegnormaljours1)/count($nbrnegnormaljours1);
echo "Moyenne jours d'affil&eacute;s avec pente <0:";
echo "</br>";
echo $moyenneneg;
echo "</br>";

// echo '<pre>';
// print_r($nbrposnormaljours1);
// echo '</pre>';
$maxpos=max($nbrposnormaljours1);
echo "Max jours d'affil&eacute;s avec pente > 0 :";
echo "</br>";
echo $maxpos;
echo "</br>";
$moyennepos=array_sum($nbrposnormaljours1)/count($nbrposnormaljours1);
echo "Moyenne jours d'affil&eacute;s avec pente > 0:";
echo "</br>";
echo $moyennepos;
echo "</br>";

//-----------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//---------SPLIT LE TABLEAU DE 0 ET 1 EN SOUS TABLEAUX CONTENANT UNIQUEMENT LES 1 CONSECUTIFS

$indexTableau= 0;
$newTab=array();
for ($x = 0 ; $x < count($resultnormaljours1) ; $x ++)
{
    if ($resultnormaljours1[$x] == 1)
	{
        $y = $x;
		
        while($resultnormaljours1[$y] != 0)
		{
            $newTab[$indexTableau][$y]=$resultnormaljours1[$y];
            $y++;

		}
        $indexTableau ++;
		
        $x=$y;
		
	}

}

$finTableau=count($newTab)-1;
// echo $finTableau;
$finalJoursCons=array();
for($i=0;$i<count($resultnormaljours1);$i++)
{
$finalJoursCons[0][$i]=$newTab[$finTableau][$i];
$finalJoursCons[0][$i]=(int)$finalJoursCons[0][$i];
$sommeJours=$finalJoursCons[0][$i]+$sommeJours;

}

$_SESSION['penteNulleNormal']=$sommeJours+1;
// echo "<pre>";
// print_r($newTab);
// echo "</pre>";

// echo "<pre>";
// print_r($finalJoursCons);
// echo "</pre>";
echo "<br/>";
echo "Stabilit&eacute; actuelle: ";
echo $sommeJours+1;
echo " jours";
?>
</body>
</html>