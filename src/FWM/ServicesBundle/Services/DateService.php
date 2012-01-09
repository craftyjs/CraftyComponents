<?php
namespace FWM\ServicesBundle\Services;

class DateService
{
	public static function calculateDistance($date1, $date2)
	{
		$date = array();
		$diff = abs(strtotime($date2) - strtotime($date1));
		$date['years']   = floor($diff / (365*60*60*24));
		$date['months']  = floor(($diff - $date['years'] * 365*60*60*24) / (30*60*60*24));
		$date['days']    = floor(($diff - $date['years'] * 365*60*60*24 - $date['months']*30*60*60*24)/ (60*60*24));
		$date['hours']   = floor(($diff - $date['years'] * 365*60*60*24 - $date['months']*30*60*60*24 - $date['days']*60*60*24)/ (60*60));
		$date['minuts']  = floor(($diff - $date['years'] * 365*60*60*24 - $date['months']*30*60*60*24 - $date['days']*60*60*24 - $date['hours']*60*60)/ 60);
		$date['seconds'] = floor(($diff - $date['years'] * 365*60*60*24 - $date['months']*30*60*60*24 - $date['days']*60*60*24 - $date['hours']*60*60 - $date['minuts']*60));
		return $date;
	}
	
	public static function dateInPolish($format,$timestamp=null){
		$to_convert = array(
			'l'=>array('dat'=>'N','str'=>array('Poniedziałek','Wtorek','Środa','Czwartek','Piątek','Sobota','Niedziela')),
			'F'=>array('dat'=>'n','str'=>array('styczeń','luty','marzec','kwiecień','maj','czerwiec','lipiec','sierpień','wrzesień','październik','listopad','grudzień')),
			'f'=>array('dat'=>'n','str'=>array('stycznia','lutego','marca','kwietnia','maja','czerwca','lipca','sierpnia','września','października','listopada','grudnia'))
		);
		if ($pieces = preg_split('#[:/.\-, ]#', $format)){
			if ($timestamp === null) { $timestamp = time(); }
			foreach ($pieces as $datepart){
				if (array_key_exists($datepart,$to_convert)){
					$replace[] = $to_convert[$datepart]['str'][(date($to_convert[$datepart]['dat'],$timestamp)-1)];
				}else{
					$replace[] = date($datepart,$timestamp);
				}
			}
			$result = strtr($format,array_combine($pieces,$replace));
			return $result;
		}
	}
}
?>