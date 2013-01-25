<?php
/**
 * Jevix � �������� ��������������� ���������� ������ ������ �������,
 * ��������� ������������ ������������� �������� HTML/XML ����������,
 * �������������� �������� ���������� ����� � ����������,
 * ������������� ��������� XSS-����� � ���� ����������.
 * http://code.google.com/p/jevix/
 * @package Pilot
 * @subpackage CMS
 * @author ur001 <ur001ur001@gmail.com>, http://ur001.habrahabr.ru
 * @version 1.00
 *
 * ������� ������:
 * 1.00
 *  + ��������� ��� � �������������� ������ ���������� � �������� ��������� ���� �������� ������
 * 1.00 RC2
 *  + ��������� ������ ����
 * 1.00 RC1
 *  + �������� ���������� ����� Jevix::RUS ��� ���������� ������� ��������
 *  + ��������������� �������� ����� ���������� ������ ��� ��������
 *  + ��������� ��������� cfgSetTagNoTypography() ����������� ���������������� � ��������� ����
 *  + ������� ��������� �������� ��������� �������. �� ���� ����� �������
 *  + ���� ����� 33" ������ �� ������������ � ������������� �������. ������ �������� "��� 24" �������" - ������ �� ���������.
 * 0.99
 *  + ��������� ���������������� ��� �������� ��������� ����:
 *    ����� ������� ��� �������� ( 'colspan'=>'#int', 'value' => '#text' )
 *    � Jevix, ��-���������, �������� ������ ����� ��� ���������� ����������� ��������� (src, href, width, height)
 * 0.98
 *  + ��������� ���������������� ��� �������� ��������� ����:
 *    ����� �������� ������ ��������� �������� �������� (  'align'=>array('left', 'right', 'center') )
 * 0.97
 *  + ������� "�������" ����������� ��� &quote; ���� ��� ���� ��� ��������
 * 0.96
 *  + ��������� ����������� ��������� https � ftp ��� ������ (a href="https://...)
 * 0.95
 *  + ���������� ���������������� ?.. � !.. (��� ����� � ����� ������ �� ������������ � ���������)
 *  + ��������� �������������� ���������� ������� ����� ����� ��� �������� ��-�� ���� ���������� ���� ��������
 *    index.php ��� .htaccess
 * 0.94
 *  + ��������� ��������� �������������� ���������� �����. �������� rel = "nofolow" ��� ������.
 *    ������� Myroslav Holyak (vbhjckfd@gmail.com)
 * 0.93
 *      + ��������� ��� � ��������� �������� (�������� � "123 &mdash; 123")
 *  + ���������� ������ ��-�� ������� ������ �� ����������� �������������� �������������� URL � �����
 *  + ��������� ��������� cfgSetAutoLinkMode ��� ���������� ��������������� �������������� URL � ������
 *  + �������������� ������� ����� �����, ���� ����� �� ��� ������� ������
 * 0.92
 *      + ��������� ��������� cfgSetAutoBrMode. ��� ��������� � false, �������� ����� �� ����� ������������� ���������� �� BR
 *      + �������� ��������� HTML-���������. ������ ��� �������� ������� ���������� � Unicode (�� ����������� <>)
 *    ������������� ������������� � ������
 * 0.91
 *      + ��������� ��������� ������������������ ����� <pre>, <code>. ��� ������� ����������� cfgSetTagPreformatted()
 *  + ��������� ��������� cfgSetXHTMLMode. ��� ���������� ������ ���� ����� ����������� ��� <br>, ��� ���������� - <br/>
 *      + ��������� �������������� ���������
 * 0.9
 *      + ������ ����-�����
 */

class Jevix{
        const PRINATABLE  = 0x1;
        const ALPHA       = 0x2;
        const LAT         = 0x4;        
        const RUS         = 0x8;                
        const NUMERIC     = 0x10;      
        const SPACE       = 0x20;
        const NAME        = 0x40;
        const URL         = 0x100;
        const NOPRINT     = 0x200;
        const PUNCTUATUON = 0x400;
        //const           = 0x800;
        //const           = 0x1000;
        const HTML_QUOTE  = 0x2000;
        const TAG_QUOTE   = 0x4000;
        const QUOTE_CLOSE = 0x8000;    
        const NL          = 0x10000;
        const QUOTE_OPEN  = 0;
       
        const STATE_TEXT = 0;
        const STATE_TAG_PARAMS = 1;    
        const STATE_TAG_PARAM_VALUE = 2;
        const STATE_INSIDE_TAG = 3;
        const STATE_INSIDE_NOTEXT_TAG = 4;
        const STATE_INSIDE_PREFORMATTED_TAG = 5;
       
        public $tagsRules = array();
        public $entities0 = array('"'=>'&quot;', "'"=>'&#39;', '&'=>'&amp;', '<'=>'&lt;', '>'=>'&gt;');
        public $entities1 = array();    
        public $entities2 = array('<'=>'&lt;', '>'=>'&gt;', '"'=>'&quot;');    
        public $textQuotes = array(array('�', '�'), array('�', '�'));
        public $dash = " � ";
        public $apostrof = "�";
        public $dotes = "�";
        public $nl = "\r\n";
        public $defaultTagParamRules = array('href' => '#link', 'src' => '#image', 'width' => '#int', 'height' => '#int', 'text' => '#text', 'title' => '#text');
       
        protected $text;
        protected $textBuf;
        protected $textLen = 0;
        protected $curPos;
        protected $curCh;
        protected $curChOrd;
        protected $curChClass;
        protected $states;
        protected $quotesOpened = 0;
        protected $brAdded = 0;
        protected $state;
        protected $tagsStack;
        protected $openedTag;
        protected $autoReplace; // ����������
        protected $isXHTMLMode  = true; // <br/>, <img/>
        protected $isAutoBrMode = true; // \n = <br/>
        protected $isAutoLinkMode = true;
        protected $br = "<br/>";
       
        protected $noTypoMode = false;
       
