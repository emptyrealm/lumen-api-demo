<?php
namespace ICAnalysis\ICMfr;

use ICAnalysis\Common;
use ICAnalysis\ICSynonym\ICSynonym;

class ICMfr
{

    private $dictDelimiter = '|';
    private $dictMfr=array();

    function __construct()
    {
        $this->loadDictMfr();
    }

    private function loadDictMfr(){
        if(!$this->dictMfr){
            $dictList = Common::config('mfr_dict');
            $this->dictMfr = Common::arrayGet($dictList, 'mfrNameCn') . $this->dictDelimiter . Common::arrayGet($dictList, 'mfrNameEn');
        }
    }

    
    public function isMfr($keyword)
    {
        return !empty(Common::dictExist($this->dictMfr, $keyword) !== false) ? true : false;
    }


    public function getMfrIdList($group){
        return Common::arrayGet(Common::config('mfr_ids'),$group,array());
    }


    public function getMfrID($group,$key){
        if($group){
            return Common::arrayGet(self::getMfrIdList($group),strtolower($key),null);
        }
    }


    public function getMfrIDByMfr($group='findic',$mfr){
        $ICSynonym=new ICSynonym();
        $sysnonyms=$ICSynonym->getSynonym($mfr);
        $sysnonyms[]=$mfr;
        $id=null;
        if($sysnonyms){
            foreach($sysnonyms as $v){
                $tId=$this->getMfrID($group,$v);
                if($tId){
                    $id=$tId;
                    break;
                }
            }
        }
        return $id;
    }
    



    



    

}
