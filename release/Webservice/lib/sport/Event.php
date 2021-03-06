<?php
/**
 * Created by PhpStorm.
 * User: Aliaksei Syrel
 * Date: 19.11.13
 * Time: 19:14
 */

class Event {

    private $eventID;
    private $eventHash;

    private $eventName;

    private $daysOfWeek;            // Days, when event takes place (array)     +
    private $interval;              // one interval object
    private $periods;               // When course takes place (array)          +
    private $place;                 // place object

    private $date;                  // Period alternative, only time event
    private $infoLink;              // Link to .pdf with detail info            +
    private $registration;          // Registration date                        +
    private $registrationLink;      // Link to registration date                +
    private $kew;                   // Course type: k,e,w  (array)              +

    private $eventsDB;
    private $eventPeriodsDB;
    private $eventKewDB;
    private $eventDaysOfWeekDB;

    private $attendedDate;
    private $sportName;

    const EVENT_ID = "eventID", EVENT_HASH = "eventHash",EVENT_NAME = "eventName", DAYS_OF_WEEK = "daysOfWeek", INTERVAL = "interval",
        PLACE = "place", DATE = "date", PERIODS = "periods",INFO_LINK = "infoLink",
        REGISTRATION = "registration", REGISTRATION_LINK = "registrationLink", KEW = "kew", ATTENDED_DATE = "attendedDate", SPORT_NAME = "sportName";

    public function __construct() {
        $this->daysOfWeek = new ArrayList();
        $this->periods = new ArrayList();
        $this->kew = new ArrayList();
        $this->eventsDB = new EventsDB();
        $this->eventPeriodsDB = new EventPeriods();
        $this->eventKewDB = new EventKEW();
        $this->eventDaysOfWeekDB = new EventDaysOfWeek();
    }

    public static function createByID($eventID){
        $event = new Event();
        $event->eventID = $eventID;
        $event->init();
        return $event;
    }

    public static function createByHash($hash){
        $eventDB = new EventsDB();
        $id = $eventDB->getIdByHash($hash);
        return self::createByID($id);
    }

    private function init(){
        $data = $this->eventsDB->getData($this->eventID);

        if (count($data) == 0) die("Unknown eventID".$this->eventID);

        $this->eventHash = $data[EventsDB::HASH];
        $this->eventName = $data[EventsDB::EVENT_NAME];
        $this->infoLink = $data[EventsDB::INFO_LINK];
        $this->registration = $data[EventsDB::REGISTRATION];
        $this->registrationLink = $data[EventsDB::REGISTRATION_LINK];
        $this->interval = Interval::createByID($data[EventsDB::IID]);
        if (String::length($data[EventsDB::PID]) > 0){
         $this->place = Place::createByID($data[EventsDB::PID]);
        }

        $this->initPeriods();
        $this->initKEW();
        $this->initDaysOfWeek();
    }

    private function initPeriods(){
        $data = $this->eventPeriodsDB->getPeriods($this->eventID);
        if (count($data) === 0 || count($data[0]) === 0) return;
        foreach ($data as $period){
            $this->periods->add(Int::intValue($period[EventPeriods::PERIOD]));
        }
    }

    private function initKEW(){
        $data = $this->eventKewDB->getKEW($this->eventID);
        if (count($data) === 0 || count($data[0]) === 0) return;
        foreach ($data as $kew){
            $this->kew->add($kew[EventKEW::KEW]);
        }
    }

    private function initDaysOfWeek(){
        $data = $this->eventDaysOfWeekDB->getDaysOfWeek($this->eventID);
        if (count($data) === 0 || count($data[0]) === 0) return;
        foreach ($data as $dayOfWeek){
            $this->daysOfWeek->add(Int::intValue($dayOfWeek[EventDaysOfWeek::DAY_OF_WEEK]));
        }
    }