        public    $outBuffer = '';
        public    $errors;

       
        /**
         * ��������� ��� ������������ �����
         *
         */
        const TR_TAG_ALLOWED = 1;                // ��� ��������
        const TR_PARAM_ALLOWED = 2;      // �������� ���� �������� (a->title, a->src, i->alt)
        const TR_PARAM_REQUIRED = 3;     // �������� ���� ������ ����������� (a->href, img->src)
        const TR_TAG_SHORT = 4;                  // ��� ����� ���� �������� (img, br)
        const TR_TAG_CUT = 5;                    // ��� ���������� �������� ������ � ��������� (script, iframe)
        const TR_TAG_CHILD = 6;                  // ��� ����� ��������� ������ ����
        const TR_TAG_CONTAINER = 7;      // ��� ����� ��������� ���� ��������� ����. � �� �� ����� ���� ������
        const TR_TAG_CHILD_TAGS = 8;     // ���� ������� ����� ��������� ������ ���� ������ ���
        const TR_TAG_PARENT = 9;                 // ��� � ������� ������ ����������� ������ ���
        const TR_TAG_PREFORMATTED = 10;  // ������������������ ���, � ������� �� ���������� �� HTML �������� ���� <pre> �������� ��� ������� � �������
        const TR_PARAM_AUTO_ADD = 11;    // Auto add parameters + default values (a->rel[=nofollow])
        const TR_TAG_NO_TYPOGRAPHY = 12; // ���������� ���������������� ��� ����
       
        /**
         * ������ �������� ������������ symclass.php
         *
         * @var array
         */
        protected $chClasses = array(0=>512,1=>512,2=>512,3=>512,4=>512,5=>512,6=>512,7=>512,8=>512,9=>32,10=>66048,11=>512,12=>512,13=>66048,14=>512,15=>512,16=>512,17=>512,18=>512,19=>512,20=>512,21=>512,22=>512,23=>512,24=>512,25=>512,26=>512,27=>512,28=>512,29=>512,30=>512,31=>512,32=>32,97=>71,98=>71,99=>71,100=>71,101=>71,102=>71,103=>71,104=>71,105=>71,106=>71,107=>71,108=>71,109=>71,110=>71,111=>71,112=>71,113=>71,114=>71,115=>71,116=>71,117=>71,118=>71,119=>71,120=>71,121=>71,122=>71,65=>71,66=>71,67=>71,68=>71,69=>71,70=>71,71=>71,72=>71,73=>71,74=>71,75=>71,76=>71,77=>71,78=>71,79=>71,80=>71,81=>71,82=>71,83=>71,84=>71,85=>71,86=>71,87=>71,88=>71,89=>71,90=>71,1072=>11,1073=>11,1074=>11,1075=>11,1076=>11,1077=>11,1078=>11,1079=>11,1080=>11,1081=>11,1082=>11,1083=>11,1084=>11,1085=>11,1086=>11,1087=>11,1088=>11,1089=>11,1090=>11,1091=>11,1092=>11,1093=>11,1094=>11,1095=>11,1096=>11,1097=>11,1098=>11,1099=>11,1100=>11,1101=>11,1102=>11,1103=>11,1040=>11,1041=>11,1042=>11,1043=>11,1044=>11,1045=>11,1046=>11,1047=>11,1048=>11,1049=>11,1050=>11,1051=>11,1052=>11,1053=>11,1054=>11,1055=>11,1056=>11,1057=>11,1058=>11,1059=>11,1060=>11,1061=>11,1062=>11,1063=>11,1064=>11,1065=>11,1066=>11,1067=>11,1068=>11,1069=>11,1070=>11,1071=>11,48=>337,49=>337,50=>337,51=>337,52=>337,53=>337,54=>337,55=>337,56=>337,57=>337,34=>57345,39=>16385,46=>1281,44=>1025,33=>1025,63=>1281,58=>1025,59=>1281,1105=>11,1025=>11,47=>257,38=>257,37=>257,45=>257,95=>257,61=>257,43=>257,35=>257,124=>257,);
                       
        /**
         * ��������� ����������������� ����� ��� ������ ��� ���������� �����
         *
         * @param array|string $tags ���(�)
         * @param int $flag ����
         * @param mixed $value ��������=� �����
         * @param boolean $createIfNoExists ���� ��� ��� �� �������� - ������ ���
         */
        protected function _cfgSetTagsFlag($tags, $flag, $value, $createIfNoExists = true){
                if(!is_array($tags)) $tags = array($tags);
                foreach($tags as $tag){
                        if(!isset($this->tagsRules[$tag])) {
                                if($createIfNoExists){
                                        $this->tagsRules[$tag] = array();
                                } else {
                                        throw new Exception("��� $tag ����������� � ������ ����������� �����");
                                }
                        }
                        $this->tagsRules[$tag][$flag] = $value;
                }              
        }
       
        /**
         * ������������: ���������� ��� ������ �����
         * ��� �� ����������� ���� ��������� ������������
         * @param array|string $tags ���(�)
         */
        function cfgAllowTags($tags){
                $this->_cfgSetTagsFlag($tags, self::TR_TAG_ALLOWED, true);
        }
       
        /**
         * ������������: ������� ���� ���� <img>
         * @param array|string $tags ���(�)
         */
        function cfgSetTagShort($tags){
                $this->_cfgSetTagsFlag($tags, self::TR_TAG_SHORT, true, false);
        }
       
        /**
         * ������������: ������������������ ����, � ������� �� ���������� �� HTML �������� ���� <pre>
         * @param array|string $tags ���(�)
         */
        function cfgSetTagPreformatted($tags){
                $this->_cfgSetTagsFlag($tags, self::TR_TAG_PREFORMATTED, true, false);
        }      
       
        /**
         * ������������: ���� � ������� ��������� ���������������� ���� <code>
         * @param array|string $tags ���(�)
         */
        function cfgSetTagNoTypography($tags){
                $this->_cfgSetTagsFlag($tags, self::TR_TAG_NO_TYPOGRAPHY, true, false);
        }              
       
        /**
         * ������������: ��� ���������� �������� ������ � ��������� (script, iframe)
         * @param array|string $tags ���(�)
         */
        function cfgSetTagCutWithContent($tags){
                $this->_cfgSetTagsFlag($tags, self::TR_TAG_CUT, true);
        }      
       
