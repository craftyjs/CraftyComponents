<?php
namespace FWM\ServicesBundle\Extension;

use FWM\ServicesBundle\Services\DateService;

class ServicesExtension extends \Twig_Extension {

    public function getFilters() {
        return array(
            'print_r'  => new \Twig_Filter_Method($this, 'print_r'),
        	'var_dump'  => new \Twig_Filter_Method($this, 'var_dump'),
        	'explode'  => new \Twig_Filter_Method($this, 'explode'),
	        'mb_substr'  => new \Twig_Filter_Method($this, 'mb_substr'),
        	'dateInPolish'	=> new \Twig_Filter_Method($this, 'dateInPolish'),
        	'count'	=> new \Twig_Filter_Method($this, 'count'),
        	'strlen' => new \Twig_Filter_Method($this, 'strlen'),
            'json_decode' => new \Twig_Filter_Method($this, 'json_decode'),
            'base64_decode' => new \Twig_Filter_Method($this, 'base64_decode'),
            'implode' => new \Twig_Filter_Method($this, 'implode'),
            'slugify' => new \Twig_Filter_Method($this, 'slugify')
        );
    }

    public function print_r($text) {
        return print_r($text, true);
    }
    
    public function var_dump($text) {
        return var_dump($text, true);
    }
    
    public function explode($text,$spliter){
    	return explode($spliter,$text);
    }

	public function mb_substr($text,$start,$length){
    	return mb_substr($text,$start,$length,'UTF-8');
    }
    
    public function dateInPolish($date,$format)
    {
    	$dateInTimestamp = strtotime($date);
 		return DateService::dateInPolish($format, $dateInTimestamp);
    }
    
    public function count($text) {
        return count($text);
    }
    
    public function strlen($text) {
        return strlen($text);
    }

    public function json_decode($text) {
        return json_decode($text);
    }

    public function base64_decode($text) {
        return base64_decode($text);
    }

    public function implode($text,$spliter){
        return implode($spliter,$text);
    }

    public function slugify($text)
    {
        // replace non letter or digits by - 
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text); 

        // trim 
        $text = trim($text, '-'); 

        // transliterate 
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text); 

        // lowercase 
        $text = strtolower($text); 

        // remove unwanted characters 
        $text = preg_replace('~[^-\w]+~', '', $text); 
        if (empty($text)) 
        { 
            return 'n-a'; 
        } 

        return $text; 
    }

    public function getName()
    {
        return 'services';
    }
}