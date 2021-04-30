<?php
class MY_Security extends CI_Security {

    /**
     * CSRF Set Cookie
     *
     * @codeCoverageIgnore
     * @return	CI_Security
     */
    public function csrf_set_cookie()
    {
        $expire = time() + $this->_csrf_expire;
        $secure_cookie = (bool) config_item('cookie_secure');

        if ($secure_cookie && ! is_https())
        {
        	return FALSE;
        }

        setcookie(
            $this->_csrf_cookie_name,
            $this->_csrf_hash,
            $expire,
            config_item('cookie_path'),
            config_item('cookie_domain'),
            $secure_cookie,
            config_item('cookie_httponly')
        );
        log_message('info', 'CRSF cookie sent');

        return $this;
    }
}
