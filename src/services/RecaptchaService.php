<?php
/**
 * recaptcha plugin for Craft CMS 3.x
 *
 * A simple craft plugin for validating Google's Invisible reCAPTCHA v2
 *
 * @link      http://www.gamesbykyle.com
 * @copyright Copyright (c) 2018 Kyle Andrews
 */

namespace codewithkyle\recaptcha\services;

use codewithkyle\recaptcha\Recaptcha;

use Craft;
use craft\web\Component;
use GuzzleHttp\Client;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Kyle Andrews
 * @package   Recaptcha
 * @since     0.0.0
 */
class RecaptchaService extends Component
{
    public function verify($token, $key)
    {
        $client = new Client();

        $response = $client->request('GET', 'https://www.google.com/recaptcha/api/siteverify', [
            'query' => [
                'secret' => $key,
                'response' => $token
            ]
        ]);
        $result = json_decode($validation, TRUE);

        $responseMessage = array();

        if(strlen($token) == 0){
            $responseMessage['status'] = 500;
            $responseMessage['message'] = 'Missing recaptcha token param';
            return $responseMessage;
        }

        if($result['success'] == 1) {
            $responseMessage['status'] = 200;
            $responseMessage['score'] = $result['score'];
            $responseMessage['timestamp'] = $result['challenge_ts'];

            // Using same API as v2 but 'aciton' is new to v3
            // so we need to make sure it's set before adding it
            if(!empty($result['action'])){
                $responseMessage['action'] = $result['action'];
            }

            return $responseMessage;
        } 
        else{
            $responseMessage['status'] = 500;
            if(strlen($key) == 0){
                $responseMessage['message'] = 'You\'re missing your reCAPTCHA secret key';
            }
            else{
                $responseMessage['message'] = 'reCAPTCHA couldn\'t verify this user';
                if(!empty($result['error-codes'])){
                    $responseMessage['error-codes'] = $result['error-codes'];
                }
            }
            return $responseMessage;
        }
    }
}