        /**
         * ������������: ���������� ����������� ���������� ����
         * @param string $tag ���
         * @param string|array $params ����������� ���������
         */
        function cfgAllowTagParams($tag, $params){
                if(!isset($this->tagsRules[$tag])) throw new Exception("��� $tag ����������� � ������ ����������� �����");
                if(!is_array($params)) $params = array($params);
                // ���� ����� �� ������� ����������� ���������� �� ���������� - ������ ��
                if(!isset($this->tagsRules[$tag][self::TR_PARAM_ALLOWED])) {
                        $this->tagsRules[$tag][self::TR_PARAM_ALLOWED] = array();
                }
                foreach($params as $key => $value){
                        if(is_string($key)){
                                $this->tagsRules[$tag][self::TR_PARAM_ALLOWED][$key] = $value;
                        } else {
                                $this->tagsRules[$tag][self::TR_PARAM_ALLOWED][$value] = true;
                        }                      
                }
        }      
       
        /**
         * ������������: ���������� ����������� ���������� ����
         * @param string $tag ���
         * @param string|array $params ����������� ���������
         */
        function cfgSetTagParamsRequired($tag, $params){
                if(!isset($this->tagsRules[$tag])) throw new Exception("��� $tag ����������� � ������ ����������� �����");
                if(!is_array($params)) $params = array($params);
                // ���� ����� �� ������� ����������� ���������� �� ���������� - ������ ��
                if(!isset($this->tagsRules[$tag][self::TR_PARAM_REQUIRED])) {
                        $this->tagsRules[$tag][self::TR_PARAM_REQUIRED] = array();
                }              
                foreach($params as $param){
                        $this->tagsRules[$tag][self::TR_PARAM_REQUIRED][$param] = true;
                }      
        }      


        /* ������������: ��������� ����� ������� ����� ��������� ���-���������
         * @param string $tag ���
         * @param string|array $childs ����������� ����
         * @param boolean $isContainerOnly ��� �������� ������ ����������� ������ ����� � �� ����� ��������� �����
         * @param boolean $isChildOnly ��������� ���� �� ����� �������������� ����� ����� ���������� ����
         */
        function cfgSetTagChilds($tag, $childs, $isContainerOnly = false, $isChildOnly = false){
                if(!isset($this->tagsRules[$tag])) throw new Exception("��� $tag ����������� � ������ ����������� �����");
                if(!is_array($childs)) $childs = array($childs);
                // ��� �������� ����������� � �� ����� ��������� �����
                if($isContainerOnly) $this->tagsRules[$tag][self::TR_TAG_CONTAINER] = true;
                // ���� ����� �� ������� ����������� ����� �� ���������� - ������ ��
                if(!isset($this->tagsRules[$tag][self::TR_TAG_CHILD_TAGS])) {
                        $this->tagsRules[$tag][self::TR_TAG_CHILD_TAGS] = array();
                }              
                foreach($childs as $child){
                        $this->tagsRules[$tag][self::TR_TAG_CHILD_TAGS][$child] = true;
                        //  ��������� ��� ������ ������������� � ������ �����
                        if(!isset($this->tagsRules[$child])) throw new Exception("��� $child ����������� � ������ ����������� �����");
                        if(!isset($this->tagsRules[$child][self::TR_TAG_PARENT])) $this->tagsRules[$child][self::TR_TAG_PARENT] = array();
                        $this->tagsRules[$child][self::TR_TAG_PARENT][$tag] = true;
                        // ��������� ����������� ���� ����� ��������� ������ ������� ����-����������                    
                        if($isChildOnly) $this->tagsRules[$child][self::TR_TAG_CHILD] = true;
                }
        }
       
    /**
     * CONFIGURATION: Adding autoadd attributes and their values to tag
     * @param string $tag tag
     * @param string|array $params array of pairs attributeName => attributeValue
     */
    function cfgSetTagParamsAutoAdd($tag, $params){
        if(!isset($this->tagsRules[$tag])) throw new Exception("Tag $tag is missing in allowed tags list");
        if(!is_array($params)) $params = array($params);
        if(!isset($this->tagsRules[$tag][self::TR_PARAM_AUTO_ADD])) {
            $this->tagsRules[$tag][self::TR_PARAM_AUTO_ADD] = array();
        }
        foreach($params as $param => $value){
            $this->tagsRules[$tag][self::TR_PARAM_AUTO_ADD][$param] = $value;
        }
    }
       

        /**
         * ����������
         *
         * @param array $from �
         * @param array $to ��
         */
        function cfgSetAutoReplace($from, $to){
                $this->autoReplace = array('from' => $from, 'to' => $to);
        }
       
        /**
         * ��������� ��� ���������� ������ XTML
         *
         * @param boolean $isXHTMLMode
         */
        function cfgSetXHTMLMode($isXHTMLMode){
                $this->br = $isXHTMLMode ? '<br/>' : '<br>';
                $this->isXHTMLMode = $isXHTMLMode;
        }
       
        /**
         * ��������� ��� ���������� ������ ������ ����� ����� �� <br/>
         *
         * @param boolean $isAutoBrMode
         */
        function cfgSetAutoBrMode($isAutoBrMode){
                $this->isAutoBrMode = $isAutoBrMode;
        }      
       
        /**
         * ��������� ��� ���������� ������ ��������������� ����������� ������
         *
         * @param boolean $isAutoLinkMode
         */
        function cfgSetAutoLinkMode($isAutoLinkMode){
                $this->isAutoLinkMode = $isAutoLinkMode;
        }              
       
        protected function &strToArray($str){
                $chars = null;
                preg_match_all('/./su', $str, $chars);
                return $chars[0];
        }
               
       
        function parse($text, &$errors){
                $this->curPos = -1;
                $this->curCh = null;
                $this->curChOrd = 0;
                $this->state = self::STATE_TEXT;
                $this->states = array();
                $this->quotesOpened = 0;
                $this->noTypoMode = false;
               
                // ���� ���������� BR?
                if($this->isAutoBrMode) {
                        $this->text = preg_replace('/<br\/?>(\r\n|\n\r|\n)?/ui', $this->nl, $text);
                } else {
                        $this->text = $text;
                }
               
               
                if(!empty($this->autoReplace)){
                        $this->text = str_replace($this->autoReplace['from'], $this->autoReplace['to'], $this->text);
                }
                $this->textBuf = $this->strToArray($this->text);
                $this->textLen = count($this->textBuf);
                $this->getCh();
                $content = '';
                $this->outBuffer='';
                $this->brAdded=0;
                $this->tagsStack = array();    
                $this->openedTag = null;
                $this->errors = array();
                $this->skipSpaces();
                $this->anyThing($content);
                $errors = $this->errors;
                return $content;
        }
       
        /**
         * ��������� ���������� ������� �� ������� ������
         * @return string ��������� ������
         */
        protected function getCh(){
                return $this->goToPosition($this->curPos+1);
        }
       
