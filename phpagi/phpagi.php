<?php

 /**
  * phpagi.php : PHP AGI Functions for Asterisk
  * Website: http://phpagi.sourceforge.net/
  *
  * $Id: phpagi.php,v 2.5 2005/02/16 23:31:50 pinhole Exp $
  *
  * Copyright (c) 2003,2004 Matthew Asham <matthewa@bcwireless.net>
  * All Rights Reserved.
  *
  * This software is released under the terms of the GNU Public License v2
  * A copy of which is available from http://www.fsf.org/licenses/gpl.txt
  *
  *
  * You are requested to drop me an Email letting me know that you're
  * using it.  This is more of a courtesy than anything else, but I am
  * interested to know how it is being used.
  *
  *
  * Written for PHP 4.3.4, should work with older PHP 4.x versions.
  * Please submit bug reports, patches, etc to http://sourceforge.net/projects/phpagi/
  * Gracias. :)
  *
  *
  * @package phpAGI
  * @version 2.0
  */

  if(!class_exists('AGI_AsteriskManager'))
  {
    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'phpagi-asmanager.php');
  }

  define('AST_CONFIG_DIR', '/etc/asterisk/meet1');
  define('AST_SPOOL_DIR', '/var/spool/asterisk/');
  define('AST_TMP_DIR', AST_SPOOL_DIR . '/tmp/');
  define('DEFAULT_PHPAGI_CONFIG', AST_CONFIG_DIR . '/phpagi.conf');

  define('AST_DIGIT_ANY', '0123456789#*');

  define('AGIRES_OK', 200);

  define('AST_STATE_DOWN', 0);
  define('AST_STATE_RESERVED', 1);
  define('AST_STATE_OFFHOOK', 2);
  define('AST_STATE_DIALING', 3);
  define('AST_STATE_RING', 4);
  define('AST_STATE_RINGING', 5);
  define('AST_STATE_UP', 6);
  define('AST_STATE_BUSY', 7);
  define('AST_STATE_DIALING_OFFHOOK', 8);
  define('AST_STATE_PRERING', 9);

 /**
  * AGI class
  *
  * @package phpAGI
  * @link http://www.voip-info.org/wiki-Asterisk+agi
  * @example examples/dtmf.php Get DTMF tones from the user and say the digits
  * @example examples/input.php Get text input from the user and say it back
  * @example examples/ping.php Ping an IP address
  */
  class AGI
  {
   /**
    * Request variables read in on initialization
    *
    * Often contains any/all of the following:
    *   agi_request - name of agi script
    *   agi_channel - current channel
    *   agi_language - current language
    *   agi_type - channel type (SIP, ZAP, IAX, ...)
    *   agi_uniqueid - unique id based on unix time
    *   agi_callerid - callerID string
    *   agi_dnid - dialed number id
    *   agi_rdnis - referring DNIS number
    *   agi_context - current context
    *   agi_extension - extension dialed
    *   agi_priority - current priority
    *   agi_enhanced - value is 1.0 if started as an EAGI script
    *   agi_accountcode - set by SetAccount in the dialplan
    *
    * @var array
    * @access public
    */
    var $request;

   /**
    * Config variables
    *
    * @var array
    * @access public
    */
    var $config;

   /**
    * Asterisk Manager
    *
    * @var AGI_AsteriskManager
    * @access public
    */
    var $asmanager;

   /**
    * Input Stream
    *
    * @access private
    */
    var $in;

   /**
    * Output Stream
    *
    * @access private
    */
    var $out;


   /**
    * Constructor
    *
    * @param string $config is the name of the config file to parse
    * @param array $optconfig is an array of configuration vars and vals, stuffed into $this->config['phpagi']
    */
    function AGI($config=NULL, $optconfig=array())
    {
      // load config
      if(!is_null($config) && file_exists($config))
        $this->config = parse_ini_file($config, true);
      elseif(file_exists(DEFAULT_PHPAGI_CONFIG))
        $this->config = parse_ini_file(DEFAULT_PHPAGI_CONFIG, true);

      // If optconfig is specified, stuff vals and vars into 'phpagi' config array.
      foreach($optconfig as $var=>$val)
        $this->config['phpagi'][$var] = $val;

      // add default values to config for uninitialized values
      if(!isset($this->config['phpagi']['error_handler'])) $this->config['phpagi']['error_handler'] = true;
      if(!isset($this->config['phpagi']['debug'])) $this->config['phpagi']['debug'] = false;
      if(!isset($this->config['phpagi']['admin'])) $this->config['phpagi']['admin'] = NULL;
      if(!isset($this->config['phpagi']['tempdir'])) $this->config['phpagi']['tempdir'] = AST_TMP_DIR;

      // festival TTS config
      if(!isset($this->config['festival']['text2wave'])) $this->config['festival']['text2wave'] = $this->which('text2wave');

      // swift TTS config
      if(!isset($this->config['cepstral']['swift'])) $this->config['cepstral']['swift'] = $this->which('swift');

      ob_implicit_flush(true);

      // open stdin
      $this->in = fopen('php://stdin', 'r');

      // open stdout
      $this->out = defined('STDOUT') ? STDOUT : fopen('php://stdout', 'w');

      // initialize error handler
      if($this->config['phpagi']['error_handler'] == true)
      {
        set_error_handler('phpagi_error_handler');
        global $phpagi_error_handler_email;
        $phpagi_error_handler_email = $this->config['phpagi']['admin'];
        error_reporting(E_ALL);
      }

      // make sure temp folder exists
      $this->make_folder($this->config['phpagi']['tempdir']);

      // read the request
      $str = fgets($this->in);
      while($str != "\n")
      {
        $this->request[substr($str, 0, strpos($str, ':'))] = trim(substr($str, strpos($str, ':') + 1));
        $str = fgets($this->in);
      }

      $this->conlog('AGI Request:');
      $this->conlog(print_r($this->request, true));
      $this->conlog('PHPAGI internal configuration:');
      $this->conlog(print_r($this->config, true));
    }

   /**
    * Answer channel if not already in answer state.
    *
    * @example examples/dtmf.php Get DTMF tones from the user and say the digits
    * @example examples/input.php Get text input from the user and say it back
    * @example examples/ping.php Ping an IP address
    *
    * @return array, see evaluate for return information.  ['result'] is 0 on success, -1 on failure.
    */
    function answer()
    {
      return $this->evaluate('ANSWER');
    }

   /**
    * Cause the channel to automatically hangup at $time seconds in the future.
    * If $time is 0 then the autohangup feature is disabled on this channel.
    *
    * If the channel is hungup prior to $time seconds, this setting has no effect.
    *
    * @param integer $time until automatic hangup
    * @return array, see evaluate for return information.
    */
    function autohangup($time=0)
    {
      return $this->evaluate("AUTOHANGUP $time");
    }

   /**
    * Get the status of the specified channel. If no channel name is specified, return the status of the current channel.
    *
    * @param string $channel
    * @return array, see evaluate for return information. ['data'] contains description.
    */
    function channel_status($channel='')
    {
      $ret = $this->evaluate("CHANNEL STATUS $channel");
      switch($ret['result'])
      {
        case -1: $ret['data'] = trim("There is no channel that matches $channel"); break;
        case AST_STATE_DOWN: $ret['data'] = 'Channel is down and available'; break;
        case AST_STATE_RESERVED: $ret['data'] = 'Channel is down, but reserved'; break;
        case AST_STATE_OFFHOOK: $ret['data'] = 'Channel is off hook'; break;
        case AST_STATE_DIALING: $ret['data'] = 'Digits (or equivalent) have been dialed'; break;
        case AST_STATE_RING: $ret['data'] = 'Line is ringing'; break;
        case AST_STATE_RINGING: $ret['data'] = 'Remote end is ringing'; break;
        case AST_STATE_UP: $ret['data'] = 'Line is up'; break;
        case AST_STATE_BUSY: $ret['data'] = 'Line is busy'; break;
        case AST_STATE_DIALING_OFFHOOK: $ret['data'] = 'Digits (or equivalent) have been dialed while offhook'; break;
        case AST_STATE_PRERING: $ret['data'] = 'Channel has detected an incoming call and is waiting for ring'; break;
        default: $ret['data'] = "Unknown ({$ret['result']})"; break;
      }
      return $ret;
    }

   /**
    * Executes the specified Asterisk application with given options
    *
    * @link http://www.voip-info.org/wiki-Asterisk+-+documentation+of+application+commands
    * @param string $cmd command
    * @return array, see evaluate for return information. ['result'] is whatever the application returns, or -2 on failure to find application
    */
    function exec($application, $options)
    {
      return $this->evaluate("EXEC $application $options");
    }

   /**
    * Plays the given file and receives DTMF data.
    *
    * This is similar to STREAM FILE, but this command can accept and return many DTMF digits,
    *  while STREAM FILE returns immediately after the first DTMF digit is detected.
    *
    * Asterisk looks for the file to play in /var/lib/asterisk/sounds 
    *
    * If the user doesn't press any keys when the message plays, there is $timeout milliseconds
    * of silence then the command ends. 
    *
    * The user has the opportunity to press a key at any time during the message or the
    * post-message silence. If the user presses a key while the message is playing, the
    * message stops playing. When the first key is pressed a timer starts counting for
    * $timeout milliseconds. Every time the user presses another key the timer is restarted.
    * The command ends when the counter goes to zero or the maximum number of digits is entered,
    * whichever happens first. 
    *
    * If you don't specify a time out then a default timeout of 2000 is used following a pressed
    * digit. If no digits are pressed then 6 seconds of silence follow the message. 
    *
    * If you don't specify $max_digits then the user can enter as many digits as they want. 
    *
    * Pressing the # key has the same effect as the timer running out: the command ends and
    * any previously keyed digits are returned. A side effect of this is that there is no
    * way to read a # key using this command.
    *
    * @example examples/ping.php Ping an IP address
    *
    * @link http://www.voip-info.org/wiki-get+data
    * @param string $filename file to play. Do not include file extension.
    * @param integer $timeout milliseconds
    * @param integer $max_digits
    * @return array, see evaluate for return information. ['result'] holds the digits and ['data'] holds the timeout if present.
    *
    * This differs from other commands with return DTMF as numbers representing ASCII characters.
    */
    function get_data($filename, $timeout=NULL, $max_digits=NULL)
    {
      return $this->evaluate(rtrim("GET DATA $filename $timeout $max_digits"));
    }

   /**
    * Fetch the value of a variable.
    *
    * Does not work with global variables. Does not work with some variables that are generated by modules.
    *
    * @link http://www.voip-info.org/wiki-Asterisk+variables
    * @param string $variable name
    * @return array, see evaluate for return information. ['result'] is 0 if variable hasn't been set, 1 if it has. ['data'] holds the value.
    */
    function get_variable($variable)
    {
      return $this->evaluate("GET VARIABLE $variable");
    }

   /**
    * Hangup the specified channel. If no channel name is given, hang up the current channel.
    *
    * With power comes responsibility. Hanging up channels other than your own isn't something
    * that is done routinely. If you are not sure why you are doing so, then don't.
    *
    * Most channels do not support the reception of text.
    *
    * @example examples/dtmf.php Get DTMF tones from the user and say the digits
    * @example examples/input.php Get text input from the user and say it back
    * @example examples/ping.php Ping an IP address
    *
    * @param string $channel
    * @return array, see evaluate for return information. ['result'] is 1 on success, -1 on failure.
    */
    function hangup($channel='')
    {
      return $this->evaluate("HANGUP $channel");
    }

   /**
    * Receive a character of text from a connected channel. Waits up to $timeout milliseconds for
    * a character to arrive, or infinitely if $timeout is zero.
    *
    * @param integer $timeout milliseconds
    * @return array, see evaluate for return information. ['result'] is 0 on timeout or not supported, -1 on failure. Otherwise 
    * it is the decimal value of the DTMF tone. Use chr() to convert to ASCII.
    */
    function receive_char($timeout=-1)
    {
      return $this->evaluate("RECEIVE CHAR $timeout");
    }

   /**
    * Record sound to a file until an acceptable DTMF digit is received or a specified amount of
    * time has passed. Optionally the file BEEP is played before recording begins.
    *
    * @link http://www.voip-info.org/wiki-record+file
    * @param string $file to record, without extension, often created in /var/lib/asterisk/sounds
    * @param string $format of the file. GSM and WAV are commonly used formats. MP3 is read-only and thus cannot be used.
    * @param string $escape_digits
    * @param integer $timeout is the maximum record time in milliseconds, or -1 for no timeout.
    * @param boolean $beep
    * @param integer $silence number of seconds of silence allowed before the function returns despite the 
    * lack of dtmf digits or reaching timeout.
    * @return array, see evaluate for return information. ['result'] is -1 on error, 0 on hangup, otherwise a decimal value of the 
    * DTMF tone. Use chr() to convert to ASCII.
    */
    function record_file($file, $format, $escape_digits='', $timeout=-1, $beep=false, $silence=NULL)
    {
      $cmd = "RECORD FILE $file $format \"$escape_digits\" $timeout";
      if($beep) $cmd .= ' BEEP';
      if(!is_null($silence)) $cmd .= " s=$silence";
      return $this->evaluate($cmd);
    }

   /**
    * Say the given digit string, returning early if any of the given DTMF escape digits are received on the channel.
    *
    * @link http://www.voip-info.org/wiki-say+digits
    * @param integer $digits
    * @param string $escape_digits
    * @return array, see evaluate for return information. ['result'] is -1 on hangup or error, 0 if playback completes with no 
    * digit received, otherwise a decimal value of the DTMF tone.  Use chr() to convert to ASCII.
    */
    function say_digits($digits, $escape_digits='')
    {
      return $this->evaluate("SAY DIGITS $digits \"$escape_digits\"");
    }

   /**
    * Say the given character string, returning early if any of the given DTMF escape digits are received on the channel.
    *
    * @param string $text
    * @param string $escape_digits
    * @return array, see evaluate for return information. ['result'] is -1 on hangup or error, 0 if playback completes with no 
    * digit received, otherwise a decimal value of the DTMF tone.  Use chr() to convert to ASCII.
    */
    function say_phonetic($text, $escape_digits='')
    {
      return $this->evaluate("SAY PHONETIC $text \"$escape_digits\"");
    }

   /**
    * Say the given number, returning early if any of the given DTMF escape digits are received on the channel.
    *
    * @link http://www.voip-info.org/wiki-say+number
    * @param integer $number
    * @param string $escape_digits
    * @return array, see evaluate for return information. ['result'] is -1 on hangup or error, 0 if playback completes with no 
    * digit received, otherwise a decimal value of the DTMF tone.  Use chr() to convert to ASCII.
    */
    function say_number($number, $escape_digits='')
    {
      return $this->evaluate("SAY NUMBER $number \"$escape_digits\"");
    }

   /**
    * Send the specified image on a channel.
    *
    * Most channels do not support the transmission of images.
    *
    * @param string $image without extension, often in /var/lib/asterisk/images
    * @return array, see evaluate for return information. ['result'] is -1 on hangup or error, 0 if the image is sent or 
    * channel does not support image transmission.
    */
    function send_image($image)
    {
      return $this->evaluate("SEND IMAGE $image");
    }

   /**
    * Send the given text to the connected channel.
    *
    * Most channels do not support transmission of text.
    *
    * @param $text
    * @return array, see evaluate for return information. ['result'] is -1 on hangup or error, 0 if the text is sent or 
    * channel does not support text transmission.
    */
    function send_text($text)
    {
      return $this->evaluate("SEND TEXT \"$text\"");
    }

   /**
    * Changes the caller ID of the current channel
    *
    * @param string $cid example: "John Smith"<1234567>
    * This command will let you take liberties with the <caller ID specification> but the format shown in the example above works 
    * well: the name enclosed in double quotes followed immediately by the number inside angle brackets. If there is no name then
    * you can omit it. If the name contains no spaces you can omit the double quotes around it. The number must follow the name
    * immediately; don't put a space between them. The angle brackets around the number are necessary; if you omit them the
    * number will be considered to be part of the name.
    * @return array, see evaluate for return information.
    */
    function set_callerid($cid)
    {
      return $this->evaluate("SET CALLERID $cid");
    }

   /**
    * Sets the context for continuation upon exiting the application.
    *
    * Setting the context does NOT automatically reset the extension and the priority; if you want to start at the top of the new 
    * context you should set extension and priority yourself. 
    *
    * If you specify a non-existent context you receive no error indication (['result'] is still 0) but you do get a 
    * warning message on the Asterisk console.
    *
    * @param string $context 
    * @return array, see evaluate for return information.
    */
    function set_context($context)
    {
      return $this->evaluate("SET CONTEXT $context");
    }

   /**
    * Set the extension to be used for continuation upon exiting the application.
    *
    * Setting the extension does NOT automatically reset the priority. If you want to start with the first priority of the 
    * extension you should set the priority yourself. 
    *
    * If you specify a non-existent extension you receive no error indication (['result'] is still 0) but you do 
    * get a warning message on the Asterisk console.
    *
    * @param string $extension
    * @return array, see evaluate for return information.
    */
    function set_extension($extension)
    {
      return $this->evaluate("SET EXTENSION $extension");
    }

   /**
    * Set the priority to be used for continuation upon exiting the application.
    *
    * If you specify a non-existent priority you receive no error indication (['result'] is still 0)
    * and no warning is issued on the Asterisk console.
    *
    * @param integer $priority
    * @return array, see evaluate for return information.
    */
    function set_priority($priority)
    {
      return $this->evaluate("SET PRIORITY $priority");
    }

   /**
    * Sets a variable to the specified value. The variables so created can later be used by later using ${<variablename>}
    * in the dialplan.
    *
    * These variables live in the channel Asterisk creates when you pickup a phone and as such they are both local and temporary. 
    * Variables created in one channel can not be accessed by another channel. When you hang up the phone, the channel is deleted 
    * and any variables in that channel are deleted as well.
    *
    * @param string $variable is case sensitive
    * @param string $value
    * @return array, see evaluate for return information.
    */
    function set_variable($variable, $value)
    {
      $value = str_replace("\n", '\n', addslashes($value));
      return $this->evaluate("SET VARIABLE $variable \"$value\"");
    }

   /**
    * Play the given audio file, allowing playback to be interrupted by a DTMF digit. This command is similar to the GET DATA 
    * command but this command returns after the first DTMF digit has been pressed while GET DATA can accumulated any number of 
    * digits before returning.
    *
    * @example examples/ping.php Ping an IP address
    *
    * @param string $filename without extension, often in /var/lib/asterisk/sounds
    * @param string $escape_digits
    * @param integer $offset
    * @return array, see evaluate for return information. ['result'] is -1 on hangup or error, 0 if playback completes with no 
    * digit received, otherwise a decimal value of the DTMF tone.  Use chr() to convert to ASCII.
    */
    function stream_file($filename, $escape_digits='', $offset=0)
    {
      return $this->evaluate("STREAM FILE $filename \"$escape_digits\" $offset");
    }

   /**
    * Enable or disable TDD transmission/reception on the current channel.
    *
    * @param string $setting can be on, off or mate
    * @return array, see evaluate for return information. ['result'] is 1 on sucess, 0 if the channel is not TDD capable.
    */
    function tdd_mode($setting)
    {
      return $this->evaluate("TDD MODE $setting");
    }

   /**
    * Sends $message to the Asterisk console via the 'verbose' message system.
    *
    * If the Asterisk verbosity level is $level or greater, send $message to the console.
    *
    * The Asterisk verbosity system works as follows. The Asterisk user gets to set the desired verbosity at startup time or later 
    * using the console 'set verbose' command. Messages are displayed on the console if their verbose level is less than or equal 
    * to desired verbosity set by the user. More important messages should have a low verbose level; less important messages 
    * should have a high verbose level.
    *
    * @param string $message
    * @param integer $level from 1 to 4
    * @return array, see evaluate for return information.
    */
    function verbose($message, $level=1)
    {
      foreach(explode("\n", str_replace("\r\n", "\n", print_r($message, true))) as $msg)
      {
        @syslog(LOG_WARNING, $msg);
        $ret = $this->evaluate("VERBOSE \"$msg\" $level");
      }
      return $ret;
    }

   /**
    * Waits up to $timeout milliseconds for channel to receive a DTMF digit
    *
    * @param integer $timeout in millisecons. Use -1 for the timeout value if you want the call to wait indefinitely.
    * @return array, see evaluate for return information. ['result'] is 0 if wait completes with no 
    * digit received, otherwise a decimal value of the DTMF tone.  Use chr() to convert to ASCII.
    */
    function wait_for_digit($timeout=-1)
    {
      return $this->evaluate("WAIT FOR DIGIT $timeout");
    }

   /**
    * Adds or updates an entry in the Asterisk database for a given family, key, and value.
    *
    * @param string $family
    * @param string $key
    * @param string $value
    * @return array, see evaluate for return information. ['result'] is 1 on sucess, 0 otherwise
    */
    function database_put($family, $key, $value)
    {
      $value = str_replace("\n", '\n', addslashes($value));
      return $this->evaluate("DATABASE PUT \"$family\" \"$key\" \"$value\"");
    }

   /**
    * Retrieves an entry in the Asterisk database for a given family and key.
    *
    * @param string $family
    * @param string $key
    * @return array, see evaluate for return information. ['result'] is 1 on sucess, 0 failure. ['data'] holds the value
    */
    function database_get($family, $key)
    {
      return $this->evaluate("DATABASE GET \"$family\ \"$key\"");
    }

   /**
    * Deletes an entry in the Asterisk database for a given family and key.
    *
    * @param string $family
    * @param string $key
    * @return array, see evaluate for return information. ['result'] is 1 on sucess, 0 otherwise.
    */
    function database_del($family, $key)
    {
      return $this->evaluate("DATABASE DEL \"$family\" \"$key\"");
    }

   /**
    * Deletes a family or specific keytree within a family in the Asterisk database.
    *
    * @param string $family
    * @param string $keytree
    * @return array, see evaluate for return information. ['result'] is 1 on sucess, 0 otherwise.
    */
    function database_deltree($family, $keytree='')
    {
      $cmd = "DATABASE DELTREE \"$family\"";
      if($keytree != '') $cmd .= " \"$keytree\"";
      return $this->evaluate($cmd);
    }


   /**
    * Say a given time, returning early if any of the given DTMF escape digits are received on the channel.
    *
    * @link http://www.voip-info.org/wiki-say+time
    * @param integer $time
    * @param string $escape_digits
    * @return array, see evaluate for return information. ['result'] is -1 on hangup or error, 0 if playback completes with no 
    * digit received, otherwise a decimal value of the DTMF tone.  Use chr() to convert to ASCII.
    */
    function say_time($time=NULL, $escape_digits='')
    {
      if(is_null($time)) $time = time();
      return $this->evaluate("SAY TIME $time \"$escape_digits\"");
    }

   /**
    * Does nothing
    *
    * @return array, see evaluate for return information.
    */
    function noop()
    {
      return $this->evaluate('NOOP');
    }

   /**
    * Enable/Disable Music on hold generator
    *
    * @param boolean $enabled
    * @param string $class
    * @return array, see evaluate for return information.
    */
    function set_music($enabled=true, $class='')
    {
      $enabled = ($enabled) ? 'ON' : 'OFF';
      return $this->evaluate("SET MUSIC $enabled $class");
    }

   // *********************************************************************************************************
   // **                       APPLICATIONS                                                                  **
   // *********************************************************************************************************

   /**
    * Set absolute maximum time of call
    *
    * Note that the timeout is set from the current time forward, not counting the number of seconds the call has already been up. 
    * Each time you call AbsoluteTimeout(), all previous absolute timeouts are cancelled. 
    * Will return the call to the T extension so that you can playback an explanatory note to the calling party (the called party 
    * will not hear that)
    *
    * @link http://www.voip-info.org/wiki-Asterisk+-+documentation+of+application+commands
    * @link http://www.dynx.net/ASTERISK/AGI/ccard/agi-ccard.agi
    * @param $seconds allowed, 0 disables timeout
    * @return array, see evaluate for return information.
    */
    function exec_absolutetimeout($seconds=0)
    {
      return $this->exec('AbsoluteTimeout', $seconds);
    }

   /**
    * Executes an AGI compliant application
    *
    * @param string $command
    * @return array, see evaluate for return information. ['result'] is -1 on hangup or if application requested hangup, or 0 on non-hangup exit.
    * @param string $args
    */
    function exec_agi($command, $args)
    {
      return $this->exec("AGI $command", $args);
    }

   /**
    * Set Language 
    *
    * @param string $language code
    * @return array, see evaluate for return information.
    */
    function exec_setlanguage($language='en')
    {
      return $this->exec('SetLanguage', $language);
    }

   /**
    * Do ENUM Lookup
    *
    * Note: to retrieve the result, use
    *   get_variable('ENUM');
    *
    * @param $exten
    * @return array, see evaluate for return information.
    */
    function exec_enumlookup($exten)
    {
      return $this->exec('EnumLookup', $exten);
    }

   /**
    * Dial
    *
    * Dial takes input from ${VXML_URL} to send XML Url to Cisco 7960
    * Dial takes input from ${ALERT_INFO} to set ring cadence for Cisco phones
    * Dial returns ${CAUSECODE}: If the dial failed, this is the errormessage.
    * Dial returns ${DIALSTATUS}: Text code returning status of last dial attempt.
    *
    * @link http://www.voip-info.org/wiki-Asterisk+cmd+Dial
    * @param string $type
    * @param string $identifier
    * @param integer $timeout
    * @param string $options
    * @param string $url
    * @return array, see evaluate for return information.
    */
    function exec_dial($type, $identifier, $timeout=NULL, $options=NULL, $url=NULL)
    {
      return $this->exec('Dial', trim("$type/$identifier|$timeout|$options|$url", '|'));
    }

   /**
    * Goto
    *
    * This function takes three arguments: context,extension, and priority, but the leading arguments
    * are optional, not the trailing arguments.  Thuse goto($z) sets the priority to $z.
    *
    * @param string $a
    * @param string $b;
    * @param string $c;
    * @return array, see evaluate for return information.
    */
    function exec_goto($a, $b=NULL, $c=NULL)
    {
      return $this->exec('Goto', trim("$a|$b|$c", '|'));
    }

   // *********************************************************************************************************
   // **                       DERIVED                                                                       **
   // *********************************************************************************************************

   /**
    * Goto - Set context, extension and priority
    *
    * @param string $context
    * @param string $extension
    * @param string $priority
    */
    function gotoo($context, $extension='s', $priority=1)
    {
      $this->set_context($context);
      $this->set_extension($extension);
      $this->set_priority($priority);
    }

   /**
    * Parse caller id
    *
    * @example examples/dtmf.php Get DTMF tones from the user and say the digits
    * @example examples/input.php Get text input from the user and say it back
    *
    * "name" <proto:user@server:port>
    *
    * @param string $callerid
    * @return array('Name'=>$name, 'Number'=>$number)
    */
    function parse_callerid($callerid=NULL)
    {
      if(is_null($callerid))
        $callerid = $this->request['agi_callerid'];

      $ret = array('name'=>'', 'protocol'=>'', 'username'=>'', 'host'=>'', 'port'=>'');
      $callerid = trim($callerid);

      if($callerid{0} == '"' || $callerid{0} == "'")
      {
        $d = $callerid{0};
        $callerid = explode($d, substr($callerid, 1));
        $ret['name'] = array_shift($callerid);
        $callerid = join($d, $callerid);
      }

      $callerid = explode('@', trim($callerid, '<> '));
      $username  = explode(':', array_shift($callerid));
      if(count($username) == 1)
        $ret['username'] = $username[0];
      else
      {
        $ret['protocol'] = array_shift($username);
        $ret['username'] = join(':', $username);
      }

      $callerid = join('@', $callerid);
      $host = explode(':', $callerid);
      if(count($host) == 1)
        $ret['host'] =  $host[0];
      else
      {
        $ret['host'] = array_shift($host);
        $ret['port'] = join(':', $host);
      }

      return $ret;
    }

   /**
    * Use festival to read text
    *
    * @example examples/dtmf.php Get DTMF tones from the user and say the digits
    * @example examples/input.php Get text input from the user and say it back
    * @example examples/ping.php Ping an IP address
    *
    * @link http://www.cstr.ed.ac.uk/projects/festival/
    * @param string $text
    * @param string $escape_digits
    * @param integer $frequency
    * @return array, see evaluate for return information.
    */
    function text2wav($text, $escape_digits='', $frequency=8000)
    {
      $text = trim($text);
      if($text == '') return true;

      // create the wave file
      $fname = $this->config['phpagi']['tempdir'] . DIRECTORY_SEPARATOR;
      $fname .= str_replace('.', '_', 'txt2wav_' . $this->request['agi_uniqueid']);
      $p = popen("{$this->config['festival']['text2wave']} -F $frequency -o $fname.wav", 'w');
      fputs($p, $text);
      pclose($p);

      // stream it
      $ret = $this->stream_file($fname, $escape_digits);

      // destroy it
      if(file_exists($fname . '.wav'))
        unlink($fname . '.wav');

      return $ret;
    }

   /**
    * Use Cepstral Swift to read text
    *
    * @link http://www.cepstral.com/
    * @param string $text
    * @param string $escape_digits
    * @param integer $frequency
    * @return array, see evaluate for return information.
    */
    function swift($text, $escape_digits='', $frequency=8000, $voice=NULL)
    {
      $text = trim($text);
      if($text == '') return true;

      if(!is_null($voice))
        $voice = "-n $voice";
      elseif(isset($this->config['cepstral']['voice']))
        $voice = "-n {$this->config['cepstral']['voice']}";

      // create the wave file
      $fname = $this->config['phpagi']['tempdir'] . DIRECTORY_SEPARATOR;
      $fname .= str_replace('.', '_', 'swift_' . $this->request['agi_uniqueid']);
      $p = popen("{$this->config['cepstral']['swift']} -p audio/channels=1,audio/sampling-rate=$frequency $voice -o $fname.wav -f -", 'w');
      fputs($p, $text);
      pclose($p);

      // stream it
      $ret = $this->stream_file($fname, $escape_digits);

      // destroy it
      if(file_exists($fname . '.wav'))
        unlink($fname . '.wav');

      return $ret;
    }

   /**
    * Text Input
    *
    * Based on ideas found at http://www.voip-info.org/wiki-Asterisk+cmd+DTMFToText
    *
    * Example:
    *              UC   H     LC   i      ,     SP   h     o      w    SP   a    r      e     SP   y      o      u     ?
    *   $string = '*8'.'44*'.'*5'.'444*'.'00*'.'0*'.'44*'.'666*'.'9*'.'0*'.'2*'.'777*'.'33*'.'0*'.'999*'.'666*'.'88*'.'0000*';
    *
    * @link http://www.voip-info.org/wiki-Asterisk+cmd+DTMFToText
    * @example examples/input.php Get text input from the user and say it back
    *
    * @return string
    */
    function text_input($mode='NUMERIC')
    {
      $alpha = array( 'k0'=>' ', 'k00'=>',', 'k000'=>'.', 'k0000'=>'?', 'k00000'=>'0',
                      'k1'=>'!', 'k11'=>':', 'k111'=>';', 'k1111'=>'#', 'k11111'=>'1',
                      'k2'=>'A', 'k22'=>'B', 'k222'=>'C', 'k2222'=>'2',
                      'k3'=>'D', 'k33'=>'E', 'k333'=>'F', 'k3333'=>'3',
                      'k4'=>'G', 'k44'=>'H', 'k444'=>'I', 'k4444'=>'4',
                      'k5'=>'J', 'k55'=>'K', 'k555'=>'L', 'k5555'=>'5',
                      'k6'=>'M', 'k66'=>'N', 'k666'=>'O', 'k6666'=>'6',
                      'k7'=>'P', 'k77'=>'Q', 'k777'=>'R', 'k7777'=>'S', 'k77777'=>'7',
                      'k8'=>'T', 'k88'=>'U', 'k888'=>'V', 'k8888'=>'8',
                      'k9'=>'W', 'k99'=>'X', 'k999'=>'Y', 'k9999'=>'Z', 'k99999'=>'9');
      $symbol = array('k0'=>'=',
                      'k1'=>'<', 'k11'=>'(', 'k111'=>'[', 'k1111'=>'{', 'k11111'=>'1',
                      'k2'=>'@', 'k22'=>'$', 'k222'=>'&', 'k2222'=>'%', 'k22222'=>'2',
                      'k3'=>'>', 'k33'=>')', 'k333'=>']', 'k3333'=>'}', 'k33333'=>'3',
                      'k4'=>'+', 'k44'=>'-', 'k444'=>'*', 'k4444'=>'/', 'k44444'=>'4',
                      'k5'=>"'", 'k55'=>'`', 'k555'=>'5',
                      'k6'=>'"', 'k66'=>'6',
                      'k7'=>'^', 'k77'=>'7',
                      'k8'=>"\\",'k88'=>'|', 'k888'=>'8',
                      'k9'=>'_', 'k99'=>'~', 'k999'=>'9');
      $text = '';
      do
      {
        $command = false;
        $result = $this->get_data('beep');
        foreach(explode('*', $result['result']) as $code)
        {
          if($command)
          {
            switch($code{0})
            {
              case '2': $text = substr($text, 0, strlen($text) - 1); break; // backspace
              case '5': $mode = 'LOWERCASE'; break;
              case '6': $mode = 'NUMERIC'; break;
              case '7': $mode = 'SYMBOL'; break;
              case '8': $mode = 'UPPERCASE'; break;
              case '9': $text = explode(' ', $text); unset($text[count($text)-1]); $text = join(' ', $text); break; // backspace a word
            }
            $code = substr($code, 1);
            $command = false;
          }
          if($code == '')
            $command = true;
          elseif($mode == 'NUMERIC')
            $text .= $code;
          elseif($mode == 'UPPERCASE' && isset($alpha['k'.$code]))
            $text .= $alpha['k'.$code];
          elseif($mode == 'LOWERCASE' && isset($alpha['k'.$code]))
            $text .= strtolower($alpha['k'.$code]);
          elseif($mode == 'SYMBOL' && isset($symbol['k'.$code]))
            $text .= $symbol['k'.$code];
        }
        $this->say_punctuation($text);
      } while(substr($result['result'], -2) == '**');
      return $text;
    }

   /**
    * Say Puncutation in a string
    *
    * @param string $text
    * @param string $escape_digits
    * @param integer $frequency
    * @return array, see evaluate for return information.
    */
    function say_punctuation($text, $escape_digits='', $frequency=8000)
    {
      for($i = 0; $i < strlen($text); $i++)
      {
        switch($text{$i})
        {
          case ' ': $ret .= 'SPACE ';
          case ',': $ret .= 'COMMA '; break;
          case '.': $ret .= 'PERIOD '; break;
          case '?': $ret .= 'QUESTION MARK '; break;
          case '!': $ret .= 'EXPLANATION POINT '; break;
          case ':': $ret .= 'COLON '; break;
          case ';': $ret .= 'SEMICOLON '; break;
          case '#': $ret .= 'POUND '; break;
          case '=': $ret .= 'EQUALS '; break;
          case '<': $ret .= 'LESS THAN '; break;
          case '(': $ret .= 'LEFT PARENTHESIS '; break;
          case '[': $ret .= 'LEFT BRACKET '; break;
          case '{': $ret .= 'LEFT BRACE '; break;
          case '@': $ret .= 'AT '; break;
          case '$': $ret .= 'DOLLAR SIGN '; break;
          case '&': $ret .= 'AMPERSAND '; break;
          case '%': $ret .= 'PERCENT '; break;
          case '>': $ret .= 'GREATER THAN '; break;
          case ')': $ret .= 'RIGHT PARENTHESIS '; break;
          case ']': $ret .= 'RIGHT BRACKET '; break;
          case '}': $ret .= 'RIGHT BRACE '; break;
          case '+': $ret .= 'PLUS '; break;
          case '-': $ret .= 'MINUS '; break;
          case '*': $ret .= 'ASTERISK '; break;
          case '/': $ret .= 'SLASH '; break;
          case "'": $ret .= 'SINGLE QUOTE '; break;
          case '`': $ret .= 'BACK TICK '; break;
          case '"': $ret .= 'QUOTE '; break;
          case '^': $ret .= 'CAROT '; break;
          case "\\": $ret .= 'BACK SLASH '; break;
          case '|': $ret .= 'BAR '; break;
          case '_': $ret .= 'UNDERSCORE '; break;
          case '~': $ret .= 'TILDE '; break;
          default: $ret .= $text{$i} . ' '; break;
        }
      }
      return $this->text2wav($ret, $escape_digits, $frequency);
    }

   /**
    * Create a new AGI_AsteriskManager
    */
    function &new_AsteriskManager()
    {
      $this->asm = new AGI_AsteriskManager(NULL, $this->config);
      $this->asm->pagi =& $this;
      $this->config =& $this->asm->config;
      return $this->asm;
    }


   // *********************************************************************************************************
   // **                       PRIVATE                                                                       **
   // *********************************************************************************************************


   /**
    * Evaluate an AGI command
    *
    * @access private
    * @param string $command
    * @return array ('code'=>$code, 'result'=>$result, 'data'=>$data)
    */
    function evaluate($command)
    {
      $broken = array('code'=>500, 'result'=>-1, 'data'=>'');

      // write command
      if(!@fwrite($this->out, trim($command) . "\n")) return $broken;
      fflush($this->out);

      // Read result.  Occasionally, a command return a string followed by an extra new line.
      // When this happens, our script will ignore the new line, but it will still be in the
      // buffer.  So, if we get a blank line, it is probably the result of a previous
      // command.  We read until we get a valid result or asterisk hangs up.  One offending
      // command is SEND TEXT.
      $count = 0;
      do
      {
        $str = trim(fgets($this->in, 4096));
      } while($str == '' && $count++ < 5);

      if($count >= 5)
      {
//        $this->conlog("evaluate error on read for $command");
        return $broken;
      }

      // parse result
      $ret['code'] = substr($str, 0, 3);
      $str = trim(substr($str, 3));

      if($str{0} == '-') // we have a multiline response!
      {
        $count = 0;
        $str = substr($str, 1) . "\n";
        $line = fgets($this->in, 4096);
        while(substr($line, 0, 3) != $ret['code'] && $count < 5)
        {
          $str .= $line;
          $line = fgets($this->in, 4096);
          $count = (trim($line) == '') ? $count + 1 : 0;
        }
        if($count >= 5)
        {
//          $this->conlog("evaluate error on multiline read for $command");
          return $broken;
        }
      }

      $ret['result'] = NULL;
      $ret['data'] = '';
      if($ret['code'] != AGIRES_OK) // some sort of error
      {
        $ret['data'] = $str;
        $this->conlog(print_r($ret, true));
      }
      else // normal AGIRES_OK response
      {
        $parse = explode(' ', trim($str));
        $in_token = false;
        foreach($parse as $token)
        {
          if($in_token) // we previously hit a token starting with ')' but not ending in ')'
          {
            $ret['data'] .= ' ' . trim($token, '() ');
            if($token{strlen($token)-1} == ')') $in_token = false;
          }
          elseif($token{0} == '(')
          {
            if($token{strlen($token)-1} != ')') $in_token = true;
            $ret['data'] .= ' ' . trim($token, '() ');
          }
          elseif(strpos($token, '='))
          {
            $token = explode('=', $token);
            $ret[$token[0]] = $token[1];
          }
          elseif($token != '')
            $ret['data'] .= ' ' . $token;
        }
        $ret['data'] = trim($ret['data']);
      }

      // log some errors
      if($ret['result'] < 0)
        $this->conlog("$command returned {$ret['result']}");

      return $ret;
    }

   /**
    * Log to console if debug mode
    *
    * @example examples/ping.php Ping an IP address
    *
    * @param string $str
    * @param integer $vbl verbose level
    */
    function conlog($str, $vbl=1)
    {
      static $busy = false;

      if($this->config['phpagi']['debug'] != false)
      {
        if(!$busy) // no conlogs inside conlog!!!
        {
          $busy = true;
          $this->verbose($str, $vbl);
          $busy = false;
        }
      }
    }

   /**
    * Find an execuable in the path
    *
    * @access private
    * @param string $cmd command to find
    * @param string $checkpath path to check
    * @return string the path to the command
    */
    function which($cmd, $checkpath=NULL)
    {
      global $_ENV;
      $chpath = is_null($checkpath) ? $_ENV['PATH'] : $checkpath;

      foreach(explode(':', $chpath) as $path)
        if(is_executable("$path/$cmd"))
          return "$path/$cmd";

      if(is_null($checkpath))
        return $this->which($cmd, '/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:'.
                                  '/usr/X11R6/bin:/usr/local/apache/bin:/usr/local/mysql/bin');
      return false;
    }

   /**
    * Make a folder recursively
    *
    * @access private
    * @param string $folder
    * @param integer $perms
    */
    function make_folder($folder, $perms=0755)
    {
      $f = explode(DIRECTORY_SEPARATOR, $folder);
      $base = '';
      for($i = 0; $i < count($f); $i++)
      {
        $base .= $f[$i];
        if($f[$i] != '' && !file_exists($base))
          mkdir($base, $perms);
        $base .= DIRECTORY_SEPARATOR;
      }
    }	
  }

