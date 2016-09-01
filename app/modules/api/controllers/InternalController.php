<?php
namespace Modules\Api\Controllers;

use \Entity\Station;

class InternalController extends BaseController
{
    public function streamauthAction()
    {
        if (!$this->hasParam('id'))
            return $this->_authFail('No station specified!');

        $id = (int)$this->getParam('id');
        $station = Station::find($id);

        if (!($station instanceof Station))
            return $this->_authFail('Invalid station specified');

        /* Passed via POST from IceCast
         * [action] => stream_auth
         * [mount] => /radio.mp3
         * [ip] => 10.0.2.2
         * [server] => localhost
         * [port] => 8000
         * [user] => testuser
         * [pass] => testpass
         */

        // Log requests to a temp file for debugging.
        $request_vars = "-------\n".date('F j, Y g:i:s')."\n".print_r($_REQUEST, true)."\n".print_r($this->dispatcher->getParams(), true);
        $log_path = APP_INCLUDE_TEMP.'/icecast_stream_auth.txt';
        file_put_contents($log_path, $request_vars, \FILE_APPEND);

        if (!$station->enable_streamers)
            return $this->_authFail('Support for streamers/DJs on this station is disabled.');

        return $this->_authSuccess();
    }

    protected function _authFail($message)
    {
        $this->response->setHeader('icecast-auth-user', '0');
        $this->response->setHeader('Icecast-Auth-Message', $message);

        return $this->response->setContent('Authentication failure: '.$message);
    }

    protected function _authSuccess()
    {
        $this->response->setHeader('icecast-auth-user', 1);

        return $this->response->setContent('Success!');
    }
}