        /**
         * ����������� �� ��������� ������� �� ������� ������ � ���������� �������
         * @return string ������ � ��������� �������
         */    
        protected function goToPosition($position){
                $this->curPos = $position;
                if($this->curPos < $this->textLen){
                        $this->curCh = $this->textBuf[$this->curPos];
                        $this->curChOrd = uniord($this->curCh);
                        $this->curChClass = $this->getCharClass($this->curChOrd);
                } else {
                        $this->curCh = null;
                        $this->curChOrd = 0;
                        $this->curChClass = 0;
                }
                return $this->curCh;            
        }
       
        /**
         * ��������� ������� ���������
         *
         */
        protected function saveState(){
                $state = array(
                        'pos'   => $this->curPos,
                        'ch'    => $this->curCh,
                        'ord'   => $this->curChOrd,
                        'class' => $this->curChClass,
                );
               
                $this->states[] = $state;
                return count($this->states)-1;
        }
       
        /**
         * ������������
         *
         */    
        protected function restoreState($index = null){
                if(!count($this->states)) throw new Exception('����� �����');
                if($index == null){
                        $state = array_pop($this->states);
                } else {
                        if(!isset($this->states[$index])) throw new Exception('�������� ������ �����');
                        $state = $this->states[$index];
                        $this->states = array_slice($this->states, 0, $index);
                }
               
                $this->curPos     = $state['pos'];
                $this->curCh      = $state['ch'];
                $this->curChOrd   = $state['ord'];
                $this->curChClass = $state['class'];    
        }
       
        /**
         * ��������� ������ ��������� ������� � ������� �������
         * ���� ������ ������������� ���������� ������� ���������� �� ���������
         *
         * @param string $ch
         * @return boolean
         */
        protected function matchCh($ch, $skipSpaces = false){
                if($this->curCh == $ch) {
                        $this->getCh();
                        if($skipSpaces) $this->skipSpaces();
                        return true;
                }
               
                return false;
        }
       
        /**
         * ��������� ������ ��������� ������� ���������� ������ � ������� �������
         * ���� ������ ������������� ���������� ������ ������� ���������� �� ���������
         *
         * @param int $chClass ����� �������
         * @return string �������� ������ ��� false
         */
        protected function matchChClass($chClass, $skipSpaces = false){
                if(($this->curChClass & $chClass) == $chClass) {
                        $ch = $this->curCh;
                        $this->getCh();
                        if($skipSpaces) $this->skipSpaces();
                        return $ch;
                }
               
                return false;
        }      
       
        /**
         * �������� �� ������ ���������� ������ � ������� �������
         * ���� ������ ������������� ��������� ������� ���������� �� ��������� ����� ������ ������
         *
         * @param string $str
         * @return boolean
         */
        protected function matchStr($str, $skipSpaces = false){
                $this->saveState();
                $len = strlen($str);
                $test = '';
                while($len-- && $this->curChClass){
                        $test.=$this->curCh;
                        $this->getCh();
                }
               
                if($test == $str) {
                        if($skipSpaces) $this->skipSpaces();
                        return true;
                } else {
                        $this->restoreState();
                        return false;
                }
        }
       
        /**
         * ������� ������ �� ���������� ���������� �������
         *
         * @param string $ch �������
         * @return string �������� ������ ��� false
         */
        protected function skipUntilCh($ch){
                $chPos = strpos($this->text, $ch, $this->curPos);
                if($chPos){
                        return $this->goToPosition($chPos);
                } else {
                        return false;
                }
        }
       
        /**
         * ������� ������ �� ���������� ��������� ������ ��� �������
         *
         * @param string $str ������ ��� ������ �� ������
         * @return boolean
         */
        protected function skipUntilStr($str){
                $str = $this->strToArray($str);
                $firstCh = $str[0];
                $len = count($str);
                while($this->curChClass){
                        if($this->curCh == $firstCh){
                                $this->saveState();
                                $this->getCh();
                                $strOK = true;
                                for($i = 1; $i<$len ; $i++){
                                        // ����� ������
                                        if(!$this->curChClass){
                                                return false;
                                        }
                                        // ������� ������ �� ����� �������� ������� ����������� ������?
                                        if($this->curCh != $str[$i]){
                                                $strOK = false;
                                                break;
                                        }
                                        // ��������� ������
                                        $this->getCh();
                                }
                               
                                // ��� ������� ������������ � ��������� �� ��������� ������
                                if(!$strOK){
                                        $this->restoreState();
                                } else {
                                        return true;
                                }
                        }
                        // ��������� ������
                        $this->getCh();
                }              
                return false;
        }      
       
        /**
         * ���������� ����� �������
         *
         * @return int
         */
        protected function getCharClass($ord){
                return isset($this->chClasses[$ord]) ? $this->chClasses[$ord] : self::PRINATABLE;
        }
       
        /*function isSpace(){
                return $this->curChClass == slf::SPACE;
        }*/
       
        /**
         * ������� ��������
         *
         */
        protected function skipSpaces(&$count = 0){
                while($this->curChClass == self::SPACE) {
                        $this->getCh();
                        $count++;
                }
                return $count > 0;
        }
       
        /**
         *  �������� ��� (����, ���������) �� �������� 1 ������ ����� ����� ��� ������
         *
         * @param string $name
         */
        protected function name(&$name = '', $minus = false){
                if(($this->curChClass & self::LAT) == self::LAT){
                        $name.=$this->curCh;
                        $this->getCh();
                } else {
                        return false;
                }
               
                while((($this->curChClass & self::NAME) == self::NAME || ($minus && $this->curCh=='-'))){
                        $name.=$this->curCh;
                        $this->getCh();
                }              
               
                $this->skipSpaces();
                return true;
        }
       
