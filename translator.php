<?php
class Translator
{
   public static $RANDOM = 0;
   public static $PIRATE = 1;
   public static $PIGLATIN = 2;
   public static $GUNGAN = 3;
   public static $ERMAHGERD = 4;
   public static $YODA = 0;
   
   public static function translate($style, $text)
   {
      $translatedText = $text;
      
      $url = "http://api.funtranslations.com/translate/" . $FUN_API_URL[$style] . ".json?text="+ $text;
      
      $r = new HttpRequest('$url', HttpRequest::METH_GET);
 
      try
      {
         $jsonResponse = $r->send()->getBody();
         
         $json = json_decode($jsonResponse, true);
      } 
      catch (HttpException $ex)
      {
         echo $ex;
      }
      
      switch ($style)
      {
         case ERMAGERD:
         {
            break;
         }
      }
      
      return ($translatedText);
   }
   
   private static $FUN_API_URL =  array("", "pirate", "piglatin", "gungan", "ermahgerd", "yoda");
}