/**
 * error handler for phpagi
 *
 * @param integer $level PHP error level
 * @param string $message error message
 * @param string $file path to file
 * @param integer $line line number of error
 * @param array $context variables in the current scope
 */
  function phpagi_error_handler($level, $message, $file, $line, $context)
  {
    if(ini_get('error_reporting') == 0) return; // this happens with an @

    @syslog(LOG_WARNING, $file . '[' . $line . ']: ' . $message);

    global $phpagi_error_handler_email;
    if(!is_null($phpagi_error_handler_email)) // generate email debugging information
    {
      // decode error level
      switch($level)
      {
        case E_WARNING:
        case E_USER_WARNING:
          $level = "Warning";
          break;
        case E_NOTICE:
        case E_USER_NOTICE:
          $level = "Notice";
          break;
        case E_USER_ERROR:
          $level = "Error";
          break;
      }

      // build message
      $basefile = basename($file);
      $subject = "$basefile/$line/$level: $message";
      $message = "$level: $message in $file on line $line\n\n";

      if(strpos(' '.strtolower($message), 'mysql'))
        $message .= 'MySQL error ' . mysql_errno() . ": " . mysql_error() . "\n\n";

      // include variables
      $message .= "\n\nHostname: " . @get_hostname() ."\n";
      $message .= "\n\nIP Address: " . @gethostbyname(@get_hostname()) ."\n";

      $message .= "\n\nContext:\n" . print_r($context, true);
      $message .= "\n\nGLOBALS:\n" . print_r($GLOBALS, true);
      $message .= "\n\nBacktrace:\n" . print_r(debug_backtrace(), true);

      // include code fragment
      $message .= "\n\n$file:\n";
      $code = file($file);
      for($i = max(0, $line - 10); $i < min($line + 10, count($code)); $i++)
        $message .= ($i + 1)."\t$code[$i]";

      // send the mail if less than 5 errors
      static $mailcount = 0;
      if($mailcount < 5)
        @mail('errors@intertech.net', $subject, readable($message));
      $mailcount++;
    }
  }
  $phpagi_error_handler_email = NULL;

?>