        protected function tag(&$tag, &$params, &$content, &$short){
                $this->saveState();    
                $params = array();
                $tag = '';
                $closeTag = '';
                $params = array();
                $short = false;
                if(!$this->tagOpen($tag, $params, $short)) return false;
                // �������� ������ ����
                if($short) return true;
               
                // ��������� ������� � ���������
                //$oldQuotesopen = $this->quotesOpened;
                $oldState = $this->state;
                $oldNoTypoMode = $this->noTypoMode;
                //$this->quotesOpened = 0;
               
               
                // ���� � ���� �� ������ ���� ������, � ������ ������ ����
                // ��������� � ��������� self::STATE_INSIDE_NOTEXT_TAG
                if(!empty($this->tagsRules[$tag][self::TR_TAG_PREFORMATTED])){
                        $this->state = self::STATE_INSIDE_PREFORMATTED_TAG;
                } elseif(!empty($this->tagsRules[$tag][self::TR_TAG_CONTAINER])){
                        $this->state = self::STATE_INSIDE_NOTEXT_TAG;
                } elseif(!empty($this->tagsRules[$tag][self::TR_TAG_NO_TYPOGRAPHY])) {
                        $this->noTypoMode = true;
                        $this->state = self::STATE_INSIDE_TAG;
                } else {
                        $this->state = self::STATE_INSIDE_TAG;
                }
               
                // ������� ����
                array_push($this->tagsStack, $tag);
                $this->openedTag = $tag;
                $content = '';
                if($this->state == self::STATE_INSIDE_PREFORMATTED_TAG){
                        $this->preformatted($content, $tag);
                } else {
                        $this->anyThing($content, $tag);
                }
               
                array_pop($this->tagsStack);
                $this->openedTag = !empty($this->tagsStack) ? array_pop($this->tagsStack) : null;
               
                $isTagClose = $this->tagClose($closeTag);
                if($isTagClose && ($tag != $closeTag)) {
                        $this->eror("�������� ������������� ��� $closeTag. ��������� �������� $tag");
                        //$this->restoreState();
                }
               

                // ��������������� ���������� ��������� � ������� �������
                $this->state = $oldState;
                $this->noTypoMode = $oldNoTypoMode;
                //$this->quotesOpened = $oldQuotesopen;
               
                return true;
        }
       
        protected function preformatted(&$content = '', $insideTag = null){
                while($this->curChClass){
                        if($this->curCh == '<'){
                                $tag = '';
                                $this->saveState();
                                // �������� ����� ������������� ���
                                $isClosedTag = $this->tagClose($tag);
                                // ������������ �����, ���� ��� ��� ������
                                if($isClosedTag) $this->restoreState();
                                // ���� ��������� ��, ��� ��������� - ����������� � ���������� true
                                if($isClosedTag && $tag == $insideTag) return;
                        }
                        $content.= isset($this->entities2[$this->curCh]) ? $this->entities2[$this->curCh] : $this->curCh;
                        $this->getCh();
                }
        }
       
        protected function tagOpen(&$name, &$params, &$short = false){
                $restore = $this->saveState();  
               
                // ��������
                if(!$this->matchCh('<')) return false;
                $this->skipSpaces();
                if(!$this->name($name)){
                        $this->restoreState();
                        return false;
                }
               
                // ������� �������� ������ ��������� ����
                if($this->curCh != '>' && $this->curCh != '/') $this->tagParams($params);
               
                // �������� ������ ����
                $short = !empty($this->tagsRules[$name][self::TR_TAG_SHORT]);

                // Short && XHTML && !Slash || Short && !XHTML && !Slash = ERROR
                $slash = $this->matchCh('/');
                //if(($short && $this->isXHTMLMode && !$slash) || (!$short && !$this->isXHTMLMode && $slash)){
                if(!$short && $slash){
                        $this->restoreState();
                        return false;
                }

                $this->skipSpaces();

                // ��������    
                if(!$this->matchCh('>')) {
                        $this->restoreState($restore);
                        return false;
                }
               
                $this->skipSpaces();
                return true;
        }


        protected function tagParams(&$params = array()){
                $name = null;
                $value = null;
                while($this->tagParam($name, $value)){
                        $params[$name] = $value;
                        $name = ''; $value = '';
                }
                return count($params) > 0;
        }      
               
        protected function tagParam(&$name, &$value){
                $this->saveState();
                if(!$this->name($name, true)) return false;
               
                if(!$this->matchCh('=', true)){
                        // �������� ����� - �������� ��� �������� <input type="checkbox" checked>, <td nowrap class=b>
                        if(($this->curCh=='>' || ($this->curChClass & self::LAT) == self::LAT)){
                                $value = null;
                                return true;
                        } else {
                                $this->restoreState();
                                return false;
                        }
                }              
               
                $quote = $this->matchChClass(self::TAG_QUOTE, true);
               
                if(!$this->tagParamValue($value, $quote)){
                        $this->restoreState();
                        return false;
                }      
               
                if($quote && !$this->matchCh($quote, true)){
                        $this->restoreState();
                        return false;
                }      
               
                $this->skipSpaces();
                return true;    
        }      
       
        protected function tagParamValue(&$value, $quote){
                if($quote !== false){
                        // ���������� �������� � ���������� �������� ���� �� ������� � �� �����
                        $escape = false;
                        while($this->curChClass && ($this->curCh != $quote || $escape)){
                                $escape = false;
                                // ���������� ������� HTML ������� �� ����� ���� � ����������
                                $value.=isset($this->entities1[$this->curCh]) ? $this->entities1[$this->curCh] : $this->curCh;
                                // ������ ������� <a href="javascript::alert(\"hello\")">
                                if($this->curCh == '\\') $escape = true;
                                $this->getCh();                
                        }
                } else {
                        // �������� �������� ��� �������. �������� ��� ���� �� ������ � �� > � �� �����
                        while($this->curChClass && !($this->curChClass & self::SPACE) && $this->curCh != '>'){
                                // ���������� ������� HTML ������� �� ����� ���� � ����������
                                $value.=isset($this->entities1[$this->curCh]) ? $this->entities1[$this->curCh] : $this->curCh;
                                $this->getCh();                
                        }                      
                }

                return true;
        }
       
        protected function tagClose(&$name){
                $this->saveState();    
                if(!$this->matchCh('<')) return false;
                $this->skipSpaces();
                if(!$this->matchCh('/')) {
                        $this->restoreState();
                        return false;
                }
                $this->skipSpaces();
                if(!$this->name($name)){
                        $this->restoreState();
                        return false;
                }
                $this->skipSpaces();
                if(!$this->matchCh('>')) {
                        $this->restoreState();
                        return false;
                }
                return true;            
        }      
       
