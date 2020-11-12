<?php
class ApiBase
{
  private $api;
  public $obj;

  const ERROR_UNKNOWN			= 0;
  const ERROR_DB			= 1;
  const ERROR_NO_ACTION			= 2;
  const ERROR_JSON_DECODE		= 3;
  const ERROR_NO_TOKEN			= 4;
  const ERROR_TOKEN_CHECK		= 5;
  const ERROR_VALIDATION		= 6;
  const ERROR_ACCESS_DENIDED            = 7;
  const ERROR_ACTION_NOT_FOUND          = 8;
  const ERROR_RUSPRIFILE                = 9;

  private $errorsList = array(
	self::ERROR_UNKNOWN			=> 'Неизвестная ошибка',
	self::ERROR_DB				=> 'Ошибка подключения к базе данных',
	self::ERROR_NO_ACTION			=> 'Не указан тип запроса (action)',
	self::ERROR_JSON_DECODE			=> 'Ошибка при парсинге JSON',
	self::ERROR_NO_TOKEN			=> 'Не указан токен',
	self::ERROR_TOKEN_CHECK			=> 'Ошибка при проверке токена',
	self::ERROR_VALIDATION			=> 'Ошибка при проверке изменяемых данных (ошибка валидации)',
	self::ERROR_ACCESS_DENIDED		=> 'Ошибка доступа',
	self::ERROR_ACTION_NOT_FOUND            => 'Метод (action) не найден'
  );

  public function __construct() {
    $this->api = (object) array();
    $this->api->errors = array();
    $this->obj = &$this->api;

    Header("Access-Control-Allow-Origin: *");
    Header("Access-Control-Allow-Headers: *");
  }
  
  // errorType - тип ошибки по документации, errorText (необязательный) - если задан, то подставится вместо стандартного (по документации) описания ошибки
  public function addError($errorType, $errorText = null) {
	// если в списке ошибкой такого типа ошибки нет, то присваеваем 0 тип (Неизвестная ошибка)
	if (!array_key_exists($errorType, $this->errorsList)) {
		$errorType = 0;
	}

	// если текст ошибки не был указан, то берем из массива
	if (empty($errorText)) {
		$errorText = $this->errorsList[$errorType];
	}

	$e = (object) array(
		'errorType' => $errorType,
		'errorText' => $errorText
	);

	return (bool)array_push($this->api->errors, $e);
  }

  public function out() {
    if(count($this->api->errors) === 0) {
      unset($this->api->errors);
    }
    header("Content-Type: application/json");
    exit(json_encode($this->api));
  }
}