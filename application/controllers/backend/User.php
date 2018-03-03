<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
  function __construct()
  {
    parent::__construct();

    $this->load->helper('url');
    $this->load->helper('security');
    $this->load->library('parser');
    $this->load->model('user_model');
  }

  public function index()
  {
    session_start();

    if (!isset($_SESSION['user']))
    {
      $data = array(
        'base_url' => base_url(),
        'csrf_name' => $this->security->get_csrf_token_name(),
        'csrf_hash' => $this->security->get_csrf_hash()
      );

      $this->parser->parse('backend/login.html', $data);
    }
    else
    {
      $url = base_url() . 'index.php/firefolio/projects';

      header('Location: ' . $url);
      exit();
    }
  }

  public function login()
  {
    $response = array(
      'success' => FALSE,
      'message' => 'No error message specified',
      'hash' => $this->security->get_csrf_hash()
    );

    $user = $this->user_model->get_user();

    session_start();

    if (isset($_POST['username']) AND isset($_POST['password']))
    {
      $username = $_POST['username'];
      $password = $_POST['password'];

      if (strlen($username) > 0 AND strlen($password) > 0)
      {
        if ($username === $user['username'] AND
            password_verify($password, $user['password']))
        {
          $_SESSION['user'] = $username;

          $response['success'] = TRUE;
          $response['message'] = 'Login attempt successful';
        }
        else
        {
          $response['message'] = 'Incorrect login credentials';
        }
      }
      else
      {
        $response['message'] = 'Login credentials contain blank fields';
      }
    }
    else
    {
      $response['message'] = 'Login credentials not recieved';
    }

    // Return the result of the AJAX request in JSON
    $json = json_encode($response);
    echo $json;
  }

  /**
  * Logs the user out of the backend and destroys the session variable
  */
  public function logout()
  {
    session_start();
    unset($_SESSION['user']);

    $url = base_url() . 'index.php/login';

    header('Location: ' . $url);
  }
}