        protected function makeTag($tag, $params, $content, $short, $parentTag = null){
                $tag = strtolower($tag);
               
                // �������� ������� ���������� ����
                $tagRules = isset($this->tagsRules[$tag]) ? $this->tagsRules[$tag] : null;
                       
                // �������� - ������������ ��� - ���������, ���������� ������ ������ ���� (ul, table, etc)
                $parentTagIsContainer = $parentTag && isset($this->tagsRules[$parentTag][self::TR_TAG_CONTAINER]);
               
                // �������� ��� ������ � �����������
                if($tagRules && isset($this->tagsRules[$tag][self::TR_TAG_CUT])) return '';
                               
                // �������� �� ���
                if(!$tagRules || empty($tagRules[self::TR_TAG_ALLOWED])) return $parentTagIsContainer ? '' : $content;

                // ���� ��� ��������� ������ ������� - ����� �� �� ��� ���������?
                if($parentTagIsContainer){
                        if(!isset($this->tagsRules[$parentTag][self::TR_TAG_CHILD_TAGS][$tag])) return '';
                }      

                // ��� ����� ��������� ������ ������ ������� ����
                if(isset($tagRules[self::TR_TAG_CHILD])){
                        if(!isset($tagRules[self::TR_TAG_PARENT][$parentTag])) return $content;
                }
               
               
                $resParams = array();
                foreach($params as $param=>$value){
                        $param = strtolower($param);
                        $value = trim($value);
                        if(empty($value)) continue;
                       
                        // ������� ���� ��������? ����� �������� ��������? �������� ������ ������
                        $paramAllowedValues = isset($tagRules[self::TR_PARAM_ALLOWED][$param]) ? $tagRules[self::TR_PARAM_ALLOWED][$param] : false;
                        if(empty($paramAllowedValues)) continue;
                       
                        // ���� ���� ������ ����������� ���������� ����
                        if(is_array($paramAllowedValues) && !in_array($value, $paramAllowedValues)) {
                                $this->eror("������������ �������� ��� �������� ���� $tag $param=$value");
                                continue;
                        // ���� ������� ���� ������� ��� �����������, �� ������� �� ������� - ������� � ������ ����������� ������ ��� ���������
                        } elseif($paramAllowedValues === true && !empty($this->defaultTagParamRules[$param])){
                                $paramAllowedValues = $this->defaultTagParamRules[$param];
                        }
                       
                        if(is_string($paramAllowedValues)){
                                switch($paramAllowedValues){
                                        case '#int':
                                                if(!is_numeric($value)) {
                                                        $this->eror("������������ �������� ��� �������� ���� $tag $param=$value. ��������� �����");
                                                        continue(2);
                                                }      
                                                break;
                                               
                                        case '#text':
                                                $value = htmlspecialchars($value);
                                                break;                                          
                                               
                                        case '#link':
                                                // ���-������ � ������
                                                if(preg_match('/javascript:/ui', $value)) {
                                                        $this->eror('������� �������� JavaScript � URI');
                                                        continue(2);
                                                }
                                                // ������ ������ ������ ���� a-z0-9!
                                                if(!preg_match('/^[a-z0-9\/]/ui', $value)) {
                                                        $this->eror('URI: ������ ������ ������ ������ ���� ������ ��� ������');
                                                        continue(2);
                                                }
                                                // HTTP � ������ ���� ���
                                                if(!preg_match('/^(http|https|ftp):\/\//ui', $value) && !preg_match('/^\//ui', $value)) $value = 'http://'.$value;                                              
                                                break;
                                               
                                        case '#image':
                                                // ���-������ � ���� � ��������
                                                if(preg_match('/javascript:/ui', $value)) {
                                                        $this->eror('������� �������� JavaScript � ���� � �����������');
                                                        continue(2);
                                                }
                                                // HTTP � ������ ���� ���
                                                if(!preg_match('/^http:\/\//ui', $value) && !preg_match('/^\//ui', $value)) $value = 'http://'.$value;                                          
                                                break;
                                               
                                        default:
                                                $this->eror("�������� �������� �������� ���� � ��������� Jevix: $param => $paramAllowedValues");
                                                continue(2);
                                                break;                                  
                                }
                        }


                        $resParams[$param] = $value;
                }
               
                // �������� ������������ ���������� ����
                // ���� ��� ������������ ���������� ���������� ������ �������
                $requiredParams = isset($tagRules[self::TR_PARAM_REQUIRED]) ? array_keys($tagRules[self::TR_PARAM_REQUIRED]) : array();
                if($requiredParams){
                        foreach($requiredParams as $requiredParam){
                                if(empty($resParams[$requiredParam])) return $content;
                        }
                }
               
                // ��������������� ���������
                if(!empty($tagRules[self::TR_PARAM_AUTO_ADD])){
                foreach($tagRules[self::TR_PARAM_AUTO_ADD] as $name => $value) {
                    // If there isn't such attribute or it has wrong value - setup it
                    if(!array_key_exists($name, $resParams) || $resParams[$name] != $value) {
                        $resParams[$name] = $value;
                    }
                }
                }
               
                // ������ ���������� ��� �������
                if(!$short && empty($content)) return '';
                // �������� ���
                $text='<'.$tag;
                // ���������
                foreach($resParams as $param=>$value) $text.=' '.$param.'="'.$value.'"';
                // �������� ���� (���� �������� �� ��� ��������)
                $text.= $short && $this->isXHTMLMode ? '/>' : '>';
                if(isset($tagRules[self::TR_TAG_CONTAINER])) $text .= "\r\n";
                if(!$short) $text.= $content.'</'.$tag.'>';
                if($parentTagIsContainer) $text .= "\r\n";
                if($tag == 'br') $text.="\r\n";
                return $text;
        }
       
        protected function comment(){
                if(!$this->matchStr('<!--')) return false;
                return $this->skipUntilStr('-->');
        }
       
