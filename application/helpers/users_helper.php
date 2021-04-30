<?php
function IsUsernameAvailable( $username ) {
    $CI = &get_instance();
    $CI->load->model('User_model', 'user_model');
    $user = $CI->user_model->get_user($username);
    if ( empty($user) ) return true;
    return false;
}
/* End of file users_helper.php */
/* Location: ./application/helpers/users_helper.php */
