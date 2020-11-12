<?php
require_once('dbSettings.php');
require_once('Db.php');
require_once('ApiBase.php');
if ($_GET['token'] == SECRET_TOKEN) {		
	try {
		$api = new ApiBase();
		$db = new DbBase(DB_NAME, DB_USERNAME, DB_PASSWORD);
		$jsonRaw = file_get_contents('php://input');
		$json = json_decode($jsonRaw);
		$action = filter_input(INPUT_GET, 'action');
		
		switch ($action) {
				case 'reka':
				$api->obj->result = $db->reka($json->email, $json->languages);
				break;	
		        case 'recovery':
				$api->obj->result = $db->recovery($json->email, $json->languages);
				break;	
			    case 'changepassword':
				$api->obj->result = $db->changepassword($json->email, $json->password, $json->languages);
				break;
		        case 'reg':
				$api->obj->result = $db->reg($json->email, $json->password, $json->languages);
				break;
                case 'login':
				$api->obj->result = $db->login($json->email, $json->password, $json->languages);
				break;
				case 'authorization':
				$api->obj->result = $db->authorization($json->email, $json->token, $json->languages);
				break;
				case 'authorizationGoogle':
				$api->obj->result = $db->authorizationGoogle($json->id_token, $json->languages, $json->client_id);
				break;
                case 'authorizationFacebook':
				$api->obj->result = $db->authorizationFacebook($json->token, $json->languages);
				break;
				case 'authorizationApple':
				$api->obj->result = $db->authorizationApple($json->id_token);
				break;
                case 'setTokenGlobal':
				$api->obj->result = $db->setTokenGlobal($json->token);
				break;
                case 'getInfoTokenGlobal':
				$api->obj->result = $db->getInfoTokenGlobal($json->token);
				break;
			    case 'getListCatalog':
				$api->obj->result = $db->getListCatalog($json->language, $json->token);
				break;
                case 'getImagesOneCatalog':
				$api->obj->result = $db->getImagesOneCatalog($json->catalogs_id);
				break;
                 case 'getImagesThreeRandom':
				$api->obj->result = $db->getImagesThreeRandom($json->token);
				break;
				case 'userInfoNull':
				$api->obj->result = $db->userInfoNull($json->token);
				break;
			    case 'getMusic':
				$api->obj->result = $db->getMusic();
				break;
                        case 'getOfferDay':
				$api->obj->result = $db->getOfferDay($json->language);
				break;
                        case 'foto':
                                $api->obj->result = $db->foto();
                                break;
                        case 'audio':
                                $api->obj->result = $db->audio();
                                break;
                        case 'getOneMusic':
				$api->obj->result = $db->getOneMusic($json->name);
				break; 
                         case 'addMoney':
				$api->obj->result = $db->addMoney($json->token,$json->action,$json->count);
				break;  
                        case 'subscriptionUser':
				$api->obj->result = $db->subscriptionUser($json->token, $json->type);
				break;	    
                        case 'testSubscriptionUser':
				$api->obj->result = $db->testSubscriptionUser($json->token);
				break; 
                         case 'getSaleAvailability':
				$api->obj->result = $db->getSaleAvailability($json->token);
				break;    
                        case 'getPazzlessOneImage':
				$api->obj->result = $db->getPazzlessOneImage($json->images_id);
				break; 
                        case 'buyCatalog':
				$api->obj->result = $db->buyCatalog($json->token, $json->catalog_id_array, $json->catalog_price);
				break;                               
			case 'getPhoto':
				$api->obj->result = $db->getPhoto($json->name);
				break; 
			case 'getAllInfo':
				$api->obj->result = $db->getAllInfo($json->name, $json->countpic, $json->offset);				
				break;            
			case 'getSub':
				$api->obj->result = $db->getSub($json->name, $json->type);
				break;   
			case 'testSub':
				$api->obj->result = $db->testSub($json->name, $json->user_id, $json->type);
				break;				
			case 'doSubscribers':
				$api->obj->result = $db->doSubscribers($json->name, $json->sub_id);
				break;
			case 'unSubscribers':
				$api->obj->result = $db->unSubscribers($json->name, $json->sub_id);
				break;	
			case 'createCompetition':
				$api->obj->result = $db->createCompetition($json->hash, $json->pic_competition, $json->pic_prize, $json->pic_draw, $json->region_pic, $json->small_pic, $json->text, $json->lasting_competition);
				break;	
			case 'getAllCompetitions':
				$api->obj->result = $db->getAllCompetitions($json->type, $json->countpic, $json->offset);
				break;				
			case 'sendPictureCompetitionUser':
				$api->obj->result = $db->sendPictureCompetitionUser($json->name, $json->competition_id, $json->picture);
				break;
			case 'putLike':
				$api->obj->result = $db->putLike($json->name, $json->competitionpart_id);
				break;	
			case 'getCompetitionPictures':
				$api->obj->result = $db->getCompetitionPictures($json->competition_id);
				break;
			case 'pressOnePictureCompetition':
				$api->obj->result = $db->pressOnePictureCompetition($json->name, $json->competitionpart_id);
				break;	
			case 'addComment':
				$api->obj->result = $db->addComment($json->name, $json->competitionpart_id, $json->message);
				break;
			case 'getAllCommentPicture':
				$api->obj->result = $db->getAllCommentPicture($json->competitionpart_id);
				break;					
			case 'sendPictureInspiration':
				$api->obj->result = $db->sendPictureInspiration($json->name, $json->picture, $json->cat_pic_id);
				break;
			case 'putLikeInspiration':
				$api->obj->result = $db->putLikeInspiration($json->name, $json->inspiration_id);
				break;
			case 'addCommentInspiration':
				$api->obj->result = $db->addCommentInspiration($json->name, $json->inspiration_id, $json->message);
				break;
			case 'getInspirationLikesLastPictures':
				$api->obj->result = $db->getInspirationLikesLastPictures($json->type, $json->countpic, $json->offset);
				break;
			case 'loadCatalogPicture':			
				$api->obj->result = $db->loadCatalogPicture($json->catalog, $json->picture, $json->picture_add, $json->picture_region);
				break;	
			case 'showAllCatalog':
				$api->obj->result = $db->showAllCatalog();
				break;		
			
			case 'subscriptionAllPicturesCatalog':
				$api->obj->result = $db->subscriptionAllPicturesCatalog($json->percent);
				break;
			case 'supportSendKey':
				$api->obj->result = $db->supportSendKey($json->name, $json->keysupport);
				break;
			case 'loginSupportKey':
				$api->obj->result = $db->loginSupportKey($json->name, $json->keysupport);
				break;
			case 'deletePicture':
				$api->obj->result = $db->deletePicture($json->pictureId, $json->type);
				break;
			case 'downloadMusic':
				$api->obj->result = $db->downloadMusic();
				break;
			case 'addLanguageText':
				$api->obj->result = $db->addLanguageText($json->name, $json->text);
				break;
			case 'getLanguageText':
				$api->obj->result = $db->getLanguageText($json->name);
				break;
			case 'testSubscriptionUser':
				$api->obj->result = $db->testSubscriptionUser($json->name);
				break;
			case 'downloadCatalogPicture':
				$api->obj->result = $db->downloadCatalogPicture($json->catalog_id);
				break;				
			case 'getAllCommentsLikesOnePicture':
				$api->obj->result = $db->getAllCommentsLikesOnePicture($json->picture_id, $json->countpic, $json->offset, $json->type);
				break;
			case 'getPictureCatalog':
				$api->obj->result = $db->getPictureCatalog($json->picture_id);
				break;	
		default: 
				$api->add_error(ApiBase::ERROR_NO_ACTION);
		}
	} catch (e_validate_exception $e) {
		$api->obj->result = false;
		$api->addError(ApiBase::ERROR_VALIDATION, $e->getMessage());
	} catch (e_db_exception $e) {
		$api->obj->result = false;
		$api->addError(ApiBase::ERROR_DB, $e->getMessage());
	} catch (e_rights_exception $e) {
		$api->obj->result = false;
		$api->addError(ApiBase::ERROR_ACCESS_DENIDED, $e->getMessage());
	} catch (Exception $e) {
		$api->obj->result = false;
		$api->addError(ApiBase::ERROR_UNKNOWN, $e->getMessage());
	} finally {
		$api->out();
	}
} else {
	//	$server->fault(403,'Access denied');
	echo 'Не верный токен';	
} 