        protected function anyThing(&$content = '', $parentTag = null){
                $this->skipNL();
                while($this->curChClass){
                        $tag = '';
                        $params = null;
                        $text = null;
                        $shortTag = false;              
                        $name = null;  
                       
                        // ���� �� ��������� � ������ ���� ��� ������
                        // ���������� ������� ���� �� ���������� <
                        if($this->state == self::STATE_INSIDE_NOTEXT_TAG && $this->curCh!='<'){
                                $this->skipUntilCh('<');
                        }
                       
                        // <���> ����� </���>
                        if($this->curCh == '<' && $this->tag($tag, $params, $text, $shortTag)){
                                // ����������� ��� � �����
                                $tagText = $this->makeTag($tag, $params, $text, $shortTag, $parentTag);
                                $content.=$tagText;
                                // ���������� ������� ����� <br> � ����������� �����, ������� ���������� ��������
                                if ($tag=='br') {
                                        $this->skipNL();
                                } elseif (empty($tagText)){
                                        $this->skipSpaces();
                                }
                       
                        // ���������� <!-- -->  
                        } elseif($this->curCh == '<' && $this->comment()){
                                continue;
                               
                        // ����� ���� ��� ������ <
                        } elseif($this->curCh == '<') {
                                // ���� ����������� <, �� ��� �� ���
                                // �� ��� ���� ������������� ��� ���� ���� <
                                $this->saveState();
                                if($this->tagClose($name)){
                                        // ���� ��� ������������� ���, �� �� ������ �����
                                        // � ������� �� �������
                                        // �� ���� �� �� ������ ����, �� ������ ���������� ���
                                        if($this->state == self::STATE_INSIDE_TAG || $this->state == self::STATE_INSIDE_NOTEXT_TAG) {
                                                $this->restoreState();
                                                return false;
                                        } else {
                                                $this->eror('�� ��������� �������������� ���� '.$name);
                                        }
                                } else {
                                        if($this->state != self::STATE_INSIDE_NOTEXT_TAG) $content.=$this->entities2['<'];
                                        $this->getCh();                                
                                }
                               
                        // �����
                        } elseif($this->text($text)){
                                $content.=$text;
                        }
                }
               
                return true;
        }
       
        /**
         * ������� ��������� ����� ������� ���-��
         *
         * @param int $count ������ ��� ����������� ����� ��������� �����
         * @return boolean
         */
        protected function skipNL(&$count = 0){
                if(!($this->curChClass & self::NL)) return false;
                $count++;
                $firstNL = $this->curCh;
                $nl = $this->getCh();
                while($this->curChClass & self::NL){
                        // ���� ������ ����� ������ ���� �� ��� � ������ ����������� �������
                        // ����� �����. ��� ��������� ��� ����� ����������
                        // \r\n\r\n, \r\r, \n\n - ��� ��������
                        if($nl == $firstNL) $count++;                  
                        $nl = $this->getCh();
                        // ����� ���������� ������ ����� ����������� �������
                        $this->skipSpaces();
                }
                return true;
        }
       
        protected function dash(&$dash){
                if($this->curCh != '-') return false;
                $dash = '';
                $this->saveState();
                $this->getCh();
                // ��������� ������
                while($this->curCh == '-') $this->getCh();
                if(!$this->skipNL() && !$this->skipSpaces()){
                        $this->restoreState();
                        return false;
                }
                $dash = $this->dash;
                return true;
        }
       
        protected function punctuation(&$punctuation){
                if(!($this->curChClass & self::PUNCTUATUON)) return false;
                $this->saveState();
                $punctuation = $this->curCh;
                $this->getCh();
               
                // ��������� ... � !!! � ?.. � !..
                if($punctuation == '.' && $this->curCh == '.'){
                        while($this->curCh == '.') $this->getCh();
                        $punctuation = $this->dotes;
                } elseif($punctuation == '!' && $this->curCh == '!'){
                        while($this->curCh == '!') $this->getCh();
                        $punctuation = '!!!';
                } elseif (($punctuation == '?' || $punctuation == '!') && $this->curCh == '.'){
                        while($this->curCh == '.') $this->getCh();
                        $punctuation.= '..';
                }
               
                // ����� ��� ����� - ��������� ������
                if($this->curChClass & self::RUS) {
                        if($punctuation != '.') $punctuation.= ' ';
                        return true;
                // ����� ��� ������, ������� ������, ����� ������
                } elseif(($this->curChClass & self::SPACE) || ($this->curChClass & self::NL) || !$this->curChClass){
                        return true;
                } else {
                        $this->restoreState();
                        return false;
                }
        }
       
        protected function number(&$num){
                if(!(($this->curChClass & self::NUMERIC) == self::NUMERIC)) return false;
                $num = $this->curCh;
                $this->getCh();
                while(($this->curChClass & self::NUMERIC) == self::NUMERIC){
                        $num.= $this->curCh;
                        $this->getCh();
                }
                return true;
        }
       
        protected function htmlEntity(&$entityCh){
                if($this->curCh<>'&') return false;
                $this->saveState();
                $this->matchCh('&');
                if($this->matchCh('#')){
                        $entityCode = 0;
                        if(!$this->number($entityCode) || !$this->matchCh(';')){
                                $this->restoreState();
                                return false;
                        }
                        $entityCh = html_entity_decode("&#$entityCode;", ENT_COMPAT, 'UTF-8');
                        return true;
                } else{
                        $entityName = '';
                        if(!$this->name($entityName) || !$this->matchCh(';')){
                                $this->restoreState();
                                return false;
                        }
                        $entityCh = html_entity_decode("&$entityName;", ENT_COMPAT, 'UTF-8');
                        return true;
                }
        }
       
        /**
         * �������
         *
         * @param boolean $spacesBefore ���� �� ����� �������
         * @param string $quote �������
         * @param boolean $closed �������������
         * @return boolean
         */
        protected function quote($spacesBefore,  &$quote, &$closed){
                $this->saveState();
                $quote = $this->curCh;
                $this->getCh();
                // ���� �� ���� ������� ��� �� ���� ������� � ��������� ������ - �� ����� - �� ��� ������ �� �������
                if($this->quotesOpened == 0 && !(($this->curChClass & self::ALPHA) || ($this->curChClass & self::NUMERIC))) {
                        $this->restoreState();
                        return false;
                }
                // ����������� �����, ���� �� ������� ���� ������� � (�� ������� �� ���� ������� ��� ������ ��� ���������� ���� ����� �������)
                // ���, ���� ������� ������ ���� ������� - ����� ���������
                $closed =  ($this->quotesOpened >= 2) ||
                          (($this->quotesOpened >  0) &&
                           (!$spacesBefore || $this->curChClass & self::SPACE || $this->curChClass & self::PUNCTUATUON));
                return true;
        }
       
