<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Video {

  // Codeigniter instance
  protected $CI;

  public function __construct()
  {
    $this->CI =& get_instance();

    $this->CI->load->helper('html_purifier');
    $this->CI->load->library('parser');
  }

  // Embeds a video onto the page using its type
  // Optionally returning the embed code as a string
  public function embed($url, $return = FALSE, $width = '100%', $height = 'auto')
  {
    $data = array(
      'width' => $width,
      'height' => $height
    );
    $html = '';

    switch ($this->get_type($url))
    {
      case 'youtube':
        $endpoint = 'http://youtube.com/oembed';
        $request = $endpoint .'?url='
                   . rawurlencode($url)
                   . '&format=json&width='
                   . rawurlencode($width)
                   . '&height'
                   . rawurlencode($height);
        $oembed = json_decode($this->curl_get($request));

        if (isset($oembed))
        {
          $html = html_entity_decode($oembed->html);
        }
        else
        {
          // The user gave an invalid URL
          // Display an error message
          $html = '<strong>' .
                  'An error occured whilst trying to load this video.' .
                  '</strong>';
        }
        break;
      case 'vimeo':
        $endpoint = 'http://vimeo.com/api/oembed';
        $request = $endpoint .'.json?url=' . rawurlencode($url);
        $oembed = json_decode($this->curl_get($request));

        if (isset($oembed))
        {
          $html = html_entity_decode($oembed->html);
        }
        else
        {
          // The user gave an invalid URL
          // Display an error message
          $html = '<strong>' .
                  'An error occured whilst trying to load this video.' .
                  '</strong>';
        }
        break;
      case 'html5':
        $data['source'] = html_purify($url);
        $html = $this->CI->parser->parse('video/html5/controls.html', $data, $return);
        break;
    }

    if ($return)
    {
      return $html;
    }
  }

  public function get_thumbnail($url)
  {
    $data = array();
    $html = '';

    switch ($this->get_type($url))
    {
      case 'youtube':
        $endpoint = 'http://youtube.com/oembed';
        $request = $endpoint .'?url=' . rawurlencode($url) . '&format=json';
        $oembed = json_decode($this->curl_get($request));

        if (isset($oembed))
        {
          $html = html_entity_decode($oembed->thumbnail_url);
        }
        break;
      case 'vimeo':
        $endpoint = 'http://vimeo.com/api/oembed';
        $request = $endpoint .'.json?url=' . rawurlencode($url);
        $oembed = json_decode($this->curl_get($request));

        if (isset($oembed))
        {
          $html = html_entity_decode($oembed->thumbnail_url);
        }
        break;
      case 'html5':
        // TODO: Figure out how to show a poster for html5 video
        $html = '';
        break;
      default:
        // Show some tv static as a placeholder image
        $html = base_url() . 'img/tv_static.gif';
        break;
    }

    return $html;
  }

  // Returns the type of video based on its URL
  // The type string will always be in lower case
  private function get_type($url)
  {
    if (filter_var($url, FILTER_VALIDATE_URL) !== FALSE)
    {      $parsed_url = parse_url($url);

      if ($parsed_url['host'] === 'www.youtube.com' OR
          $parsed_url['host'] === 'youtube.com' OR
          $parsed_url['host'] === 'youtu.be')
      {
        $type = 'youtube';
      }
      elseif ($parsed_url['host'] === 'www.vimeo.com' OR
              $parsed_url['host'] === 'vimeo.com')
      {
        $type = 'vimeo';
      }
      else
      {
        // Assume self-hosted HTML5
        $type = 'html5';
      }
    }

    return $type ?? '';
  }

  private function curl_get($url)
  {
    // As in th PHP cURL library
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

    $value = curl_exec($curl);
    curl_close($curl);

    return $value;
  }
}