    public function saveInDB(){
        if (String::length($this->eventID) === 0){
            $this->eventHash = $this->hash();
            $eventID = $this->eventsDB->getIdByHash($this->eventHash);
            if ($eventID === 0){
                $this->eventID = $this->eventsDB->add($this->eventHash,$this->eventName);
            }
        }

        $this->eventsDB->updateAll($this->eventID,$this->eventName,$this->date,$this->infoLink,$this->registration,$this->registrationLink);
        $this->eventPeriodsDB->updateAll($this->eventID,$this->periods->getAll());
        $this->eventKewDB->updateAll($this->eventID,$this->kew->getAll());
        $this->eventDaysOfWeekDB->updateAll($this->eventID,$this->daysOfWeek->getAll());

        $this->interval->saveInDB();
        $this->eventsDB->setInterval($this->eventID,$this->interval->getIntervalID());

        if ($this->place !== null){
            $this->place->saveInDB();
            $this->eventsDB->setPlace($this->eventID,$this->place->getPlaceID());
        }
    }

    public function hash(){
        return substr(hash("sha256",$this->eventName.$this->interval->hash().$this->periods->hash().$this->daysOfWeek->hash().$this->date),0,32);
    }

    public function getEventID(){
        return $this->eventID;
    }

    public function setInterval($interval){
        $this->interval = $interval;
    }

    public function setEventName($name){
        $this->eventName = $name;
    }

    public function setDate($date){
        $this->date = $date;
    }

    public function setDaysOfWeek($dayOfWeek){
        $this->daysOfWeek = $dayOfWeek;
    }

    public function setKew($kew){
        $this->kew = $kew;
    }

    public function setPeriods($periods){
        $this->periods = $periods;
    }

    public function setPlace($place){
        $this->place = $place;
    }

    public function setInfoLink($infoLink){
        $this->infoLink = $infoLink;
    }

    public function setRegistration($registration){
        $this->registration = $registration;
    }

    public function setRegistrationLink($registrationLink){
        $this->registrationLink = $registrationLink;
    }

    public function setAttendedDate($date){
        $this->attendedDate = $date;
    }

    public function initSportName(){
        $sportDB = new SportsDB();
        $sportEvents = new SportEvents();
        $sportID = $sportEvents->getSportID($this->eventID);
        if ($sportID === 0) return;
        $data = $sportDB->getData($sportID);
        $this->sportName = $data[SportsDB::SPORT];
    }

    public function __toString(){
        $str = '{';
        $str .= self::EVENT_ID.': '.$this->eventID;
        $str .= ', '.self::EVENT_NAME.': '.$this->eventName;
        $str .= ', '.self::DAYS_OF_WEEK.': '.$this->daysOfWeek;
        $str .= ', '.self::INTERVAL.': '.$this->interval;
        $str .= ', '.self::PLACE.': '.$this->place;
        $str .= ', '.self::DATE.': '.$this->date;
        $str .= ', '.self::PERIODS.': '.$this->periods;
        $str .= ', '.self::INFO_LINK.': '.$this->infoLink;
        $str .= ', '.self::REGISTRATION.': '.$this->registration;
        $str .= ', '.self::REGISTRATION_LINK.': '.$this->registrationLink;
        $str .= ', '.self::KEW.': '.$this->kew;
        return $str."}\n";
    }

    public function toJson(){
        $jSonArray = array();
        $jSonArray[self::EVENT_ID] = Int::intValue($this->eventID);
        if (String::length($this->eventHash) > 0)$jSonArray[self::EVENT_HASH] = $this->eventHash;
        else $jSonArray[self::EVENT_HASH] = $this->hash();
        $jSonArray[self::EVENT_NAME] = $this->eventName;
        $jSonArray[self::INTERVAL] = $this->interval->toJson();
        if ($this->place !== null) $jSonArray[self::PLACE] = $this->place->toJson();
        else $jSonArray[self::PLACE] = null;
        $jSonArray[self::DATE] = $this->date;
        $jSonArray[self::PERIODS] = $this->periods->getAll();
        $jSonArray[self::DAYS_OF_WEEK] = $this->daysOfWeek->getAll();
        $jSonArray[self::INFO_LINK] = $this->infoLink;
        $jSonArray[self::REGISTRATION] = $this->registration;
        $jSonArray[self::REGISTRATION_LINK] = $this->registrationLink;
        $jSonArray[self::KEW] = $this->kew->getAll();
        if (String::length($this->attendedDate) > 0) {
            $jSonArray[self::ATTENDED_DATE] = array("date"=>Int::intValue($this->attendedDate));
        }
        if (String::length($this->sportName) > 0) {
            $jSonArray[self::SPORT_NAME] = $this->sportName;
        }
        return $jSonArray;
    }
} 