        protected function makeQuote($closed, $level){
                $levels = count($this->textQuotes);
                if($level > $levels) $level = $levels;
                return $this->textQuotes[$level][$closed ? 1 : 0];
        }


       
        protected function text(&$text){
                $text = '';
                //$punctuation = '';
                $dash = '';
                $newLine = true;
                $newWord = true; // �������� ������ ������ �����
                $url = null;
                $href = null;
               
                // �������� ����������������?
                //$typoEnabled = true;
                $typoEnabled = !$this->noTypoMode;
               
                // ������ ������ ����� ���� <, ��� ������ ��� tag() ������ false
                // � < � ���� �� ���������
                while(($this->curCh != '<') && $this->curChClass){
                        $brCount = 0;
                        $spCount = 0;
                        $quote = null;
                        $closed = false;
                        $punctuation = null;
                        $entity = null;
                       
                        $this->skipSpaces($spCount);

                        // ������������������ ���������...
                        if (!$spCount && $this->curCh == '&' && $this->htmlEntity($entity)){
                                $text.= isset($this->entities2[$entity]) ? $this->entities2[$entity] : $entity;
                        } elseif ($typoEnabled && ($this->curChClass & self::PUNCTUATUON) && $this->punctuation($punctuation)){
                                // �������������� ���������
                                // ���� ����������� ���������� - ��������� ��
                                // ��������� ������ ����� ������ ���� ����� ��������� ������ - ��������
                                if($spCount && $punctuation == '.' && ($this->curChClass & self::LAT)) $punctuation = ' '.$punctuation;
                                $text.=$punctuation;    
                                $newWord = true;                        
                        } elseif ($typoEnabled && ($spCount || $newLine) && $this->curCh == '-' && $this->dash($dash)){
                                // ����
                                $text.=$dash;  
                                $newWord = true;
                        } elseif ($typoEnabled && ($this->curChClass & self::HTML_QUOTE) && $this->quote($spCount, $quote, $closed)){
                                // �������
                                $this->quotesOpened+=$closed ? -1 : 1;
                                // ���������� �������� ���� ������� ������������ ������ ��� �����������
                                if($this->quotesOpened<0){
                                        $closed = false;
                                        $this->quotesOpened=1;
                                }
                                $quote = $this->makeQuote($closed, $closed ? $this->quotesOpened : $this->quotesOpened-1);
                                if($spCount) $quote = ' '.$quote;
                                $text.= $quote;
                                $newWord = true;
                        } elseif ($spCount>0){
                                $text.=' ';
                                // ����� �������� ����� �������� ����� �����
                                $newWord = true;
                        } elseif ($this->isAutoBrMode && $this->skipNL($brCount)){
                                // ������� ������
                                $br = $this->br.$this->nl;
                                $text.= $brCount == 1 ? $br : $br.$br;
                                // �������� ��� ����� ������ � ����� �����
                                $newLine = true;
                                $newWord = true;
                                // !!!���������� �����
                        } elseif ($newWord && $this->isAutoLinkMode && ($this->curChClass & self::LAT) && $this->openedTag!='a' && $this->url($url, $href)){
                                // URL
                                $text.= $this->makeTag('a' , array('href' => $href), $url, false);
                        } elseif($this->curChClass & self::PRINATABLE){
                                // ���������� ������� HTML ������� ������ ������ ������ ���� (�� �� ��? ������� �� ����� ���� � ����������)
                                $text.=isset($this->entities2[$this->curCh]) ? $this->entities2[$this->curCh] : $this->curCh;
                                $this->getCh();
                                $newWord = false;
                                $newLine = false;
                                // !!!���������� � �����
                        } else {
                                // ���������� ������������ ������� ������� ������ �� �������
                                $this->getCh();
                        }
                }
               
                // �������
                $this->skipSpaces();            
                return $text != '';
        }
       
        protected function url(&$url, &$href){
                $this->saveState();
                $url = '';
                //$name = $this->name();
                //switch($name)
                $urlChMask = self::URL | self::ALPHA;
               
                if($this->matchStr('http://')){
                        while($this->curChClass & $urlChMask){
                                $url.= $this->curCh;
                                $this->getCh();
                        }
                       
                        if(!strlen($url)) {
                                $this->restoreState();
                                return false;
                        }
                       
                        $href = 'http://'.$url;
                        return true;
                } elseif($this->matchStr('www.')){
                        while($this->curChClass & $urlChMask){
                                $url.= $this->curCh;
                                $this->getCh();
                        }
                       
                        if(!strlen($url)) {
                                $this->restoreState();
                                return false;
                        }
                       
                        $url = 'www.'.$url;
                        $href = 'http://'.$url;
                        return true;            
                }
                $this->restoreState();
                return false;
        }
       
        protected function eror($message){
                $str = '';
                $strEnd = min($this->curPos + 8, $this->textLen);
                for($i = $this->curPos; $i < $strEnd; $i++){
                        $str.=$this->textBuf[$i];
                }
               
                $this->errors[] = array(
                        'message' => $message,
                        'pos'     => $this->curPos,
                        'ch'      => $this->curCh,
                        'line'    => 0,
                        'str'     => $str,
                );
        }
}

/**
 * ������� ord() ��� ������������� �����
 *
 * @param string $c ������ utf-8
 * @return int ��� �������
 */
function uniord($c) {
    $h = ord($c{0});
    if ($h <= 0x7F) {
        return $h;
    } else if ($h < 0xC2) {
        return false;
    } else if ($h <= 0xDF) {
        return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
    } else if ($h <= 0xEF) {
        return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6
                                 | (ord($c{2}) & 0x3F);
    } else if ($h <= 0xF4) {
        return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12
                                 | (ord($c{2}) & 0x3F) << 6
                                 | (ord($c{3}) & 0x3F);
    } else {
        return false;
    }
}

/**
 * ������� chr() ��� ������������� �����
 *
 * @param int $c ��� �������
 * @return string ������ utf-8
 */
function unichr($c) {
    if ($c <= 0x7F) {
        return chr($c);
    } else if ($c <= 0x7FF) {
        return chr(0xC0 | $c >> 6) . chr(0x80 | $c & 0x3F);
    } else if ($c <= 0xFFFF) {
        return chr(0xE0 | $c >> 12) . chr(0x80 | $c >> 6 & 0x3F)
                                    . chr(0x80 | $c & 0x3F);
    } else if ($c <= 0x10FFFF) {
        return chr(0xF0 | $c >> 18) . chr(0x80 | $c >> 12 & 0x3F)
                                    . chr(0x80 | $c >> 6 & 0x3F)
                                    . chr(0x80 | $c & 0x3F);
    } else {
        return false;
    }
}
?>
