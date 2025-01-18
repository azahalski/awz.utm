<?php
namespace Awz\Utm;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Cookie;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Security;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class App {

    const NO_SITE_ID = 'all';
    const SESSION_KEY = 'AWZ_UTM';

    protected string $siteId;

    private int $utm_id = 0;
    private int $utm_parent_id = 0;
    private static array $_instances = [];

    private array $utmData;

    /**
     * @param string $siteId
     */
    private function __construct(string $siteId)
    {
        $this->siteId = $siteId;

        $request = Application::getInstance()->getContext()->getRequest();
        $session = Application::getInstance()->getSession();
        $utmId = static::getSignedId((string)$request->getCookie(self::SESSION_KEY.'_'.$this->siteId));
        if(!$utmId) {
            $utmId = static::getSignedId((string)$session->get(self::SESSION_KEY.'_'.$this->siteId));
        }
        if($utmId) {
            $this->setParentId($utmId);
            $this->setId($utmId);
            $utmData = UtmTable::getRowById($utmId);
            if(!$utmData) {
                $utmData = [];
                $this->setId(0);
            }
            $this->utmData = $utmData;
            if($parentId = $this->get('PARENT_ID')){
                $this->setParentId($parentId);
            }
        }else{
            $this->utmData = [];
        }
    }

    /**
     * @param string $siteId ид сайта
     * @return \Awz\Utm\App
     */
    public static function getInstance(string $siteId=''): App
    {
        if(!$siteId){
            $siteId = Application::getInstance()->getContext()->getSite();
        }
        if(!$siteId) $siteId = self::NO_SITE_ID;
        if(!isset(self::$_instances[$siteId])){
            self::$_instances[$siteId] = new self($siteId);
        }
        return self::$_instances[$siteId];
    }

    /**
     * получение ид метки из строки подписи
     *
     * @param string $value подпись
     * @return int
     */
    public static function getSignedId(string $value=''): int
    {

        if(!$value)
            return 0;

        try {
            $signer = new Security\Sign\Signer();
            $signedData = unserialize(
                base64_decode($signer->unsign($value)),
                ['allowed_classes' => false]
            );
            if(is_array($signedData) && isset($signedData['id']) && $signedData['id'])
                return (int) $signedData['id'];
        }catch (\Exception $e){
            return 0;
        }
        return 0;
    }

    /**
     * Установка ид записи UTM
     *
     * @param $id ид записи UTM
     * @return \Awz\Utm\App
     */
    public function setId(int $id): App
    {
        $this->utm_id = $id;
        return $this;
    }

    public function setParentId(int $id): App
    {
        $this->utm_parent_id = $id;
        return $this;
    }

    /**
     * Текущий ID метки
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->utm_id;
    }

    public function getParentId(): int
    {
        return $this->utm_parent_id;
    }

    /**
     * Возвращает значение параметра метки
     *
     * @param string $type параметр метки (source|medium|term|campaign|content)
     * @return mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function get(string $type){
        $type = mb_strtoupper(str_replace('utm_', '', $type));
        $utmData = $this->getAllData();
        if(isset($utmData[$type])) return $utmData[$type];
        return '';
    }

    /**
     * Получение значения записи метки
     *
     * @return array|mixed
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getAllData(){
        return $this->utmData;
    }

    /**
     * @param string $type
     * @param $value
     * @return $this
     */
    public function set(string $type, $value){
        $type = mb_strtoupper(str_replace('utm_', '', $type));
        $this->utmData[$type] = $value;
        return $this;
    }

