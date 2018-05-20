<?php
class OauthController extends Controller
{
    public $layout           = '';
    public $model            = null;
    public $needRightActions = array('*' => false);
    private $server          = null;
    public function init()
    {
        header("Content-type: text/html; charset=" . $this->encoding);
        $this->server = Oauth2Service::getInstance();
    }


    public function token()
    {
        $this->server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
        exit();
    }

    public function resource()
    {
        if (!$this->server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->server->getResponse()->send();

            die;
        }
        echo json_encode(array('success' => true, 'message' => 'You accessed my APIs!'));
    }

    public function authorize()
    {
        $request  = OAuth2\Request::createFromGlobals();
        $response = new OAuth2\Response();

// validate the authorize request
        if (!$this->server->validateAuthorizeRequest($request, $response)) {
            $response->send();
            die;
        }
// display an authorization form
        if (empty($_POST)) {
            exit('
<form method="post">
  <label>Do You Authorize TestClient?</label><br />
  <input type="submit" name="authorized" value="yes">
  <input type="submit" name="authorized" value="no">
</form>');
        }

// print the authorization code if the user has authorized your client
        $is_authorized = ($_POST['authorized'] === 'yes');
        $this->server->handleAuthorizeRequest($request, $response, $is_authorized);
        if ($is_authorized) {
            // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
            $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
            exit("SUCCESS! Authorization Code: $code");
        }
        $response->send();
    }
}