    /**
     * Возвращает Источник кампании
     *
     * @return mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getSource(){
        return $this->get('utm_source');
    }

    /**
     * Возвращает Тип трафика
     *
     * @return mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getMedium(){
        return $this->get('utm_medium');
    }

    /**
     * Возвращает Название кампании
     *
     * @return mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getTerm(){
        return $this->get('utm_term');
    }

    /**
     * Возвращает Идентификатор объявления
     *
     * @return mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getCampaign(){
        return $this->get('utm_campaign');
    }

    /**
     * Возвращает Ключевое слово
     *
     * @return mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getContent(){
        return $this->get('utm_content');
    }

    public function setSource(string $value){
        return $this->set('Source', $value);
    }
    public function setMedium(string $value){
        return $this->set('Medium', $value);
    }
    public function setTerm(string $value){
        return $this->set('Term', $value);
    }
    public function setCampaign(string $value){
        return $this->set('Campaign', $value);
    }
    public function setContent(string $value){
        return $this->set('Content', $value);
    }

    public function save($new=false){
        $server = Application::getInstance()->getContext()->getServer();
        if($new){
            if(!$this->getParentId()){
                $this->setParentId($this->getId());
            }
            $this->setId(0);
        }
        if(!$this->getId() && !$this->isEmpty()){
            $data = $this->getAllData();
            $data['IP_ADDR'] = $server->getRemoteAddr();
            $data['SITE_ID'] = $this->siteId;
            $data['U_AGENT'] = $server->getUserAgent();
            $context = Application::getInstance()->getContext();
            $uri = new \Bitrix\Main\Web\Uri($context->getRequest()->getRequestUri());
            $data['PAGE'] = $uri->getPath();
            $data['REFERER'] = $server->get('HTTP_REFERER');
            $data['PARENT_ID'] = $this->getParentId();
            $data['DATE_ADD'] = DateTime::createFromTimestamp(time());
            unset($data['ID']);
            $r = UtmTable::add($data);
            if($newId = $r->getId()){
                $this->setId($newId);
            }
            $this->saveCookies();
        }
    }

    public function saveCookies(){

        $utmId = $this->getId();
        if(!$utmId) return;

        $context = Application::getInstance()->getContext();
        $session = Application::getInstance()->getSession();

        $signer = new Security\Sign\Signer();
        $signValue = $signer->sign(base64_encode(serialize(array(
            'id'=>$utmId,
            'site_id'=>$this->siteId,
            'rnd'=>Security\Random::getString(6)
        ))));
        $session->set(self::SESSION_KEY.'_'.$this->siteId, $signValue);
        $cookiesAdd = false;
        $cookieType = Option::get(Agents::MODULE, 'COOKIES', '0', $this->siteId);
        if($cookieType==1){
            $cookiesAdd = true;
        }elseif($cookieType>1){
            if(Loader::includeModule('awz.cookiessett')){
                $cookieApp = \Awz\Cookiessett\App::getInstance();
                if($cookieType==2 && $cookieApp->check(\Awz\Cookiessett\App::USER_TECH))
                    $cookiesAdd = true;
                if($cookieType==3 && $cookieApp->check(\Awz\Cookiessett\App::MARKET_TECH))
                    $cookiesAdd = true;
                if($cookieType==4 && $cookieApp->check(\Awz\Cookiessett\App::MARKET_EXT))
                    $cookiesAdd = true;
            }else{
                $cookiesAdd = true;
            }
        }
        if($cookiesAdd){
            $cookie = new Cookie(self::SESSION_KEY.'_'.$this->siteId, $signValue);
            $cookie->setPath('/');
            $context->getResponse()->addCookie($cookie);
        }
    }

    public function isEmpty(){
        return !(
            $this->getSource() || $this->getMedium() || $this->getTerm() ||
            $this->getCampaign() || $this->getContent()
        );
    }

    public function getHtml(): string
    {
        $html = '';
        if($this->get('SOURCE'))
            $html .= Loc::getMessage('AWZ_UTM_HANDLERS_UTM_SOURCE').': '.$this->get('SOURCE').'<br>';
        if($this->get('MEDIUM'))
            $html .= Loc::getMessage('AWZ_UTM_HANDLERS_UTM_MEDIUM').': '.$this->get('MEDIUM').'<br>';
        if($this->get('TERM'))
            $html .= Loc::getMessage('AWZ_UTM_HANDLERS_UTM_TERM').': '.$this->get('TERM').'<br>';
        if($this->get('CAMPAIGN'))
            $html .= Loc::getMessage('AWZ_UTM_HANDLERS_UTM_CAMPAIGN').': '.$this->get('CAMPAIGN').'<br>';
        if($this->get('CONTENT'))
            $html .= Loc::getMessage('AWZ_UTM_HANDLERS_UTM_CONTENT').': '.$this->get('CONTENT').'<br>';
        return $html ? mb_substr($html,0, -4) : "";
    }
}